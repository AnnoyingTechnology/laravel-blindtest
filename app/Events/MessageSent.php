<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message;
	public $username;
	public $uuid;
    /**
     * Create a new event instance.
     */
    public function __construct(
		$message, 
		$username,
		$uuid
	)
    {
        $this->message = $message;
		$this->username = $username;
		$this->uuid = $uuid;
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
        return 'message.sent';
    }
}