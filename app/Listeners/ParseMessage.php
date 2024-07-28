<?php 
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\{ User, Track };
use App\Events\{ 
    UserMessage,
    UserJoined,
	TrackNew,
	TrackFound,
	TrackGiveup, 
	TrackClues,
	TrackFastForward,
	ScoresReset,
	ScoresIncrease,
};

class ParseMessage {

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(UserMessage $event) :void {
        $message = $event->message;

        if (str_starts_with($message, '/next')) {
            $this->next($message);
        } else {
            match ($message) {
                '/ff'      => $this->fastforward(),
                '/clue'    => $this->clue(),
                '/giveup'  => $this->giveup(),
                '/reset'   => $this->reset(),
                default    => mb_strlen($message) >= 3 ? 
                    $this->match($message, $event->username, $event->uuid) : 
                    null,
            };
        }
    }

    private function clue() :void {
        $track = Track::getCurrent();
        $clues = $track->getClues();
        TrackClues::dispatch(...$clues);
    }

    private function giveup() :void {
        $track = Track::getCurrent();
        $track->giveup()->save();
        TrackGiveup::dispatch($track);
    }

    private function match(
        string $message, 
        string $username, 
        string $uuid
    ) :void {
        $matches = Track::getCurrent()->match($message);
        if ($matches) {
            TrackFound::dispatch(
                $username, 
                $matches['found'], 
                $matches['score'], 
                $uuid
            );
            ScoresIncrease::dispatch();
        }
    }

    private function reset() :void {
        User::resetScores();
        ScoresReset::dispatch();
    }

    private function fastforward() :void {
        TrackFastForward::dispatch();
    }

    private function next(string $message) :void {
        $track = Track::setAndGetRandom(
            collect(explode(' ', $message))
                ->skip(1)
                ->values()
                ->all()
            );
        TrackNew::dispatch($track);
    }
}
