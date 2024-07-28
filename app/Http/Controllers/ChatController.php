<?php
namespace App\Http\Controllers;
// events
use App\Events\{ 
	// user related
	UserMessage,
	UserJoined,
	// track related
	TrackNew,
	TrackFound,
	TrackGiveup, 
	TrackClues,
	TrackFastForward,
	// score related
	ScoresReset,
	ScoresIncrease, 		// rename -> ScoresIncrease
};
// models
use App\Models\{ User, Track };
// framework
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{

	public function login(Request $request) {
	
		$request->validate(['username' => 'required|string']);

        $username = $request->input('username');

        $user = User::firstOrCreate(['username' => $username]);

        Auth::login($user);

		// notify that a user has just joined
		UserJoined::dispatch($user);

        return redirect()->intended(route('chat'));

	}

	// we received a new message
    public function sendMessage(Request $request)
    {

		// get the contents
		$request->validate(['message' => 'required|string']);
        $message 	= $request->input('message');
		$username 	= Auth::user()->username;
		$color		= Auth::user()->color;
		$uuid 		= Str::uuid();

		// dispatch the message to all watchers
        UserMessage::dispatch($message, $username, $uuid, $color);

		// if we have a next instructions
		if(str_starts_with($message, '/next')) {
			$this->next($message);
		} else {
			match ($message) {
				'/ff' 		=> $this->fastforward(),
				'/clue' 	=> $this->clue(),
				'/giveup' 	=> $this->giveup(),
				'/reset' 	=> $this->reset(),
				default 	=> mb_strlen($message) >= 3 ? 
					$this->match($message, $username, $uuid) : 
					null,
			};
		}

        return response()->json(['success' => true]);
    }

	private function clue() :void {

		// retrieve the current track
		$track = Track::getCurrent();
		// get the clues
		$clues = $track->getClues();
		// dispatch the clue event
		TrackClues::dispatch(...$clues);

	}

	private function giveup() :void {

		// retrive the current track
		$track = Track::getCurrent();
		// mark all as found to prevent cheating afterwards
		$track->giveup()->save();
		// broadcast the giveup event
		TrackGiveup::dispatch($track);
		
	}

	private function match(
		string $message, 
		string $username, 
		string $uuid
	) :void {

		// get the current track
		$matches = Track::getCurrent()
			// attempt to match it with an answer
			->match($message);

		if($matches) {

			// broadcast a found event
			TrackFound::dispatch(
				$username,
				$matches['found'],
				$matches['score'],
				$uuid
			);

			// broadcast a score increase event
			ScoresIncrease::dispatch();
		}

	}

	private function reset() :void {

		// reset users scores
		User::ScoresReset();
		// broadcast the event
		ScoresReset::dispatch();

	}

	private function fastforward() :void {

		// dispatch a fast forward event
		TrackFastForward::dispatch();

	}

	private function next(string $message) :void {

		// set and retrieve a random new track of the proper genre
		$track = Track::setAndGetRandom(
			// extract parameters
			collect(explode(' ', $message))
				->skip(1)
				->values()
				->all()
		);
		// dispatch a TrackNew event
		TrackNew::dispatch($track);

	}

}
