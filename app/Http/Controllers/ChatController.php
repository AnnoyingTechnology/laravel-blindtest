<?php
namespace App\Http\Controllers;
// events
use App\Events\{ UserMessage, UserJoined };
// models
use App\Models\{ User, Track };
// framework
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Authenticatable;

class ChatController extends Controller
{

	public function login(
		Request $request, 
		AuthFactory $auth
	) {
	
		// validate that a username has been posted
		$request->validate(['username' => 'required|string|min:2|max:12']); ;

		// find already existing user or create one
        $user = User::firstOrCreate(['username' => $request->input('username')]);

		// authenticate as that user
        $auth->login($user);

		// notify that a user has just joined
		UserJoined::dispatch($user);

		// go to the chatroom
        return redirect(route('chat'));

	}

	// we received a new message
    public function sendMessage(
		Request $request, 
		Authenticatable $user
	) {

		// validate the contents
		$request->validate(['message' => 'required|string|max:512']);

		// broadcast the message to webhooks and event listeners
		UserMessage::dispatch($request->input('message'), $user);

		// answer that's we're fine
        return response()->json(['success' => true]);

    }

}
