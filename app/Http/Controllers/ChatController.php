<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    public function index()
    {
        return view('chat'); // We'll create this view
    }

    public function fetchMessages()
    {
        $messages = Message::with('sender')
            ->latest()
            ->take(50)
            ->get()
            ->reverse();

        return response()->json($messages->values()); // Ensures it's a clean indexed array
    }

    public function sendMessage(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => null, // Or set to specific user ID for private
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return ['status' => 'Message sent!'];
    }
}