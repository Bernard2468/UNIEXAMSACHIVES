<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\Broadcast;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Mail;

class BroadcastController extends Controller
{
    public function send(Request $request){
        $title = $request->input('title');
        $body = $request->input('body');
        $users = User::where('is_admin', 1)->where('is_approve', 1)->get();

        Message::create($request->all());

        foreach ($users as $user) {
            Mail::to($user->email)->send(new Broadcast($title,$body));
        }
        return redirect()->route('dashboard')->with('success', 'Broadcast Message sent successfully.');
    }

    public function read($id){
        $message = Message::findOrFail($id);
        $message->update([
            'is_read' => true,
        ]);
        $message->users()->attach(auth()->user()->id, ['is_read' => true]);

        return view('admin.view_message',[
            'message' => $message,
        ]);
    }
}
