<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            return response()->json([
                'message' => 'Nội dung câu hỏi không được để trống.',
            ], 422);
        }

        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'message' => 'Chức năng trợ lý AI chưa được kích hoạt. Vui lòng liên hệ quản trị viên để cấu hình OPENAI_API_KEY.',
            ], 503);
        }

        $endpoint = config('services.openai.endpoint', 'https://api.openai.com/v1/chat/completions');
        $model = config('services.openai.model', 'gpt-4o-mini');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
                ->timeout(20)
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Bạn là trợ lý AI thân thiện của HandicraftShop. Luôn trả lời bằng tiếng Việt, tông giọng chuyên nghiệp, tập trung vào sản phẩm thủ công, quà tặng, chính sách ưu đãi và hướng dẫn bảo quản. Nếu câu hỏi nằm ngoài phạm vi, hãy lịch sự từ chối.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                    'temperature' => 0.7,
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Không thể kết nối tới máy chủ AI. Vui lòng thử lại sau ít phút.',
            ], 504);
        }

        if ($response->failed()) {
            $body = $response->json();
            $errorMessage = data_get($body, 'error.message')
                ?? 'Trợ lý AI đang bận. Vui lòng thử lại sau.';

            return response()->json([
                'message' => $errorMessage,
            ], $response->status() ?: 500);
        }

        $reply = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($reply) || trim($reply) === '') {
            return response()->json([
                'message' => 'Máy chủ AI không trả về nội dung hợp lệ.',
            ], 502);
        }

        return response()->json([
            'reply' => trim($reply),
        ]);
    }
}
