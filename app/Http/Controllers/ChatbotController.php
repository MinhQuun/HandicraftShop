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
    private const INTENT_PRICE        = 'GET_PRICE';
    private const INTENT_STOCK        = 'GET_STOCK';
    private const INTENT_CATEGORY     = 'GET_CATEGORY_PRODUCTS';
    private const INTENT_PROMO        = 'GET_PROMOTIONS';
    private const INTENT_REVIEW       = 'GET_REVIEWS';
    private const INTENT_PRICE_FILTER = 'GET_PRICE_FILTER';
    private const INTENT_DESCRIPTION  = 'GET_DESCRIPTION';  // Mới: Mô tả sản phẩm
    private const INTENT_SUGGESTIONS  = 'GET_SUGGESTIONS';  // Mới: Gợi ý sản phẩm tương tự
    private const INTENT_SUPPLIER     = 'GET_SUPPLIER';     // Mới: Thông tin nhà cung cấp
    private const INTENT_GENERAL      = 'GENERAL_AI';       // Ý định chung, sẽ dùng AI

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
            case self::INTENT_PRICE_FILTER:
                $response = $this->handlePriceFilterQuestion($message);
                break;
            case self::INTENT_DESCRIPTION:
                $response = $this->handleDescriptionQuestion($keyword);
                break;
            case self::INTENT_SUGGESTIONS:
                $response = $this->handleSuggestionsQuestion($keyword);
                break;
            case self::INTENT_SUPPLIER:
                $response = $this->handleSupplierQuestion($keyword);
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
        if (!$product) {
            return null; // Không tìm thấy, để AI xử lý
        }

        $promoIds = [];
        if ($product->MAKHUYENMAI) {
            $promoIds[] = $product->MAKHUYENMAI;
        }

        // Lấy từ bảng liên kết (nếu có nhiều KM)
        $linkedPromos = DB::table('SANPHAM_KHUYENMAI')
            ->where('MASANPHAM', $product->MASANPHAM)
            ->pluck('MAKHUYENMAI')
            ->toArray();

        $promoIds = array_merge($promoIds, $linkedPromos);
        $promoIds = array_unique($promoIds);

        if (empty($promoIds)) {
            return response()->json(['reply' => "Sản phẩm \"{$product->TENSANPHAM}\" hiện không nằm trong chương trình khuyến mãi nào ạ."]);
        }

        // Lấy chi tiết KM
        $promos = DB::table('KHUYENMAI')
            ->whereIn('MAKHUYENMAI', $promoIds)
            ->where('NGAYKETTHUC', '>', now())
            ->get(['TENKHUYENMAI', 'GIAMGIA', 'LOAIKHUYENMAI']);

        if ($promos->isEmpty()) {
            return response()->json(['reply' => "Sản phẩm \"{$product->TENSANPHAM}\" hiện không có khuyến mãi đang hoạt động."]);
        }

        $lines = $promos->map(fn($p) => "- {$p->TENKHUYENMAI} (Giảm {$p->GIAMGIA}% - Loại: {$p->LOAIKHUYENMAI})")->implode("\n");
        $reply = "Sản phẩm \"{$product->TENSANPHAM}\" đang được áp dụng các ưu đãi sau:\n" . $lines;

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
            ->limit(3)  // Tăng lên 3 để chi tiết hơn
            ->pluck('NHANXET');

        $avg = number_format($stats->avg_rating, 1);
        $reply = "Sản phẩm \"{$product->TENSANPHAM}\" có {$stats->total} lượt đánh giá, với điểm trung bình là {$avg}/5 sao.";

        if ($reviews->isNotEmpty()) {
            $reply .= "\nMột số đánh giá mới nhất:\n- " . $reviews->implode("\n- ");
        }

        return response()->json(['reply' => $reply]);
    }

    /**
     * Xử lý câu hỏi LỌC GIÁ (ví dụ: "dưới 100k")
     */
    private function handlePriceFilterQuestion(string $message): ?JsonResponse
    {
        [$operator, $price] = $this->parsePriceFromMessage($message);

        // Nếu không bóc tách được giá hoặc toán tử, để AI lo
        if ($operator === null || $price === null || $price === 0) {
            return null;
        }

        $rows = DB::table('SANPHAM')
            ->where('GIABAN', $operator, $price)
            ->orderBy('GIABAN', $operator === '>=' ? 'asc' : 'desc') // Sắp xếp hợp lý
            ->limit(5)
            ->get(['TENSANPHAM', 'GIABAN']);

        if ($rows->isEmpty()) {
            $reply = "Dạ, mình không tìm thấy sản phẩm nào có giá " . $this->formatOperatorText($operator) . " " . number_format($price, 0, ',', '.') . " VND ạ.";
            return response()->json(['reply' => $reply]);
        }

        $lines = $rows->map(function ($r, $i) {
            $vnd = number_format((int) $r->GIABAN, 0, ',', '.') . ' VND';
            return ($i + 1) . ". {$r->TENSANPHAM} — {$vnd}";
        })->implode("\n");

        $intro = "Dạ, đây là các sản phẩm có giá " . $this->formatOperatorText($operator) . " " . number_format($price, 0, ',', '.') . " VND mình tìm thấy:";
        return response()->json(['reply' => $intro . "\n" . $lines]);
    }

    /**
     * Xử lý câu hỏi về MÔ TẢ SẢN PHẨM (Mới)
     */
    private function handleDescriptionQuestion(string $keyword): ?JsonResponse
    {
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product) {
            return null;
        }

        $desc = Str::limit($product->MOTA, 300); // Giới hạn để ngắn gọn
        $reply = "Dạ, mô tả chi tiết về sản phẩm \"{$product->TENSANPHAM}\":\n{$desc}";

        // Thêm thông tin loại và danh mục để chi tiết hơn
        $category = DB::table('LOAI as l')
            ->join('DANHMUCSANPHAM as dm', 'l.MADANHMUC', '=', 'dm.MADANHMUC')
            ->where('l.MALOAI', $product->MALOAI)
            ->select('l.TENLOAI', 'dm.TENDANHMUC')
            ->first();

        if ($category) {
            $reply .= "\n\nThuộc loại: {$category->TENLOAI} (Danh mục: {$category->TENDANHMUC})";
        }

        return response()->json(['reply' => $reply]);
    }

    /**
     * Xử lý câu hỏi về GỢI Ý SẢN PHẨM (Mới: Gợi ý sản phẩm tương tự)
     */
    private function handleSuggestionsQuestion(string $keyword): ?JsonResponse
    {
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product) {
            return null;
        }

        // Gợi ý sản phẩm cùng loại hoặc danh mục
        $suggestions = DB::table('SANPHAM')
            ->where('MALOAI', $product->MALOAI)
            ->where('MASANPHAM', '!=', $product->MASANPHAM)
            ->limit(3)
            ->get(['TENSANPHAM', 'GIABAN']);

        if ($suggestions->isEmpty()) {
            return response()->json(['reply' => "Dạ, hiện tại mình chưa tìm thấy sản phẩm tương tự với \"{$product->TENSANPHAM}\"."]);
        }

        $lines = $suggestions->map(function ($r, $i) {
            $vnd = number_format((int) $r->GIABAN, 0, ',', '.') . ' VND';
            return ($i + 1) . ". {$r->TENSANPHAM} — {$vnd}";
        })->implode("\n");

        $intro = "Dựa trên sản phẩm \"{$product->TENSANPHAM}\", mình gợi ý một số sản phẩm tương tự:";
        return response()->json(['reply' => $intro . "\n" . $lines]);
    }

    /**
     * Xử lý câu hỏi về NHÀ CUNG CẤP (Mới)
     */
    private function handleSupplierQuestion(string $keyword): ?JsonResponse
    {
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product || !$product->MANHACUNGCAP) {
            return null;
        }

        $supplier = DB::table('NHACUNGCAP')
            ->where('MANHACUNGCAP', $product->MANHACUNGCAP)
            ->first(['TENNHACUNGCAP', 'DTHOAI', 'DIACHI']);

        if (!$supplier) {
            return response()->json(['reply' => "Sản phẩm \"{$product->TENSANPHAM}\" chưa có thông tin nhà cung cấp."]);
        }

        $reply = "Sản phẩm \"{$product->TENSANPHAM}\" được cung cấp bởi: {$supplier->TENNHACUNGCAP}.\n";
        $reply .= "Liên hệ: {$supplier->DTHOAI}\nĐịa chỉ: {$supplier->DIACHI}";

        return response()->json(['reply' => $reply]);
    }

    // =========================================================
    // HÀM GỌI AI (GEMINI) VỚI NGỮ CẢNH ĐỘNG (RAG)
    // =========================================================

    private function callGeminiAI(string $message): JsonResponse
    {
        $apiKey = config('services.gemini.api_key');
        $model  = config('services.gemini.model', 'gemini-1.5-flash'); // Nâng cấp model nếu cần
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
                    Luôn định dạng tiền tệ theo VND (dùng dấu . cho hàng nghìn).
                    Gợi ý thêm sản phẩm liên quan nếu phù hợp.";

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
     * Nhận diện ý định của người dùng (cải tiến với nhiều keywords và regex)
     */
    private function detectIntent(string $message): string
    {
        $lower = Str::lower($message);

        // Cải tiến: Sử dụng regex để chính xác hơn
        if (preg_match('/(còn hàng|còn không|tồn kho|số lượng|so luong ton|stock)/iu', $lower)) {
            return self::INTENT_STOCK;
        }

        // Ưu tiên lọc giá
        if (preg_match('/(dưới|trên|khoảng|từ|thấp hơn|cao hơn)\s+([0-9,.]+\s*(k|ngàn|nghìn|triệu))|([0-9,.]+\s*(k|ngàn|nghìn|triệu))\s*(trở\s*xuống|trở\s*lên)/iu', $lower)) {
            return self::INTENT_PRICE_FILTER;
        }

        if (preg_match('/(giá|gia tien|bao nhiêu tiền|bao nhieu|price|cost)/iu', $lower)) {
            return self::INTENT_PRICE;
        }

        if (preg_match('/(đánh giá|review|tốt không|chất lượng|co ben khong|nhận xét)/iu', $lower)) {
            return self::INTENT_REVIEW;
        }

        if (preg_match('/(khuyến mãi|giam gia|sale|ưu đãi|khuyen mai|promo)/iu', $lower)) {
            return self::INTENT_PROMO;
        }

        if (preg_match('/(danh mục|danh muc|loại|loai|nhóm|nhom|có bán|các loại|thuoc nhom)/iu', $lower)) {
            return self::INTENT_CATEGORY;
        }

        // Intent mới
        if (preg_match('/(mô tả|chi tiết|thông tin|description|detail)/iu', $lower)) {
            return self::INTENT_DESCRIPTION;
        }

        if (preg_match('/(gợi ý|tương tự|recommend|suggest|lien quan)/iu', $lower)) {
            return self::INTENT_SUGGESTIONS;
        }

        if (preg_match('/(nhà cung cấp|nha cung cap|supplier|nguồn gốc|nguon goc)/iu', $lower)) {
            return self::INTENT_SUPPLIER;
        }

        return self::INTENT_GENERAL;
    }

    /**
     * Trích xuất từ khóa SẢN PHẨM (cải tiến để xử lý tiếng Việt tốt hơn)
     */
    private function extractProductKeyword(string $msg): ?string
    {
        // Bỏ dấu để dễ match (optional, nếu cần)
        $normalized = $this->removeAccents($msg);

        // Lấy cụm sau các từ khóa "sản phẩm", "túi", "thảm", "giá của", "tồn kho của"...
        $m = [];
        if (preg_match('/(?:sản phẩm|san pham|túi|tui|thảm|tham|giá của|tồn kho của|đánh giá cho|mô tả của|gợi ý cho|nhà cung cấp của)\s+([A-Za-z0-9\s\-]+)/iu', $normalized, $m)) {
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
        // Cải tiến: Bỏ dấu cho keyword để match tốt hơn
        $normalizedKeyword = $this->removeAccents($keyword);

        // Chuyển đổi keyword cho FTS (ví dụ: "túi cói" -> "+tui +coi*")
        $ftsKeyword = Str::of($normalizedKeyword)->squish()->explode(' ')
            ->map(fn($word) => '+' . $word) // Bắt buộc phải có
            ->implode(' ');

        // Thêm * vào từ cuối cùng để tìm kiếm khớp 1 phần (ví dụ: "túi c" -> "+tui +c*")
        if (strlen($ftsKeyword) > 2) {
            $ftsKeyword .= '*';
        }

        return DB::table('SANPHAM')
            ->select(['MASANPHAM','TENSANPHAM','GIABAN', 'SOLUONGTON', 'MOTA', 'MALOAI', 'MAKHUYENMAI', 'MANHACUNGCAP'])
            ->whereRaw('MATCH(TENSANPHAM, MOTA) AGAINST(? IN BOOLEAN MODE)', [$ftsKeyword])
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
            ->select('TENKHUYENMAI', 'GIAMGIA', 'LOAIKHUYENMAI')
            ->limit($limit)
            ->get();
    }

    // ===== CÁC HÀM HELPER MỚI ĐƯỢC THÊM =====

    /**
     * Bóc tách giá và toán tử từ một tin nhắn
     * @return array [?string $operator, ?int $price]
     */
    private function parsePriceFromMessage(string $message): array
    {
        $lower = Str::lower($message);
        $price = 0;
        $operator = null;

        // 1. Trích xuất con số (100, 100k, 100.000)
        if (preg_match('/([0-9,.]+)/', $lower, $matches)) {
            $numberStr = str_replace(['.', ','], '', $matches[1]);
            $price = (int) $numberStr;
        } else {
            return [null, null]; // Không tìm thấy số
        }

        // 2. Điều chỉnh giá trị (k, ngàn, triệu)
        if (Str::contains($lower, ['k', 'ngàn', 'nghìn'])) {
            $price *= 1000;
        } elseif (Str::contains($lower, 'triệu')) {
            $price *= 1000000;
        }

        // 3. Xác định toán tử
        if (Str::contains($lower, ['dưới', 'nhỏ hơn', 'trở xuống', '<'])) {
            $operator = '<=';
        } elseif (Str::contains($lower, ['trên', 'lớn hơn', 'trở lên', '>'])) {
            $operator = '>=';
        } else {
             $operator = '<='; // Mặc định nếu chỉ nói "100k" -> hiểu là "dưới 100k"
        }

        return [$operator, $price];
    }

    /**
     * Chuyển toán tử thành text tiếng Việt
     */
    private function formatOperatorText(string $operator): string
    {
        return match ($operator) {
            '<=' => 'dưới hoặc bằng',
            '>=' => 'trên hoặc bằng',
            '<' => 'dưới',
            '>' => 'trên',
            default => 'bằng',
        };
    }

    /**
     * Xây dựng ngữ cảnh động (dynamic context) cho RAG (Cải tiến: Thêm nhiều info hơn)
     */
    private function buildDynamicContext(string $message): string
    {
        $contextLines = [];

        // 1. Lấy tất cả danh mục (ngữ cảnh chung)
        $categories = DB::table('DANHMUCSANPHAM')->pluck('TENDANHMUC');
        if ($categories->isNotEmpty()) {
            $contextLines[] = "Các danh mục sản phẩm của shop: " . $categories->implode(', ') . ".";
        }

        // 2. Lấy tất cả loại (chi tiết hơn)
        $types = DB::table('LOAI')->pluck('TENLOAI');
        if ($types->isNotEmpty()) {
            $contextLines[] = "Các loại sản phẩm: " . $types->implode(', ') . ".";
        }

        // 3. Tìm các sản phẩm liên quan đến câu hỏi
        $products = $this->searchProducts($message, 5); // Tăng lên 5 để chi tiết hơn
        if ($products->isNotEmpty()) {
            $contextLines[] = "\nThông tin các sản phẩm LIÊN QUAN đến câu hỏi:";
            foreach ($products as $p) {
                $price = number_format((int) $p->GIABAN, 0, ',', '.') . ' VND';
                $stock = $p->SOLUONGTON > 0 ? "Còn hàng ({$p->SOLUONGTON})" : "Hết hàng";
                $desc = Str::limit($p->MOTA, 150); // Giới hạn mô tả

                $contextLines[] = "- Tên: {$p->TENSANPHAM}\n  Giá: {$price}\n  Tồn kho: {$stock}\n  Mô tả: {$desc}";

                // Lấy 1-2 review cho sản phẩm này
                $reviews = DB::table('DANHGIA')
                    ->where('MASANPHAM', $p->MASANPHAM)
                    ->orderBy('DIEMSO', 'desc')
                    ->limit(2)->pluck('NHANXET');
                if ($reviews->isNotEmpty()) {
                    $contextLines[] = "  Đánh giá nổi bật: " . $reviews->implode(' | ');
                }

                // Lấy KM nếu có
                if ($p->MAKHUYENMAI) {
                    $promo = DB::table('KHUYENMAI')->where('MAKHUYENMAI', $p->MAKHUYENMAI)->first(['TENKHUYENMAI', 'GIAMGIA']);
                    if ($promo) {
                        $contextLines[] = "  Khuyến mãi: {$promo->TENKHUYENMAI} (Giảm {$promo->GIAMGIA}%)";
                    }
                }
            }
        } else {
            $contextLines[] = "\nKhông tìm thấy sản phẩm nào khớp với \"{$message}\" trong cơ sở dữ liệu.";
        }

        // 4. Thêm khuyến mãi đang hoạt động
        $promos = $this->searchActivePromotions(3);
        if ($promos->isNotEmpty()) {
            $contextLines[] = "\nCác khuyến mãi đang hoạt động: " . $promos->map(fn($p) => "{$p->TENKHUYENMAI} (Giảm {$p->GIAMGIA}%)")->implode(', ') . ".";
        }

        return implode("\n", $contextLines);
    }

    /**
     * Helper: Bỏ dấu tiếng Việt để match keyword tốt hơn
     */
    private function removeAccents(string $str): string
    {
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ắ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/', 'A', $str);
        $str = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/', 'E', $str);
        $str = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/', 'I', $str);
        $str = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/', 'O', $str);
        $str = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/', 'U', $str);
        $str = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/', 'Y', $str);
        $str = preg_replace('/(Đ)/', 'D', $str);
        return $str;
    }
}
