<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    /**
     * إرسال رسالة باستخدام Hugging Face Router (Together AI Provider)
     */
    public function sendMessage(Request $request): JsonResponse
    {
        // 1. استلام الرسالة من الطلب
        $userMessage = $request->input('message');

        // 2. جلب المفتاح من ملف .env (تأكد أن اسمه HUGGINGFACE_API_KEY)
        $apiKey = env('HUGGINGFACE_API_KEY');

        // 3. الإعدادات مأخوذة مباشرة من الصورة اللي بعتتها (الخيارات السوداء)
        $url = "https://router.huggingface.co/v1/chat/completions";
        $modelName = "Qwen/Qwen2.5-7B-Instruct:together";

        try {
            // 4. تنفيذ الطلب بنظام OpenAI-compatible المختار في صورتك
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => $modelName,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'max_tokens' => 500,
                'stream' => false
            ]);

            // 5. إذا فشل الطلب (مثلاً المفتاح غلط أو السيرفر مضغوط)
            if ($response->failed()) {
                return response()->json([
                    'reply' => 'خطأ من السيرفر: ' . ($response->json()['error']['message'] ?? 'فشل الاتصال')
                ], $response->status());
            }

            $data = $response->json();

            // 6. استخراج الرد (هاد التنسيق خاص بخيار openai اللي بالصورة)
            $aiReply = $data['choices'][0]['message']['content'] ?? 'لم أستطع الحصول على رد نصي.';

            return response()->json([
                'reply' => $aiReply
            ]);

        } catch (\Exception $e) {
            // 7. في حال وجود خطأ في الكود نفسه
            return response()->json([
                'reply' => 'خطأ داخلي في السيرفر: ' . $e->getMessage()
            ], 500);
        }
    }
}

