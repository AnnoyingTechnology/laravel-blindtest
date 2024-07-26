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
	public $remix;
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
		"You must be kidding, right?",
		"Off-key disaster",
		"Tone-deaf attempt",
		"Rhythm? Never heard of it",
		"Stick to humming in the shower",
		"Even a broken record sounds better",
		"You make nails on a chalkboard sound pleasant",
		"Did your brain go on mute?",
		"More off-key than a drunk karaoke night",
		"Did someone hit the wrong note in your brain?",
		"Your ears must be broken",
		"Do you even know what music is?",
		"Pitchy disaster",
		"That performance was out of tune",
		"Better check your hearing",
		"Musically challenged, are we?",
		"Sounds like a cat in a blender",
		"Stop torturing the music",
		"Stick to playing air guitar",
		"Your sense of rhythm is a joke",
		"Tone-deaf and clueless",
		"You couldn't find the beat if it hit you",
		"Sounds like you’re tone-blind",
		"Worse than elevator music",
		"Even silence is better",
		"Is your brain on shuffle?",
		"You couldn’t carry a tune in a bucket",
		"Off the beat and off your rocker",
		"You've got the musicality of a rock",
		"Are you tone-deaf or just clueless?",
		"Your ears must be painted on",
		"Not even auto-tune could save you",
		"That was a flat-out disaster"
	];
    /**
     * Create a new event instance.
     */
    public function __construct(Track $track)
    {
        $this->name = $track->name;
		$this->artist = $track->artist;
		$this->remix = $track->remix;
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