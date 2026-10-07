<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class ChatController extends Controller
{
    /**
     * Gửi tin nhắn từ User tới Admin
     */
    public function send(Request $request)
    {
        $messageText = $request->input('message');

        if (empty(trim($messageText))) {
            return response()->json(['error' => 'Nội dung tin nhắn không được để trống'], 400);
        }

        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập để gửi tin nhắn!'], 401);
        }

        $admin = User::where('role', 'admin')->first();
        $receiverId = $admin ? $admin->id : 1;

        try {
            $message = Message::create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $receiverId,
                'content'     => $messageText,
                'is_read'     => false,
            ]);

            return response()->json($message);
        } catch (Exception $e) {
            return response()->json(['error' => 'Không thể gửi tin nhắn: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lấy lịch sử chat giữa User hiện tại và Admin
     */
    public function getMessages()
    {
        try {
            $userId = Auth::id();

            // Nếu chưa đăng nhập thì trả về mảng rỗng thay vì bị sập 500
            if (!$userId) {
                return response()->json([]);
            }

            $admin = User::where('role', 'admin')->first();
            $adminId = $admin ? $admin->id : 1;

            $messages = Message::with(['sender', 'receiver'])
                ->where(function ($q) use ($userId, $adminId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $adminId);
                })
                ->orWhere(function ($q) use ($userId, $adminId) {
                    $q->where('sender_id', $adminId)
                      ->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($messages);
        } catch (Exception $e) {
            // Trả lỗi dạng JSON giúp JS không bị văng SyntaxError: Unexpected token '<'
            return response()->json(['error' => 'Lỗi tải tin nhắn: ' . $e->getMessage()], 500);
        }
    }
}