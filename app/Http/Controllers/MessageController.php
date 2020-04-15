<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PrivateMessageEvent;
use App\Message;
use App\User;
use App\UserMessageGroup;

class MessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function chat($id)
    {
        $friend = User::find($id);
        return view('chat', compact('friend'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'receiver_id' => 'required'
        ]);

        $sender_id = auth()->user()->id;
        $receiver_id = $request->receiver_id;

        $message = new Message;
        $message->message = $request->message;

        if ($message->save()) {
            try {
                $message->users()->attach($sender_id, ['receiver_id' => $receiver_id]);
                $sender = User::where('id', '=', $sender_id)->first();
                $data = [];
                $data['sender_id'] = $sender_id;
                $data['sender_name'] = $sender->name;
                $data['receiver_id'] = $receiver_id;
                $data['content'] = $message->message;
                $data['type'] = 1;
                $data['in'] = true;
                $data['content_type'] = "text";

                event(new PrivateMessageEvent($data));

                return response()->json([
                    'data' => [],
                    'success' => true,
                    'message' => 'Message sent successfully.'
                ]);
            } catch (\Exception $e) {
                $message->delete();
            }
        }
    }
}
