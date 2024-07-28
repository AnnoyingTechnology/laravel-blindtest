<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use App\Models\User;

class UserMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;
    public string $username;
    public string $uuid;
    public string $color;
    /**
     * Create a new event instance.
     */
    public function __construct(
		string $message, 
		User $user
	) {
        $this->message  = $message;
        $this->username = $user->username;
		$this->color	= $user->color;
		$this->uuid 	= Str::uuid();
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
        return 'user.message';
    }
}