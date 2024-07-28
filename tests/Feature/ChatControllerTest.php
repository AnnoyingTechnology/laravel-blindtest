<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_authentication_is_required_to_access_chat(): void
    {
        $response = $this->get('/chat');
        $response->assertRedirect(route('login'));
    }

    // public function test_user_can_login()
    // {
    //     Event::fake();
    //     $response = $this->post(route('chat.login'), ['username' => 'testuser']);

    //     $response->assertRedirect(route('chat'));
    //     $this->assertAuthenticated();
    //     $this->assertDatabaseHas('users', ['username' => 'Testuser']);
    //     Event::assertDispatched(\App\Events\UserJoined::class);
    // }

    public function test_user_can_send_message()
    {
        Event::fake();
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('chat.send'), ['message' => 'Hello, world!']);
        Event::assertDispatched(\App\Events\UserMessage::class);
        $response->assertJson(['success' => true]);
    }
}
