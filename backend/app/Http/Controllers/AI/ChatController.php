<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'required|string',
        ]);

        $userMessage = $request->input('message');
        $sessionId = $request->input('session_id');
        $apiKey = env('GEMINI_API_KEY');

        $lastMessage = ChatMessage::where('session_id', $sessionId)->latest()->first();

        $alreadyNeedsAdmin = $lastMessage ? ($lastMessage->needs_admin || $lastMessage->sender === 'admin') : false;

        ChatMessage::create([
            'session_id' => $sessionId,
            'sender' => 'user',
            'message' => $userMessage,
            'needs_admin' => $alreadyNeedsAdmin,
            'is_closed' => false,
            'chat_message_user_id' => auth('sanctum')->id(),
        ]);

        // LOGIKAI ZÁR: Ha már admin kell, a Bot nem válaszol, csak mentettük a user üzenetét
        if ($alreadyNeedsAdmin) {
            return response()->json([
                'reply' => null,
                'needs_admin' => true
            ]);
        }

        $systemInstructions = "You are the UCC Project AI Assistant. Your task is to help users based on these rules:
        1. EVENT CREATION: To create a new event, the user must go to the 'Event Management' tab and click '+ New Event'.
        2. EDIT EVENT: Users can modify their own events using the 'Edit' button next to the event.
        3. FORGOT PASSWORD: If they can't log in, they should use the 'Forgot Password' button on the Login tab to get an email.
        4. CHANGE PASSWORD: To change a password, they need to go to the 'Profile' tab, enter the new password, and save.
        5. MFA / 2FA: For two-factor authentication, go to the 'Profile' tab, scan the QR code with Google Authenticator.

        GENERAL RULES:
        - Be professional, clear, and concise.
        - Answer in the language the user uses (Hungarian or English).
        - If you cannot answer the question based on the info above, politely tell them that an agent will join the chat soon to help.
        - Important: If you don't know the answer, end your response with the exact string: [NEEDS_ADMIN]";

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            $payload = [
                "system_instruction" => ["parts" => [["text" => $systemInstructions]]],
                "contents" => [["role" => "user", "parts" => [["text" => $userMessage]]]],
                "generationConfig" => ["temperature" => 0.1, "maxOutputTokens" => 300]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($httpCode === 200 && isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $botReply = $result['candidates'][0]['content']['parts'][0]['text'];
                $needsAdmin = str_contains($botReply, '[NEEDS_ADMIN]');
                $botReply = str_replace('[NEEDS_ADMIN]', '', $botReply);
            } else {
                $botReply = "I'm sorry, I can't help with this specific question. A human agent will join the chat shortly, or you can call us at +36 1 123 1234.";
                $needsAdmin = true;
            }
        } catch (\Exception $e) {
            Log::error("Gemini Error: " . $e->getMessage());
            $botReply = "Technical error. Please wait for an agent.";
            $needsAdmin = true;
        }

        // 3. Bot válasz mentése
        ChatMessage::create([
            'session_id' => $sessionId,
            'sender' => 'bot',
            'message' => trim($botReply),
            'needs_admin' => $needsAdmin,
            'is_closed' => false
        ]);

        return response()->json([
            'reply' => trim($botReply),
            'needs_admin' => $needsAdmin
        ]);
    }
}
