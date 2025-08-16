<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getConversations(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where('user_one', $user->id)
            ->orWhere('user_two', $user->id)
            ->with(['userOne', 'userTwo', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser = $conversation->getOtherUser($user->id);
                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'email' => $otherUser->email,
                    ],
                    'latest_message' => $conversation->latestMessage ? [
                        'message' => $conversation->latestMessage->message,
                        'created_at' => $conversation->latestMessage->created_at,
                        'sender_id' => $conversation->latestMessage->sender_id,
                    ] : null,
                    'unread_count' => $conversation->unreadMessagesCount($user->id),
                    'last_message_at' => $conversation->last_message_at,
                ];
            });

        return response()->json($conversations);
    }

    public function getMessages(Request $request, $conversationId)
    {
        $user = $request->user();

        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('user_one', $user->id)
                      ->orWhere('user_two', $user->id);
            })
            ->firstOrFail();

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_id' => $message->sender_id,
                    'created_at' => $message->created_at,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                    ],
                ];
            });

        // Mark messages as read
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $receiverId = $request->receiver_id;

        if ($user->id === $receiverId) {
            return response()->json(['error' => 'Cannot send message to yourself'], 400);
        }

        // Find or create conversation
        $conversation = Conversation::findBetweenUsers($user->id, $receiverId);

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one' => min($user->id, $receiverId),
                'user_two' => max($user->id, $receiverId),
                'last_message_at' => now(),
            ]);
        } else {
            $conversation->update(['last_message_at' => now()]);
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        $message->load('sender');

        // Broadcast the message
        broadcast(new MessageSent($message));

        return response()->json([
            'id' => $message->id,
            'message' => $message->message,
            'sender_id' => $message->sender_id,
            'conversation_id' => $message->conversation_id,
            'created_at' => $message->created_at,
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
            ],
        ], 201);
    }

    public function startConversation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $otherUserId = $request->user_id;

        if ($user->id === $otherUserId) {
            return response()->json(['error' => 'Cannot start conversation with yourself'], 400);
        }

        // Find or create conversation
        $conversation = Conversation::findBetweenUsers($user->id, $otherUserId);

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one' => min($user->id, $otherUserId),
                'user_two' => max($user->id, $otherUserId),
            ]);
        }

        $conversation->load(['userOne', 'userTwo']);
        $otherUser = $conversation->getOtherUser($user->id);

        return response()->json([
            'id' => $conversation->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
            ],
            'unread_count' => $conversation->unreadMessagesCount($user->id),
        ]);
    }

    public function userTyping(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'is_typing' => 'required|boolean',
        ]);

        $user = $request->user();
        $conversationId = $request->conversation_id;

        // Verify user is part of the conversation
        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('user_one', $user->id)
                      ->orWhere('user_two', $user->id);
            })
            ->firstOrFail();

        broadcast(new UserTyping($user, $conversationId, $request->is_typing));

        return response()->json(['status' => 'success']);
    }
}
