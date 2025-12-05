<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
    private const INTENT_POLICY       = 'GET_POLICY';       // Chính sách giao/đổi/trả
    private const INTENT_GENERAL      = 'GENERAL_AI';       // Ý định chung, sử dụng AI

    private const HISTORY_TTL_MINUTES = 720; // 12h lưu lịch sử
    private const MAX_HISTORY_FOR_AI  = 8;
    private const MAX_HISTORY_RETURN  = 40;
    private const POLICY_RESPONSES    = [
        'shipping' => [
            'keywords' => ['giao hàng','giao hang','ship','vận chuyển','van chuyen','delivery','shipping'],
            'answer'   => "• HandicraftShop giao hàng toàn quốc: nội thành TP.HCM 24h, tỉnh thành khác 2-4 ngày.\n"
                        . "• Đơn trên 500.000đ được miễn phí giao tiêu chuẩn; đơn nhỏ hơn áp dụng phí theo hãng vận chuyển.\n"
                        . "• Có thể chọn giao nhanh, tiêu chuẩn hoặc nhận tại showroom (miễn phí).",
        ],
        'payment' => [
            'keywords' => ['thanh toán','payment','pay','chuyển khoản','chuyen khoan','momo','cod','vnpay'],
            'answer'   => "• Hỗ trợ COD, chuyển khoản ngân hàng, ví MoMo và VNPay.\n"
                        . "• Đơn trên 5.000.000đ cần đặt cọc 50%. Sau thanh toán gửi hóa đơn điện tử ngay.\n"
                        . "• Thông tin thanh toán hiển thị rõ ở bước Checkout để bạn duyệt trước khi xác nhận.",
        ],
        'return' => [
            'keywords' => ['đổi trả','doi tra','hoàn trả','hoan tra','bảo hành','bao hanh','return','refund','warranty'],
            'answer'   => "• Đổi trả trong 7 ngày nếu sản phẩm lỗi do nhà sản xuất hoặc hư hỏng khi vận chuyển.\n"
                        . "• Quy trình: chụp ảnh tình trạng, gửi mã đơn + số điện thoại để được hướng dẫn đổi/hoàn tiền.\n"
                        . "• Đồ gỗ, sơn mài bảo hành nước sơn 6 tháng; đồ gốm nghệ nhân bảo hành 3 tháng.",
        ],
        'care' => [
            'keywords' => ['bảo quản','bao quan','chăm sóc','cham soc','vệ sinh','ve sinh','care','giữ màu','giu mau'],
            'answer'   => "• Đồ gỗ: lau khăn ẩm mềm, hạn chế nắng gắt và tránh hóa chất mạnh.\n"
                        . "• Gốm sứ: rửa tay bằng nước ấm, không dùng máy rửa với chi tiết vàng kim.\n"
                        . "• Mây tre: để nơi khô ráo, có thể thoa dầu khoáng mỏng mỗi 6 tháng để giữ màu.",
        ],
    ];
    private array $suggestedProducts = [];

    public function __invoke(Request $request): JsonResponse
    {
        $this->suggestedProducts = [];

        $validated = $request->validate([
            'message'       => ['required', 'string', 'max:500'],
            'session_token' => ['nullable', 'string', 'max:100'],
        ]);

        $message = Str::of($validated['message'])->trim()->toString();
        if ($message === '') {
            return response()->json(['message' => 'Nội dung câu hỏi không được để trống.'], 422);
        }

        $session = $this->resolveChatSession(data_get($validated, 'session_token'));
        $history = $this->getStoredHistory($session->id, self::MAX_HISTORY_FOR_AI);

        // ====== 1. PHÂN LOẠI Ý ĐỊNH (INTENT DETECTION) ======
        $intent  = $this->detectIntent($message);
        $keyword = $this->extractProductKeyword($message) ?: $message;
        $reply   = null;

        // ====== 2. XỬ LÝ "FAST PATH" (TRẢ LỜI TỪ DB) ======
        switch ($intent) {
            case self::INTENT_PRICE:
                $reply = $this->handlePriceQuestion($keyword);
                break;
            case self::INTENT_STOCK:
                $reply = $this->handleStockQuestion($keyword);
                break;
            case self::INTENT_CATEGORY:
                $reply = $this->handleCategoryQuestion($message);
                break;
            case self::INTENT_PROMO:
                $reply = $this->handlePromoQuestion($keyword);
                break;
            case self::INTENT_REVIEW:
                $reply = $this->handleReviewQuestion($keyword);
                break;
            case self::INTENT_PRICE_FILTER:
                $reply = $this->handlePriceFilterQuestion($message);
                break;
            case self::INTENT_DESCRIPTION:
                $reply = $this->handleDescriptionQuestion($keyword);
                break;
            case self::INTENT_SUGGESTIONS:
                $reply = $this->handleSuggestionsQuestion($keyword);
                break;
            case self::INTENT_SUPPLIER:
                $reply = $this->handleSupplierQuestion($keyword);
                break;
            case self::INTENT_POLICY:
                $reply = $this->handlePolicyQuestion($message);
                break;
        }

        // ====== 3. GỌI GEMINI (RAG) VỚI NGỮ CẢNH ĐỘNG ======
        if ($reply === null) {
            $reply = $this->callGeminiAI($message, $history);
        }

        $this->storeChatMessage($session->id, 'user', $message);
        $meta = empty($this->suggestedProducts) ? [] : ['products' => $this->suggestedProducts];
        $this->storeChatMessage($session->id, 'assistant', $reply, $meta);
        $this->cleanupExpiredMessages($session->id);

        return response()->json([
            'reply'         => $reply,
            'session_token' => $session->token,
            'expires_at'    => $session->expires_at->toIso8601String(),
            'products'      => $this->suggestedProducts,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['nullable', 'string', 'max:100'],
            'token'         => ['nullable', 'string', 'max:100'],
        ]);

        $token = $validated['session_token']
            ?? $validated['token']
            ?? $request->query('token');

        $session = $this->resolveChatSession($token);
        $history = $this->getStoredHistory($session->id, self::MAX_HISTORY_RETURN)
            ->map(function ($row) {
                $created = $row->CREATED_AT ? Carbon::parse($row->CREATED_AT)->toIso8601String() : null;
                $metadata = json_decode((string) ($row->METADATA ?? ''), true) ?: [];
                return [
                    'role'       => $row->ROLE,
                    'message'    => $row->MESSAGE,
                    'created_at' => $created,
                    'products'   => data_get($metadata, 'products', []),
                ];
            })->all();

        return response()->json([
            'session_token' => $session->token,
            'expires_at'    => $session->expires_at->toIso8601String(),
            'history'       => $history,
        ]);
    }


    // =========================================================
    // HÀM XỬ LÝ CÁC "INTENT" (FAST PATH)
    // =========================================================

    /**
     * Xử lý câu hỏi về GIÁ
     */
    private function handlePriceQuestion(string $keyword): ?string
    {
        $rows = $this->searchProducts($keyword, 5); // Tìm 5 SP liên quan

        if ($rows->isEmpty()) {
            return null; // Không tìm thấy, để AI xử lý
        }

        $lines = $rows->map(function ($r, $i) {
            $vnd = number_format((int) $r->GIABAN, 0, ',', '.') . ' VND';
            return ($i + 1) . ". {$r->TENSANPHAM} — {$vnd}";
        })->implode("\n");

        $this->rememberProducts($rows);

        $intro = $keyword
            ? "Mình tìm thấy một số sản phẩm khớp với \"{$keyword}\":"
            : "Mình tìm thấy một số sản phẩm phù hợp:";
        $tail = "\n\nBạn có thể hỏi thêm về \"tồn kho\" hoặc \"đánh giá\" của sản phẩm này nhé.";

        return $intro . "\n" . $lines . $tail;
    }

    /**
     * Xử lý câu hỏi về TỒN KHO
     */
    private function handleStockQuestion(string $keyword): ?string
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
        return $intro . "\n" . $lines;
    }

    /**
     * Xử lý câu hỏi về DANH MỤC
     */
    private function handleCategoryQuestion(string $message): ?string
    {
        // Cố gắng tìm tên danh mục/loại trong câu
        $categoryName = $this->extractCategoryKeyword($message);
        if (!$categoryName) {
            return null; // Không rõ danh mục, để AI lo
        }

        $rows = $this->searchProductsByCategory($categoryName, 5);

        if ($rows->isEmpty()) {
            return "Mình có tìm danh mục " . $categoryName . " nhưng chưa thấy sản phẩm nào phù hợp.";
        }

        $lines = $rows->map(fn($r, $i) => ($i + 1) . ". {$r->TENSANPHAM}")->implode("\n");
        $intro = "Đây là các sản phẩm thuộc nhóm " . $categoryName . " mình tìm thấy:";

        return $intro . "\n" . $lines;
    }

    /**
     * Xử lý câu hỏi về KHUYẾN MÃI
     */
    private function handlePromoQuestion(string $keyword): ?string
    {
        // 1. Hỏi về KM chung ("có sale gì không?")
        if (Str::contains(Str::lower($keyword), ['chung', 'tất cả', $keyword])) {
            $promos = $this->searchActivePromotions();
            if ($promos->isEmpty()) {
                return 'Dạ, hiện tại shop chưa có chương trình khuyến mãi nào.';
            }
            $lines = $promos->map(fn($r, $i) => ($i + 1) . ". {$r->TENKHUYENMAI} (Giảm {$r->GIAMGIA}%)")->implode("
");
            return "Shop đang có các ưu đãi sau:
" . $lines;
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

        $linkedPromos = DB::table('SANPHAM_KHUYENMAI')
            ->where('MASANPHAM', $product->MASANPHAM)
            ->pluck('MAKHUYENMAI')
            ->toArray();

        $promoIds = array_unique(array_merge($promoIds, $linkedPromos));

        if (empty($promoIds)) {
            return "Sản phẩm " . $product->TENSANPHAM . " hiện không nằm trong chương trình khuyến mãi nào ạ.";
        }

        $promos = DB::table('KHUYENMAI')
            ->whereIn('MAKHUYENMAI', $promoIds)
            ->where('NGAYKETTHUC', '>', now())
            ->get(['TENKHUYENMAI', 'GIAMGIA', 'LOAIKHUYENMAI']);

        if ($promos->isEmpty()) {
            return "Sản phẩm " . $product->TENSANPHAM . " hiện không có khuyến mãi đang hoạt động.";
        }

        $lines = $promos->map(fn($p) => "- {$p->TENKHUYENMAI} (Giảm {$p->GIAMGIA}% - Loại: {$p->LOAIKHUYENMAI})")->implode("
        ");
        return "Sản phẩm " . $product->TENSANPHAM . " đang được áp dụng các ưu đãi sau:\n" . $lines;
    }

    /**
     * Xử lý câu hỏi về ĐÁNH GIÁ
     */
    private function handleReviewQuestion(string $keyword): ?string
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
            return "Sản phẩm " . $product->TENSANPHAM . " hiện chưa có đánh giá nào.";
        }

        $reviews = DB::table('DANHGIA')
            ->where('MASANPHAM', $product->MASANPHAM)
            ->orderBy('NGAYDANHGIA', 'desc')
            ->limit(3)
            ->pluck('NHANXET');

        $avg = number_format($stats->avg_rating, 1);
        $reply = "Sản phẩm " . $product->TENSANPHAM . " có {$stats->total} lượt đánh giá, với điểm trung bình là {$avg}/5 sao.";

        if ($reviews->isNotEmpty()) {
            $reply .= "
Một số đánh giá mới nhất:
- " . $reviews->implode("
- ");
        }

        return $reply;
    }

    /**
     * Xử lý câu hỏi LỌC GIÁ (ví dụ: "dưới 100k")
     */
    /**
     * Xử lý câu hỏi LỌC GIÁ (ví dụ: "dưới 100k")
     */

    private function handlePriceFilterQuestion(string $message): ?string
    {
        [$operator, $price] = $this->parsePriceFromMessage($message);

        if ($operator === null || $price === null || $price === 0) {
            return null;
        }

        $rows = DB::table('SANPHAM')
            ->where('GIABAN', $operator, $price)
            ->orderBy('GIABAN', $operator === '>=' ? 'asc' : 'desc')
            ->limit(5)
            ->get(['MASANPHAM', 'TENSANPHAM', 'GIABAN']);

        if ($rows->isEmpty()) {
            return "Mình không tìm thấy sản phẩm nào có giá " . $this->formatOperatorText($operator) . " " . number_format($price, 0, ',', '.') . " VND.";
        }

        $this->rememberProducts($rows);

        $lines = $rows->map(function ($r, $i) {
            $vnd = number_format((int) $r->GIABAN, 0, ',', '.') . ' VND';
            return ($i + 1) . ". {$r->TENSANPHAM} - {$vnd}";
        })->implode("
");

        $intro = "Đây là các sản phẩm có giá " . $this->formatOperatorText($operator) . " " . number_format($price, 0, ',', '.') . " VND:";
        return $intro . "
" . $lines;
    }

    /**
     * X Xử lý câu hỏi về MÔ TẢ SẢN PHẨM (Mới)
     */
    /**
     * Xử lý câu hỏi về MÔ TẢ SẢN PHẨM (Mới)
     */
    private function handleDescriptionQuestion(string $keyword): ?string
    {
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product) {
            return null;
        }

        $desc = Str::limit($product->MOTA, 300);
        $reply = "Dạ, mô tả chi tiết về sản phẩm " . $product->TENSANPHAM . ":\n{$desc}";

        $category = DB::table('LOAI as l')
            ->join('DANHMUCSANPHAM as dm', 'l.MADANHMUC', '=', 'dm.MADANHMUC')
            ->where('l.MALOAI', $product->MALOAI)
            ->select('l.TENLOAI', 'dm.TENDANHMUC')
            ->first();

        if ($category) {
            $reply .= "

Thuộc loại: {$category->TENLOAI} (Danh mục: {$category->TENDANHMUC})";
        }

        return $reply;
    }

    /**
     * Xử lý câu hỏi về GỢI Ý SẢN PHẨM (Mới: Gợi ý sản phẩm tương tự)
     */
    /**
     * Xử lý câu hỏi về GỢI Ý SẢN PHẨM (Mới: Gợi ý sản phẩm tương tự)
     */
    private function handleSuggestionsQuestion(string $keyword): ?string
    {
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product) {
            return null;
        }

        $suggestions = DB::table('SANPHAM')
            ->where('MALOAI', $product->MALOAI)
            ->where('MASANPHAM', '!=', $product->MASANPHAM)
            ->limit(3)
            ->get(['TENSANPHAM', 'GIABAN']);

        if ($suggestions->isEmpty()) {
            return "Dạ, hiện tại mình chưa tìm thấy sản phẩm tương tự với " . $product->TENSANPHAM . ".";
        }

        $lines = $suggestions->map(function ($r, $i) {
            $vnd = number_format((int) $r->GIABAN, 0, ',', '.') . ' VND';
            return ($i + 1) . ". {$r->TENSANPHAM} — {$vnd}";
        })->implode("
");

        $intro = "Dựa trên sản phẩm " . $product->TENSANPHAM . ", mình gợi ý một số sản phẩm tương tự:";
        return $intro . "
" . $lines;
    }

    /**
     * Xử lý câu hỏi về NHÀ CUNG CẤP (Mới)
     */
    private function handleSupplierQuestion(string $keyword): ?string
    {
        $product = $this->searchProducts($keyword, 1)->first();
        if (!$product || !$product->MANHACUNGCAP) {
            return null;
        }

        $supplier = DB::table('NHACUNGCAP')
            ->where('MANHACUNGCAP', $product->MANHACUNGCAP)
            ->first(['TENNHACUNGCAP', 'DTHOAI', 'DIACHI']);

        if (!$supplier) {
            return "Sản phẩm " . $product->TENSANPHAM . " chưa có thông tin nhà cung cấp.";
        }

        $reply = "Sản phẩm " . $product->TENSANPHAM . " được cung cấp bởi: {$supplier->TENNHACUNGCAP}.\n";
        $reply .= "Liên hệ: {$supplier->DTHOAI}
Địa chỉ: {$supplier->DIACHI}";

        return $reply;
    }

    /**
     * Xử lý câu hỏi về chính sách giao hàng/thanh toán/bảo hành
     */
    private function handlePolicyQuestion(string $message): ?string
    {
        $lower = Str::lower($message);
        foreach (self::POLICY_RESPONSES as $topic => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (Str::contains($lower, $keyword)) {
                    return $data['answer'];
                }
            }
        }

        return "HandicraftShop hỗ trợ giao hàng toàn quốc, nhiều phương thức thanh toán và đổi trả trong 7 ngày cho sản phẩm lỗi. Bạn có thể hỏi cụ thể về giao hàng, thanh toán, đổi trả hoặc bảo quản để mình tư vấn kỹ hơn nhé.";
    }

    // =========================================================
    // HÀM GỌI AI (GEMINI) VỚI NGỮ CẢNH ĐỘNG (RAG)
    // =========================================================
    // =========================================================
    // HÀM GỌI AI (GEMINI) VỚI NGỮ CẢNH ĐỘNG (RAG)
    // =========================================================

    private function callGeminiAI(string $message, $historyRecords = []): string
    {
        $apiKey = config('services.gemini.api_key');
        $model  = config('services.gemini.model', 'gemini-1.5-flash');
        $base   = rtrim(config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models'), '/');
        if (empty($apiKey)) {
            throw new HttpResponseException(response()->json(['message' => 'Chưa cấu hình GEMINI_API_KEY.'], 503));
        }
        $endpoint = "{$base}/{$model}:generateContent?key=" . urlencode($apiKey);

        $context = $this->buildDynamicContext($message);
        $persona = "Bạn là trợ lý AI của HandicraftShop. Luôn trả lời tiếng Việt, thân thiện, súc tích.
                    Khi người dùng hỏi giá/tồn kho/thông tin sản phẩm, ưu tiên dữ liệu nội bộ bên dưới.
                    Nếu không tìm thấy thì giải thích lịch sự và gợi ý bước tiếp theo.";

        $historyParts = [];
        foreach ($historyRecords as $record) {
            $role = data_get($record, 'ROLE');
            $text = trim((string) data_get($record, 'MESSAGE', ''));
            if ($text === '' || !in_array($role, ['user', 'assistant'], true)) {
                continue;
            }
            $historyParts[] = [
                'role'  => $role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $text]],
            ];
        }

        $payload = [
            'contents' => array_merge(
                [
                    ['role' => 'user',  'parts' => [['text' => $persona]]],
                    ['role' => 'model', 'parts' => [['text' => 'Đã hiểu vai trò trợ lý HandicraftShop.']]],
                    ['role' => 'user',  'parts' => [['text' => "DANH MỤC NỘI BỘ (dữ liệu tham khảo):
{$context}"]]],
                    ['role' => 'model', 'parts' => [['text' => 'Đã nhận thông tin nền.']]],
                ],
                $historyParts,
                [
                    ['role' => 'user', 'parts' => [['text' => $message]]],
                ]
            ),
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 512,
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->timeout(30)->post($endpoint, $payload);
        } catch (Throwable $e) {
            report($e);
            throw new HttpResponseException(response()->json(['message' => 'Không thể kết nối tới Gemini.'], 504));
        }

        if ($response->failed()) {
            $body = $response->json();
            $status = $response->status();
            $errMsg = data_get($body, 'error.message') ?: 'Trợ lý AI đang bận. Vui lòng thử lại sau.';
            if (in_array($status, [401, 403])) {
                $errMsg = 'Gemini API key không hợp lệ hoặc không đủ quyền.';
            }
            if ($status === 429) {
                $errMsg = 'Gemini vượt hạn mức. Vui lòng thử lại ít phút.';
            }
            throw new HttpResponseException(response()->json(['message' => $errMsg], $status ?: 500));
        }

        $json  = $response->json();
        $parts = data_get($json, 'candidates.0.content.parts', []);
        $reply = '';
        if (is_array($parts)) {
            foreach ($parts as $p) {
                $t = data_get($p, 'text', '');
                if (is_string($t)) {
                    $reply .= $t;
                }
            }
        }
        if (!is_string($reply) || trim($reply) === '') {
            throw new HttpResponseException(response()->json(['message' => 'Máy chủ AI không trả về nội dung hợp lệ.'], 502));
        }

        return trim($reply);
    }

    // =========================================================
    // HÀM TRỢ GIÚP (HELPERS)
    // =========================================================

    private function resolveChatSession(?string $token): object
    {
        $this->purgeExpiredSessions();

        $now       = Carbon::now();
        $expiresAt = $now->copy()->addMinutes(self::HISTORY_TTL_MINUTES);
        $userId    = Auth::id();

        $session = null;
        if ($token) {
            $session = DB::table('CHATBOT_SESSIONS')
                ->where('SESSION_TOKEN', $token)
                ->first();
        }

        if ($session) {
            DB::table('CHATBOT_SESSIONS')
                ->where('ID', $session->ID)
                ->update([
                    'USER_ID'    => $userId,
                    'EXPIRES_AT' => $expiresAt,
                    'UPDATED_AT' => $now,
                ]);

            return (object) [
                'id'         => (int) $session->ID,
                'token'      => $session->SESSION_TOKEN,
                'expires_at' => $expiresAt,
            ];
        }

        $token = $token && Str::length($token) >= 20 ? $token : (string) Str::uuid();
        $id = DB::table('CHATBOT_SESSIONS')->insertGetId([
            'SESSION_TOKEN' => $token,
            'USER_ID'       => $userId,
            'EXPIRES_AT'    => $expiresAt,
            'CREATED_AT'    => $now,
            'UPDATED_AT'    => $now,
        ]);

        return (object) [
            'id'         => $id,
            'token'      => $token,
            'expires_at' => $expiresAt,
        ];
    }

    private function getStoredHistory(int $sessionId, int $limit = 10)
    {
        if ($sessionId <= 0) {
            return collect();
        }

        return DB::table('CHATBOT_MESSAGES')
            ->where('SESSION_ID', $sessionId)
            ->where('EXPIRES_AT', '>', Carbon::now())
            ->orderBy('ID')
            ->limit($limit)
            ->get(['ID', 'ROLE', 'MESSAGE', 'CREATED_AT', 'METADATA']);
    }

    private function rememberProducts($rows): void
    {
        $this->suggestedProducts = collect($rows)
            ->filter(fn($r) => isset($r->MASANPHAM))
            ->map(fn($r) => [
                'id'    => (string) $r->MASANPHAM,
                'name'  => (string) $r->TENSANPHAM,
                'price' => isset($r->GIABAN) ? (int) $r->GIABAN : null,
            ])
            ->values()
            ->toArray();
    }

    private function storeChatMessage(int $sessionId, string $role, string $message, array $metadata = []): void
    {
        $text = trim($message);
        if ($sessionId <= 0 || $text === '') {
            return;
        }

        DB::table('CHATBOT_MESSAGES')->insert([
            'SESSION_ID' => $sessionId,
            'ROLE'       => in_array($role, ['assistant', 'user', 'system'], true) ? $role : 'user',
            'MESSAGE'    => Str::limit($text, 2000, '...'),
            'METADATA'   => empty($metadata) ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE),
            'CREATED_AT' => Carbon::now(),
            'EXPIRES_AT' => Carbon::now()->addMinutes(self::HISTORY_TTL_MINUTES),
        ]);
    }

    private function cleanupExpiredMessages(int $sessionId): void
    {
        DB::table('CHATBOT_MESSAGES')
            ->where('SESSION_ID', $sessionId)
            ->where('EXPIRES_AT', '<=', Carbon::now())
            ->delete();
    }

    private function purgeExpiredSessions(): void
    {
        $threshold = Carbon::now()->subMinutes(self::HISTORY_TTL_MINUTES);
        $ids = DB::table('CHATBOT_SESSIONS')
            ->where('EXPIRES_AT', '<=', $threshold)
            ->limit(50)
            ->pluck('ID');

        if ($ids->isNotEmpty()) {
            DB::table('CHATBOT_SESSIONS')->whereIn('ID', $ids)->delete();
        }
    }
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

        if (preg_match('/(giao hàng|ship|vận chuyển|thanh toán|payment|chuyển khoản|cod|momo|vnpay|đổi trả|hoàn trả|bảo hành|return|refund|warranty|bảo quản|chăm sóc|vệ sinh|care)/iu', $lower)) {
            return self::INTENT_POLICY;
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
