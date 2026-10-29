<?php

use Illuminate\Support\Facades\Log;

test('failed google callback logs the reason and returns to login', function () {
    Log::spy();

    // No OAuth state is stored in the session, so Socialite rejects the callback.
    $response = $this->get('/auth/google/callback?code=abc&state=xyz');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Unable to login with Google. Please try again.');

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $message === 'Google login failed'
            && ($context['exception'] ?? null) !== null)
        ->once();
});
