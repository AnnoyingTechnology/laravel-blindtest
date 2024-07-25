<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class ScoreIncrease implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $scores;
    /**
     * Create a new event instance.
     */
    public function __construct()
    {

		$this->scores = User::pluck('score', 'username')->toArray();

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
        return 'score.increase';
    }
}