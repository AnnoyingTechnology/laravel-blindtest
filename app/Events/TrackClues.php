<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class TrackClues implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
	
    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $name,
        public string $artist,
        public ?string $remix = null
    ) {}
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
        return 'track.clues';
    }
}