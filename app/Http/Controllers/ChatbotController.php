<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ChatbotController extends Controller
{
    // Định nghĩa các "ý định" (intent) mà chatbot có thể hiểu
    private const INTENT_PRICE   = 'GET_PRICE';
    private const INTENT_STOCK   = 'GET_STOCK';
    private const INTENT_CATEGORY = 'GET_CATEGORY_PRODUCTS';
    private const INTENT_PROMO   = 'GET_PROMOTIONS';
    private const INTENT_REVIEW  = 'GET_REVIEWS';
    private const INTENT_GENERAL = 'GENERAL_AI'; // Ý định chung, sẽ dùng AI

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = Str::of($validated['message'])->trim()->toString();
        if ($message === '') {
            return response()->json(['message' => 'Nội dung câu hỏi không được để trống.'], 422);
        }

        // ====== 1. PHÂN LOẠI Ý ĐỊNH (INTENT DETECTION) ======
        $intent = $this->detectIntent($message);
        $keyword = $this->extractProductKeyword($message) ?: $message;
        $response = null;

        // ====== 2. XỬ LÝ "FAST PATH" (TRẢ LỜI TỪ DB) ======
        // Dựa trên ý định, gọi hàm xử lý tương ứng
        switch ($intent) {
            case self::INTENT_PRICE:
                $response = $this->handlePriceQuestion($keyword);
                break;
            case self::INTENT_STOCK:
                $response = $this->handleStockQuestion($keyword);
                break;
            case self::INTENT_CATEGORY:
                $response = $this->handleCategoryQuestion($message); // Dùng cả câu để tìm tên DM
                break;
            case self::INTENT_PROMO:
                $response = $this->handlePromoQuestion($keyword);
                break;
            case self::INTENT_REVIEW:
                $response = $this->handleReviewQuestion($keyword);
                break;
        }

        // Nếu một "Fast Path" đã xử lý thành công, trả về ngay
        if ($response) {
            return $response;
        }

        // ====== 3. GỌI GEMINI (RAG) VỚI NGỮ CẢNH ĐỘNG ======
        // Nếu không có intent cụ thể, hoặc intent cần AI, thì gọi Gemini
        return $this->callGeminiAI($message);
    }

    // =========================================================
    // HÀM XỬ LÝ CÁC "INTENT" (FAST PATH)
    // =========================================================

    /**
     * Xử lý câu hỏi về GIÁ
     */
    private function handlePriceQuestion(string $keyword): ?JsonResponse
    {
        $rows = $this->searchProducts($keyword, 5); // Tìm 5 SP liên quan

        if ($rows->isEmpty()) {
            return null; // Không tìm thấy, để AI xử lý
        }

        $lines = $rows->map(function ($r, $i) {
            $vnd = number_format((int) $r->GIABAN, 0, ',', '.') . ' VND';
            return ($i + 1) . ". {$r->TENSANPHAM} — {$vnd}";
        })->implode("\n");

        $intro = $keyword 
            ? "Mình tìm thấy một số sản phẩm khớp với \"{$keyword}\":"
            : "Mình tìm thấy một số sản phẩm phù hợp:";
        $tail = "\n\nBạn có thể hỏi thêm về \"tồn kho\" hoặc \"đánh giá\" của sản phẩm này nhé.";

        return response()->json(['reply' => $intro . "\n" . $lines . $tail]);
    }

    /**
     * Xử lý câu hỏi về TỒN KHO
     */
    private function handleStockQuestion(string $keyword): ?JsonResponse
    {
        $rows = $this->searchProducts($keyword, 5);

        if ($rows->isEmpty()) {
            return null;
        }

        $lines = $rows->map(function ($r, $i) {
            $stock = (int) $r->SOLUONGTON;
            $status = $stock > 0 ? "Còn hàng ({$stock} chiếc)" : "Hết hàng";
            return ($i + 1) . ". {$r->TENSANPHAM} — {$status}";
        })->implode("\n");

        $intro = "Tình trạng tồn kho cho \"{$keyword}\" như sau:";
        return response()->json(['reply' => $intro . "\n" . $lines]);
    }

    /**
     * Xử lý câu hỏi về DANH MỤC
     */
    private function handleCategoryQuestion(string $message): ?JsonResponse
    {
        // Cố gắng tìm tên danh mục/loại trong câu
        $categoryName = $this->extractCategoryKeyword($message);
        if (!$categoryName) {
            return null; // Không rõ danh mục, để AI lo
        }

        $rows = $this->searchProductsByCategory($categoryName, 5);

        if ($rows->isEmpty()) {
            return response()->json(['reply' => "Mình có tìm danh mục \"{$categoryName}\" nhưng chưa thấy sản phẩm nào phù hợp."]);
        }

        $lines = $rows->map(fn($r, $i) => ($i + 1) . ". {$r->TENSANPHAM}")->implode("\n");
        $intro = "Dạ, đây là các sản phẩm thuộc nhóm \"{$categoryName}\" mình tìm thấy:";

        return response()->json(['reply' => $intro . "\n" . $lines]);
    }

    /**
     * Xử lý câu hỏi về KHUYẾN MÃI
     */
    private function handlePromoQuestion(string $keyword): ?JsonResponse
    {
        // 1. Hỏi về KM chung ("có sale gì không?")
        if (Str::contains(Str::lower($keyword), ['chung', 'tất cả', $keyword])) {
            $promos = $this->searchActivePromotions();
            if ($promos->isEmpty()) {
                return response()->json(['reply' => 'Dạ, hiện tại shop chưa có chương trình khuyến mãi nào.']);
            }
            $lines = $promos->map(fn($r, $i) => ($i + 1) . ". {$r->TENKHUYENMAI} (Giảm {$r->GIAMGIA}%)")->implode("\n");
            return response()->json(['reply' => "Shop đang có các ưu đãi sau:\n" . $lines]);
        }

        // 2. Hỏi về KM cho 1 sản phẩm cụ thể
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product || (!$product->MAKHUYENMAI && !$product->KM_NHIEU)) {
            return response()->json(['reply' => "Sản phẩm \"{$product->TENSANPHAM}\" hiện không nằm trong chương trình khuyến mãi nào ạ."]);
        }

        // (Code này giả định bạn có join để lấy tên KM, hoặc sẽ cần query thêm)
        $reply = "Sản phẩm \"{$product->TENSANPHAM}\" đang được áp dụng ưu đãi. (Bạn cần query chi tiết KM từ MAKHUYENMAI)";
        
        return response()->json(['reply' => $reply]);
    }

    /**
     * Xử lý câu hỏi về ĐÁNH GIÁ
     */
    private function handleReviewQuestion(string $keyword): ?JsonResponse
    {
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product) {
            return null; // Không tìm thấy SP, để AI lo
        }

        $stats = DB::table('DANHGIA')
            ->where('MASANPHAM', $product->MASANPHAM)
            ->selectRaw('COUNT(*) as total, AVG(DIEMSO) as avg_rating')
            ->first();

        if ($stats->total == 0) {
            return response()->json(['reply' => "Sản phẩm \"{$product->TENSANPHAM}\" hiện chưa có đánh giá nào."]);
        }

        $reviews = DB::table('DANHGIA')
            ->where('MASANPHAM', $product->MASANPHAM)
            ->orderBy('NGAYDANHGIA', 'desc')
            ->limit(2)
            ->pluck('NHANXET');
        
        $avg = number_format($stats->avg_rating, 1);
        $reply = "Sản phẩm \"{$product->TENSANPHAM}\" có {$stats->total} lượt đánh giá, với điểm trung bình là {$avg}/5 sao.";
        
        if ($reviews->isNotEmpty()) {
            $reply .= "\nMột số đánh giá mới nhất:\n- " . $reviews->implode("\n- ");
        }

        return response()->json(['reply' => $reply]);
    }

    // =========================================================
    // HÀM GỌI AI (GEMINI) VỚI NGỮ CẢNH ĐỘNG (RAG)
    // =========================================================

    private function callGeminiAI(string $message): JsonResponse
    {
        $apiKey = config('services.gemini.api_key');
        $model  = config('services.gemini.model', 'gemini-2.0-flash'); // Hoặc 1.5
        $base   = rtrim(config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models'), '/');
        if (empty($apiKey)) {
            return response()->json(['message' => 'Chưa cấu hình GEMINI_API_KEY.'], 503);
        }
        $endpoint = "{$base}/{$model}:generateContent?key=" . urlencode($apiKey);

        // ===== BƯỚC 1: TRUY XUẤT (RETRIEVAL) =====
        // Lấy thông tin ngữ cảnh động từ DB dựa trên tin nhắn
        $context = $this->buildDynamicContext($message);

        // ===== BƯỚC 2: BỔ SUNG (AUGMENT) =====
        $persona = "Bạn là trợ lý AI của HandicraftShop. Luôn trả lời bằng tiếng Việt, ngắn gọn, thân thiện. 
                    Khi người dùng hỏi giá/tên sản phẩm/thông tin, ưu tiên trả lời theo DANH MỤC NỘI BỘ (bên dưới). 
                    Nếu không tìm thấy, hãy nói lịch sự là chưa có dữ liệu.
                    Luôn định dạng tiền tệ theo VND (dùng dấu . cho hàng nghìn).";
        
        $fullContext = "DANH MỤC NỘI BỘ (dữ liệu tham khảo):\n" . $context;

        $payload = [
            'contents' => [
                // Xây dựng lịch sử chat cho AI
                ['role' => 'user', 'parts' => [[ 'text' => $persona ]]],
                ['role' => 'model', 'parts' => [[ 'text' => "Dạ, vâng. Tôi là trợ lý của HandicraftShop. Tôi đã sẵn sàng." ]]],
                ['role' => 'user', 'parts' => [[ 'text' => $fullContext ]]],
                ['role' => 'model', 'parts' => [[ 'text' => "Tôi đã nhận được thông tin ngữ cảnh nội bộ." ]]],
                ['role' => 'user', 'parts' => [[ 'text' => $message ]]], // Câu hỏi cuối cùng của người dùng
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 512,
            ],
        ];

        // ===== BƯỚC 3: TẠO SINH (GENERATE) =====
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->timeout(30)->post($endpoint, $payload);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Không thể kết nối tới Gemini.'], 504);
        }

        if ($response->failed()) {
            $body = $response->json();
            $status = $response->status();
            $errMsg = data_get($body, 'error.message') ?: 'Trợ lý AI đang bận. Vui lòng thử lại sau.';
            if (in_array($status, [401, 403])) $errMsg = 'Gemini API key không hợp lệ hoặc không đủ quyền.';
            if ($status === 429) $errMsg = 'Gemini vượt hạn mức/quá tải. Vui lòng thử lại ít phút.';
            return response()->json(['message' => $errMsg], $status ?: 500);
        }

        $json  = $response->json();
        $parts = data_get($json, 'candidates.0.content.parts', []);
        $reply = '';
        if (is_array($parts)) {
            foreach ($parts as $p) {
                $t = data_get($p, 'text', '');
                if (is_string($t)) $reply .= $t;
            }
        }
        if (!is_string($reply) || trim($reply) === '') {
            return response()->json(['message' => 'Máy chủ AI không trả về nội dung hợp lệ.'], 502);
        }

        return response()->json(['reply' => trim($reply)]);
    }


    // =========================================================
    // HÀM TRỢ GIÚP (HELPERS)
    // =========================================================

    /**
     * Nhận diện ý định của người dùng (bằng từ khóa)
     */
    private function detectIntent(string $message): string
    {
        $lower = Str::lower($message);

        if (Str::contains($lower, ['còn hàng', 'còn không', 'tồn kho', 'so luong ton'])) {
            return self::INTENT_STOCK;
        }
        if (Str::contains($lower, ['giá', 'gia tien', 'bao nhiêu tiền', 'bao nhieu', 'price', 'cost'])) {
            return self::INTENT_PRICE;
        }
        if (Str::contains($lower, ['đánh giá', 'review', 'tốt không', 'chất lượng', 'co ben khong'])) {
            return self::INTENT_REVIEW;
        }
        if (Str::contains($lower, ['khuyến mãi', 'giam gia', 'sale', 'ưu đãi', 'khuyen mai'])) {
            return self::INTENT_PROMO;
        }
        if (Str::contains($lower, ['danh mục', 'loại nào', 'có bán', 'các loại', 'thuoc nhom'])) {
            return self::INTENT_CATEGORY;
        }

        return self::INTENT_GENERAL;
    }

    /**
     * Trích xuất từ khóa SẢN PHẨM (tên SP)
     */
    private function extractProductKeyword(string $msg): ?string
    {
        // Lấy cụm sau các từ khóa "sản phẩm", "túi", "thảm", "giá của", "tồn kho của"...
        $m = [];
        if (preg_match('/(?:sản phẩm|san pham|túi|tui|thảm|tham|giá của|tồn kho của|đánh giá cho)\s+([A-Za-zÀ-ỹ0-9\s\-]+)/iu', $msg, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * Trích xuất từ khóa DANH MỤC
     */
    private function extractCategoryKeyword(string $msg): ?string
    {
        $m = [];
        // Lấy cụm sau các từ "danh mục", "loại", "nhóm"...
        if (preg_match('/(?:danh mục|danh muc|loại|loai|nhóm|nhom)\s+([A-Za-zÀ-ỹ0-9\s\-]+)/iu', $msg, $m)) {
            return trim($m[1]);
        }
        // Thử bắt nếu không có từ khóa đầu
        if (preg_match('/(?:có bán|các loại)\s+([A-Za-zÀ-ỹ0-9\s\-]+)\s*(không|ko|ạ)?/iu', $msg, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * (NÂNG CẤP) Tìm sản phẩm bằng FULL-TEXT SEARCH (FTS)
     * LƯU Ý: Cần chạy: ALTER TABLE SANPHAM ADD FULLTEXT(TENSANPHAM, MOTA);
     */
    private function searchProducts(string $keyword, int $limit = 10)
    {
        // Chuyển đổi keyword cho FTS (ví dụ: "túi cói" -> "+túi +cói*")
        $ftsKeyword = Str::of($keyword)->squish()->explode(' ')
            ->map(fn($word) => '+' . $word) // Bắt buộc phải có
            ->implode(' ');
        
        // Thêm * vào từ cuối cùng để tìm kiếm khớp 1 phần (ví dụ: "túi c" -> "+túi +c*")
        if (strlen($ftsKeyword) > 2) {
            $ftsKeyword .= '*';
        }

        return DB::table('SANPHAM')
            ->select(['MASANPHAM','TENSANPHAM','GIABAN', 'SOLUONGTON', 'MOTA', 'MAKHUYENMAI'])
            // (Tùy chọn) Join để lấy thông tin KM M-M
            // ->leftJoin('SANPHAM_KHUYENMAI as spkm', 'SANPHAM.MASANPHAM', '=', 'spkm.MASANPHAM')
            // ->addSelect(DB::raw('GROUP_CONCAT(spkm.MAKHUYENMAI) as KM_NHIEU'))
            ->whereRaw('MATCH(TENSANPHAM, MOTA) AGAINST(? IN BOOLEAN MODE)', [$ftsKeyword])
            // ->groupBy('SANPHAM.MASANPHAM') // Bỏ group nếu không join M-M
            ->limit($limit)
            ->get();
    }

    /**
     * Tìm sản phẩm theo TÊN DANH MỤC / LOẠI
     */
    private function searchProductsByCategory(string $categoryName, int $limit = 10)
    {
        return DB::table('SANPHAM as sp')
            ->join('LOAI as l', 'sp.MALOAI', '=', 'l.MALOAI')
            ->join('DANHMUCSANPHAM as dm', 'l.MADANHMUC', '=', 'dm.MADANHMUC')
            ->where('l.TENLOAI', 'LIKE', "%{$categoryName}%")
            ->orWhere('dm.TENDANHMUC', 'LIKE', "%{$categoryName}%")
            ->select('sp.TENSANPHAM', 'sp.GIABAN')
            ->limit($limit)
            ->get();
    }

    /**
     * Tìm các khuyến mãi đang hoạt động
     */
    private function searchActivePromotions(int $limit = 5)
    {
        return DB::table('KHUYENMAI')
            ->where('NGAYKETTHUC', '>', now())
            // ->where('NGAYBATDAU', '<=', now()) // (Tùy chọn nếu muốn KM bắt đầu trong tương lai)
            ->select('TENKHUYENMAI', 'GIAMGIA', 'LOAIKHUYENMAI')
            ->limit($limit)
            ->get();
    }

    /**
     * Xây dựng ngữ cảnh động (dynamic context) cho RAG
     */
    private function buildDynamicContext(string $message): string
    {
        $contextLines = [];

        // 1. Lấy tất cả danh mục (ngữ cảnh chung)
        $categories = DB::table('DANHMUCSANPHAM')->pluck('TENDANHMUC');
        if ($categories->isNotEmpty()) {
            $contextLines[] = "Các danh mục sản phẩm của shop: " . $categories->implode(', ') . ".";
        }

        // 2. Tìm các sản phẩm liên quan đến câu hỏi
        $products = $this->searchProducts($message, 3); // Lấy 3 SP liên quan nhất
        if ($products->isNotEmpty()) {
            $contextLines[] = "\nThông tin các sản phẩm LIÊN QUAN đến câu hỏi:";
            foreach ($products as $p) {
                $price = number_format((int) $p->GIABAN, 0, ',', '.') . ' VND';
                $stock = $p->SOLUONGTON > 0 ? "Còn hàng ({$p->SOLUONGTON})" : "Hết hàng";
                $desc = Str::limit($p->MOTA, 150); // Giới hạn mô tả
                
                $contextLines[] = "- Tên: {$p->TENSANPHAM}\n  Giá: {$price}\n  Tồn kho: {$stock}\n  Mô tả: {$desc}";

                // (Tùy chọn) Lấy 1-2 review cho sản phẩm này
                $reviews = DB::table('DANHGIA')
                    ->where('MASANPHAM', $p->MASANPHAM)
                    ->orderBy('DIEMSO', 'desc')
                    ->limit(1)->pluck('NHANXET');
                if ($reviews->isNotEmpty()) {
                    $contextLines[] = "  Đánh giá nổi bật: " . $reviews->first();
                }
            }
        } else {
            $contextLines[] = "\nKhông tìm thấy sản phẩm nào khớp với \"{$message}\" trong cơ sở dữ liệu.";
        }

        return implode("\n", $contextLines);
    }
}