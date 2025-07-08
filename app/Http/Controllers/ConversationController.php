<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     *
     * This method retrieves all conversations where the authenticated user is either user1 or user2.
     * It also loads the associated messages for each conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with(['messages', 'user1:id,name,img', 'user2:id,name,img'])
            ->get();

        return response()->json([
            'conversations' => $conversations->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'user1_id' => $conversation->user1_id,
                    'user1_name' => $conversation->user1->name ?? null,
                    'user1_img' => $conversation->user1->img ?? null,
                    'user2_id' => $conversation->user2_id,
                    'user2_name' => $conversation->user2->name ?? null,
                    'user2_img' => $conversation->user2->img ?? null,
                    'messages' => $conversation->messages,
                    'created_at' => $conversation->created_at,
                ];
            }),
        ]);
    }


    /**
     * Create or retrieve a conversation between two users.
     *
     * This method either creates a new conversation between the authenticated user (user1)
     * and the second user (user2), or retrieves an existing conversation if one already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'user2_id' => 'required|exists:users,id', // التأكد من وجود المستخدم الثاني
        ]);

        // محاولة إنشاء المحادثة إذا لم تكن موجودة مسبقًا
        $conversation = Conversation::firstOrCreate([
            'user1_id' => $request->user()->id, // المستخدم الحالي هو user1
            'user2_id' => $request->user2_id, // المستخدم الثاني
        ]);

        // إعادة المحادثة في استجابة JSON
        return response()->json($conversation);
    }
    public function destroy(Request $request, $id)
{
    $userId = $request->user()->id;

    // محاولة جلب المحادثة بشرط أن يكون المستخدم أحد أطرافها
    $conversation = Conversation::where('id', $id)
        ->where(function ($query) use ($userId) {
            $query->where('user1_id', $userId)
                  ->orWhere('user2_id', $userId);
        })
        ->first();

    // إذا لم توجد المحادثة أو المستخدم ليس طرفًا فيها
    if (!$conversation) {
        return response()->json(['error' => 'Conversation not found or access denied'], 404);
    }

    // حذف المحادثة (سيتم حذف الرسائل المرتبطة بها إذا كان هناك foreign key ON DELETE CASCADE)
    $conversation->delete();

    return response()->json(['message' => 'Conversation deleted successfully']);
}

}
