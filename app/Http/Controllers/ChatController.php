<?php
namespace App\Http\Controllers;
use App\Events\MessageSent;
use App\Events\NewTrack;
use App\Events\FoundTrack;
use App\Events\TrackGiveup;
use App\Events\FastForwardTrack;
use App\Events\UserJoined;
use App\Events\ResetScores;
use App\Events\ScoreIncrease;
use App\Models\User;
use App\Models\Track;
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
    public function store(Request $request)
    {

		// get the contents
		$request->validate(['message' => 'required|string']);
        $message 	= $request->input('message');
		$username 	= Auth::user()->username;
		$color		= Auth::user()->color;
		$uuid 		= Str::uuid();

		// dispatch it to all watchers
        MessageSent::dispatch($message, $username, $uuid, $color);

		// if the message ask for the next track
		if(str_starts_with($message, '/next')) {

			// set and retrieve a random new track of the proper genre
			$track = Track::setAndGetRandom(
				// extract parameters
				collect(explode(' ', $message))
					->skip(1)
					->values()
					->all()
			);

			NewTrack::dispatch($track);

		}
		// fast forward in the current track by x seconds
		elseif($message == '/ff') {
			FastForwardTrack::dispatch();
		}
		// give up and displays the track info
		elseif($message == '/giveup') {
		
			// retrive the current track
			$track = Track::getCurrent();
			// mark all as found to prevent cheating afterwards
			$track->is_name_found = 1;
			$track->is_artist_found = 1;
			$track->is_remix_found = 1;
			$track->save();
			// broadcast the giveup event
			TrackGiveup::dispatch($track);

		}
		// resets the scoreboard
		elseif($message == '/reset') {
		
			// reset users scores
			User::resetScores();
			// broadcast the event
			ResetScores::dispatch();

		}
		elseif(mb_strlen($message) >= 3) {
		
			// get the current track
			$matches = Track::getCurrent()
				// attempt to match it with an answer
				->match($message);
		
			if($matches) {
				// broadcast a found event
				FoundTrack::dispatch(
					$username,
					$matches['found'],
					$matches['score'],
					$uuid
				);

				// broadcast a score increase event
				ScoreIncrease::dispatch();
			}

		}

        return response()->json(['success' => true, 'message' => 'Message sent successfully']);
    }


}
