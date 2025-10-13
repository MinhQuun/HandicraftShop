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
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = Str::of($validated['message'])->trim()->toString();
        if ($message === '') {
            return response()->json(['message' => 'Nội dung câu hỏi không được để trống.'], 422);
        }

        // ===== 1) TRA CỨU GIÁ SẢN PHẨM NGAY TRONG DB (trả lời tức thì) =====
        // - Bắt vài từ khóa thường gặp: gia, giá, bao nhieu, bao nhiêu, cost, price...
        $lower = Str::lower($message);
        $isPriceQuestion = Str::contains($lower, ['gia', 'giá', 'bao nhieu', 'bao nhiêu', 'price', 'cost', 'bao nhieu tiền', 'bao nhiều']);

        if ($isPriceQuestion) {
            // lấy cụm danh từ sau từ "sản phẩm"/"túi"/"thảm" nếu có, nếu không thì try-like toàn câu
            $keyword = $this->extractProductKeyword($message);
            $rows = $this->searchProducts($keyword ?: $message);

            if ($rows->isNotEmpty()) {
                // định dạng trả lời có giá VND, gợi ý link "Chi tiết" (nếu có route), và đề xuất sp gần tên
                $top = $rows->take(5);
                $lines = $top->map(function ($r, $i) {
                    $vnd = number_format((int) $r->GIABAN, 0, ',', '.') . ' VND';
                    return ($i + 1) . ". {$r->TENSANPHAM} — {$vnd}";
                })->implode("\n");

                $intro = $keyword
                    ? "Mình tìm thấy một số sản phẩm khớp với \"{$keyword}\":"
                    : "Mình tìm thấy một số sản phẩm phù hợp:";

                $tail = "\n\nBạn có thể nói \"chi tiết + tên sản phẩm\" để mình mở trang chi tiết, hoặc cho biết kích thước/chất liệu mong muốn để lọc chính xác hơn.";

                return response()->json([
                    'reply' => $intro . "\n" . $lines . $tail,
                ]);
            }
        }

        // ===== 2) GỌI GEMINI (có GROUNDING bằng danh mục sản phẩm để AI không bịa) =====
        $apiKey = config('services.gemini.api_key');
        $model  = config('services.gemini.model', 'gemini-2.0-flash');
        $base   = rtrim(config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models'), '/');
        if (empty($apiKey)) {
            return response()->json(['message' => 'Chưa cấu hình GEMINI_API_KEY.'], 503);
        }
        $endpoint = "{$base}/{$model}:generateContent?key=" . urlencode($apiKey);

        // Lấy 15 sản phẩm làm ngữ cảnh (tên + giá)
        $catalog = DB::table('SANPHAM')
            ->select(['MASANPHAM', 'TENSANPHAM', 'GIABAN'])
            ->orderBy('TENSANPHAM')
            ->limit(15)
            ->get()
            ->map(fn($r) => "{$r->TENSANPHAM} — " . number_format((int)$r->GIABAN, 0, ',', '.') . " VND")
            ->implode("\n");

        $persona = "Bạn là trợ lý AI của HandicraftShop. Luôn trả lời bằng tiếng Việt, ngắn gọn, chính xác. 
                Khi người dùng hỏi giá/tên sản phẩm, ưu tiên trả lời theo danh mục nội bộ (bên dưới). 
                Nếu không tìm thấy trong danh mục, hãy nói lịch sự là chưa có dữ liệu và gợi ý cách tìm sản phẩm tương tự.
                Luôn định dạng tiền tệ theo VND (dùng dấu . cho hàng nghìn).";

        $context = "DANH MỤC SẢN PHẨM (tên — giá VND):\n" . ($catalog ?: '(trống)');

        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [[ 'text' => $persona ]]],
                ['role' => 'user', 'parts' => [[ 'text' => $context ]]],
                ['role' => 'user', 'parts' => [[ 'text' => $message ]]],
            ],
            'generationConfig' => [
                'temperature' => 0.4, // hạ nhiệt để ít "bịa"
                'maxOutputTokens' => 512,
            ],
        ];

        try {
            $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->timeout(30)
                ->post($endpoint, $payload);
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

    private function extractProductKeyword(string $msg): ?string
    {
        // Rất đơn giản: lấy cụm sau các từ khóa “sản phẩm”, “túi”, “thảm”, …
        $m = [];
        if (preg_match('/(?:sản phẩm|san pham|túi|tui|thảm|tham)\s+([A-Za-zÀ-ỹ0-9\s\-]+)/iu', $msg, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function searchProducts(string $keyword)
    {
        $kw = '%' . Str::of($keyword)->squish()->toString() . '%';
        return DB::table('SANPHAM')
            ->select(['MASANPHAM','TENSANPHAM','GIABAN'])
            ->where('TENSANPHAM', 'like', $kw)
            ->orWhere('MASANPHAM', 'like', $kw)
            ->limit(10)
            ->get();
    }
}