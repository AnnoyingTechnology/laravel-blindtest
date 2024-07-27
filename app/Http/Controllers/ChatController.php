<?php
namespace App\Http\Controllers;
// events
use App\Events\{ 
	MessageSent, 		// rename -> UserMessage
	UserJoined,
	NewTrack, 			// rename -> TrackNew
	FoundTrack, 		// rename -> TrackFound
	TrackGiveup, 
	TrackClues,
	FastForwardTrack, 	// rename -> TrackFastForward
	ResetScores, 		// rename -> ScoresReset
	ScoreIncrease, 		// rename -> ScoresIncrease
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
    public function store(Request $request)
    {

		// get the contents
		$request->validate(['message' => 'required|string']);
        $message 	= $request->input('message');
		$username 	= Auth::user()->username;
		$color		= Auth::user()->color;
		$uuid 		= Str::uuid();

		// dispatch the message to all watchers
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
			// dispatch a newtrack event
			NewTrack::dispatch($track);

		}
		// fast forward in the current track by x seconds
		elseif($message == '/ff') {
			// dispatch a fast forward event
			FastForwardTrack::dispatch();
		}
		// give a clue
		elseif($message == '/clue') {

			// retrieve the current track
			$track = Track::getCurrent();
			// get the clues
			$clues = $track->getClues();
			// dispatch the clue event
			TrackClues::dispatch(...$clues);

		}
		// give up and displays the track info
		elseif($message == '/giveup') {

			// retrive the current track
			$track = Track::getCurrent();
			// mark all as found to prevent cheating afterwards
			$track->giveup()->save();
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

        return response()
			->json([
				'success' => true, 
				'message' => 'Message sent successfully'
			]);
    }


}
