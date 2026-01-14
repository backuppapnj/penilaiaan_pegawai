<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create([
        'nip' => 'test@example.com',
    ]);

    $this->post(route('password.email'), ['nip' => $user->nip])->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create([
        'nip' => 'test@example.com',
    ]);

    $this->post(route('password.email'), ['nip' => $user->nip]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create([
        'nip' => 'test@example.com',
    ]);

    $this->post(route('password.email'), ['nip' => $user->nip]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'nip' => $user->nip,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard-redirect', absolute: false));

        return true;
    });
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create([
        'nip' => 'test@example.com',
    ]);

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'nip' => $user->nip,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors();
});