<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use App\Models\Track;
class TrackGiveup implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
	public $name;
	public $artist;
	public $insult;
	const insults = [
		"You wankers",
		"Nice try, butterfingers",
		"Better luck next time, losers",
		"Ouch, that was embarrassing",
		"Give it another shot, clowns",
		"Whoops, looks like you blew it",
		"That was a mess, folks",
		"Swing and a miss, amateurs",
		"Maybe next time, slowpokes",
		"Don't quit your day job",
		"You call that an attempt?",
		"Time for a reality check",
		"Pathetic effort, rookies",
		"Is that all you've got?",
		"Epic fail, genius",
		"A toddler could do better",
		"Total disaster, boneheads",
		"That was painful to watch",
		"Even my grandma could beat that",
		"You totally botched it",
		"What a trainwreck, dimwits",
		"Get it together, numbskulls",
		"Did you even try?",
		"Laughable attempt, weaklings",
		"Completely hopeless",
		"Amateurs at work",
		"That was just sad",
		"Epic botch job, champs",
		"Not even close",
		"You must be kidding, right?"
	];
    /**
     * Create a new event instance.
     */
    public function __construct(Track $track)
    {
        $this->name = $track->name;
		$this->artist = $track->artist;
		$this->insult = Arr::random(self::insults);
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('chatroom'),
        ];
    }
    public function broadcastAs(): string
    {
        return 'track.giveup';
    }
}