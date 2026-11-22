<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_or_update_profile(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
        $this->patch(route('profile.update'))->assertRedirect(route('login'));
        $this->put(route('profile.password.update'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_and_update_their_own_profile(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser('OtherUser', 'other@example.com');
        $this->actingAs($user)->get(route('profile'))
            ->assertOk()
            ->assertSee('Иванов Иван Иванович')
            ->assertSee($user->email)
            ->assertSee('Не указан');

        $data = [
            'last_name' => 'Петров',
            'first_name' => 'Пётр',
            'patronymic' => 'Петрович',
            'email' => $user->email,
            'phone' => '01234567890',
            'delivery_address' => 'Томск, проспект Ленина, 1',
        ];

        $this->patch(route('profile.update'), $data + ['id' => $otherUser->id, 'role' => 'admin'])
            ->assertRedirect(route('profile'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', $data + ['id' => $user->id, 'role' => 'user']);
        $this->assertDatabaseHas('users', ['id' => $otherUser->id, 'last_name' => 'Иванов']);

        $this->patch(route('profile.update'), array_replace($data, ['delivery_address' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->delivery_address);
    }

    public function test_email_change_requires_current_password_and_an_unused_email(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser('OtherUser', 'other@example.com');
        Password::createToken($user);

        $data = [
            'last_name' => $user->last_name,
            'first_name' => $user->first_name,
            'patronymic' => $user->patronymic,
            'phone' => $user->phone,
            'email' => 'new@example.com',
        ];

        $this->actingAs($user)->from(route('profile'))
            ->patch(route('profile.update'), $data)
            ->assertSessionHasErrorsIn('profile', 'current_password');

        $this->patch(route('profile.update'), $data + ['current_password' => ''])
            ->assertSessionHasErrorsIn('profile', 'current_password');
        $this->withCookie(config('session.cookie'), session()->getId())
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Для смены email введите текущий пароль.')
            ->assertDontSee('new@example.com');
        $this->assertSame('test@example.com', $user->fresh()->email);

        $this->patch(route('profile.update'), array_replace($data, [
            'email' => $otherUser->email,
            'current_password' => 'OldPassword!42',
        ]))->assertSessionHasErrorsIn('profile', 'email');
        $this->assertSame('test@example.com', $user->fresh()->email);

        $this->patch(route('profile.update'), $data + ['current_password' => 'OldPassword!42'])
            ->assertSessionHasNoErrors();
        $this->assertSame('new@example.com', $user->fresh()->email);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'test@example.com']);
    }

    public function test_password_change_checks_current_password_and_requires_a_new_login(): void
    {
        $user = $this->createUser();
        Password::createToken($user);
        $data = [
            'current_password' => 'WrongPassword!42',
            'password' => 'NewPassword!42',
            'password_confirmation' => 'NewPassword!42',
        ];

        $this->actingAs($user)->from(route('profile'))
            ->put(route('profile.password.update'), $data)
            ->assertSessionHasErrorsIn('password', 'current_password');
        $this->assertTrue(Hash::check('OldPassword!42', $user->fresh()->password));

        $data['current_password'] = 'OldPassword!42';
        $this->put(route('profile.password.update'), array_replace($data, ['password_confirmation' => 'Mismatch!42']))
            ->assertSessionHasErrorsIn('password', 'password');

        $this->put(route('profile.password.update'), $data)->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->post(route('login.store'), ['login' => $user->login, 'password' => 'OldPassword!42'])
            ->assertSessionHasErrors('login');
        $this->post(route('login.store'), ['login' => $user->login, 'password' => 'NewPassword!42'])
            ->assertRedirect(route('profile'));
        $this->assertAuthenticatedAs($user);
    }

    private function createUser(string $login = 'TestUser', string $email = 'test@example.com'): User
    {
        return User::create([
            'login' => $login,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'email' => $email,
            'phone' => '79990001122',
            'password' => 'OldPassword!42',
        ]);
    }
}
