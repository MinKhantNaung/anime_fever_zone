<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\AiChatService;
use Illuminate\Http\Request;

final class ChatController extends Controller
{
    public function __invoke(Request $request, AiChatService $ai)
    {
        $message = trim($request->string('message')->toString());

        if ($message === '') {
            abort(422, 'A message is required.');
        }

        if (mb_strlen($message) > 1000) {
            abort(422, 'Message is too long. Please keep it under 1000 characters.');
        }

        $history = $request->input('history', []);

        if (! is_array($history)) {
            $history = [];
        }

        try {
            return response()->json([
                'text' => $ai->respond($message, $history),
            ]);
        } catch (\Throwable $e) {
            logger()->error('AI Chat Error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'AI response failed.',
            ], 500);
        }
    }
}
