<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class MessageController extends Controller
{
    /**
     * إنشاء رسالة جديدة داخل محادثة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id', // التحقق من وجود المحادثة
            'message' => 'required|string', // التحقق من وجود الرسالة
        ]);

        // العثور على المحادثة بناءً على conversation_id
        $conversation = Conversation::find($request->conversation_id);

        // إنشاء الرسالة
        $message = Message::create([
            'conversation_id' => $conversation->id, // ربط الرسالة بالمحادثة
            'sender_id' => $request->user()->id, // معرف المرسل (المستخدم الحالي)
            'receiver_id' => ($conversation->user1_id == $request->user()->id)
                ? $conversation->user2_id // إذا كان المرسل هو user1، فالمستقبل هو user2
                : $conversation->user1_id, // وإذا كان المرسل هو user2، فالمستقبل هو user1
            'message' => $request->message, // النص الفعلي للرسالة
        ]);

        // إرجاع الرسالة التي تم إنشاؤها كرد
        return response()->json($message);
    }

    /**
     * استرجاع جميع الرسائل الخاصة بمحادثة معينة.
     *
     * @param  int  $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages($conversationId)
    {
        // استرجاع جميع الرسائل مع تحميل أسماء المرسل والمستلم
        $messages = Message::with(['sender', 'receiver'])
            ->where('conversation_id', $conversationId)
            ->get();

        // تجهيز الريسبونس
        return response()->json([
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name ?? null,
                    'receiver_id' => $message->receiver_id,
                    'receiver_name' => $message->receiver->name ?? null,
                    'message' => $message->message,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at,
                ];
            }),
        ]);
    }


    /**
     * تعيين الرسالة على أنها تم قراءتها.
     *
     * @param  int  $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($messageId)
    {
        // العثور على الرسالة باستخدام معرف الرسالة
        $message = Message::findOrFail($messageId);

        // تحديث حالة الرسالة إلى "تم قراءتها"
        $message->update(['is_read' => true]);

        // إرجاع رد بتأكيد تحديث الحالة
        return response()->json(['message' => 'Message marked as read']);
    }

    public function latestConversations(Request $request)
    {
        // نستخرج آخر رسالة من كل محادثة يكون المستخدم طرف فيها
        $userId = $request->user()->id;

        $latestMessages = Message::select(DB::raw('MAX(id) as id'))
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->groupBy('conversation_id')
            ->latest()
            ->take(3)
            ->pluck('id');

        // نجيب الرسائل نفسها مع معلومات المرسل والمستلم
        $messages = Message::with(['sender', 'receiver'])
            ->whereIn('id', $latestMessages)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'latest_conversations' => $messages->map(function ($message) {
                $senderImage = $message->sender->img ?? null;
                $receiverImage = $message->receiver->img ?? null;
                return [
                    'conversation_id' => $message->conversation_id,
                    'sender_name' => $message->sender->name ?? null,
                    'sender_image' => $senderImage ? 'https://final.agrovision.ltd/storage/photos/' . $senderImage : null,
                    'receiver_name' => $message->receiver->name ?? null,
                    'receiver_image' => $message->receiver->img
                        ? 'https://final.agrovision.ltd/storage/' . ltrim($message->receiver->img, '/')
                        : null,
                    'last_message' => $message->message,
                    'created_at' => $message->created_at,
                    'is_read' => $message->is_read,
                    'status' => 'Order pending', // يمكن تعديله من جدول آخر لو مطلوب
                    'priority' => 'High Priority', // أيضاً حسب الداتا
                ];
            }),
        ]);
    }
}
