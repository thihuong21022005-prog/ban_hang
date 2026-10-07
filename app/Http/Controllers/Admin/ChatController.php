<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Lấy danh sách những User đã từng nhắn tin với Admin
     */
    public function getUsers()
    {
        $adminId = Auth::id();

        // Tìm tất cả ID của người dùng có tương tác với admin
        $userIds = Message::where('receiver_id', $adminId)
            ->orWhere('sender_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($msg) use ($adminId) {
                return $msg->sender_id == $adminId ? $msg->receiver_id : $msg->sender_id;
            })
            ->unique()
            ->toArray();

        // Lấy thông tin chi tiết các User đó
        return User::whereIn('id', $userIds)
            ->where('id', '!=', $adminId)
            ->select('id', 'name')
            ->get();
    }

    /**
     * Lấy lịch sử tin nhắn của một User cụ thể
     */
    public function getMessages($userId)
    {
        $adminId = Auth::id();

        return Message::with('sender')
            ->where(function ($q) use ($userId, $adminId) {
                $q->where('sender_id', $userId)->where('receiver_id', $adminId);
            })
            ->orWhere(function ($q) use ($userId, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Admin gửi tin nhắn phản hồi
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->user_id,
            'content'     => $request->message,
            'is_read'     => true,
        ]);

        return response()->json($message);
    }
}