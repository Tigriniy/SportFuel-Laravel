<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_email_allows_sign_in_with_new_password_once(): void
    {
        config(['app.url' => 'http://localhost']);
        $user = $this->createUser();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertSame($user->email, $email->getTo()[0]->getAddress());
        preg_match('~http://localhost/reset-password/([^?\s]+)\?email=[^\s)]+~', $email->getTextBody(), $link);
        $this->assertArrayHasKey(1, $link);

        $this->get($link[0])->assertOk()->assertSee($user->email);
        $data = [
            'token' => $link[1],
            'email' => $user->email,
            'password' => 'NewPassword!42',
            'password_confirmation' => 'NewPassword!42',
        ];

        $this->post(route('password.update'), $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check($data['password'], $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->from($link[0])
            ->post(route('password.update'), $data)
            ->assertSessionHasErrors('email');

        $this->post(route('login.store'), ['login' => $user->login, 'password' => 'OldPassword!42'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->post(route('login.store'), ['login' => $user->login, 'password' => $data['password']])
            ->assertRedirect(route('profile'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_blocked_and_unknown_addresses_get_no_email(): void
    {
        $user = $this->createUser();
        $user->forceFill(['is_blocked' => true])->save();

        foreach ([$user->email, 'unknown@example.com'] as $email) {
            $this->from(route('password.request'))
                ->post(route('password.email'), ['email' => $email])
                ->assertSessionHas('status', 'Если для этого адреса доступно восстановление, мы отправим ссылку.');
        }

        $this->assertCount(0, Mail::mailer('array')->getSymfonyTransport()->messages());
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    private function createUser(): User
    {
        return User::create([
            'login' => 'TestUser',
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'phone' => '79990001122',
            'email' => 'test@example.com',
            'password' => 'OldPassword!42',
        ]);
    }
}
