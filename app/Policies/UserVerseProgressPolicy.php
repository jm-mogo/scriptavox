<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserVerseProgress;
use Illuminate\Auth\Access\Response;

class UserVerseProgressPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserVerseProgress $userVerseProgress): bool
    {
        // A user can view a progress record if and only if
        // the user_id on the record matches their own id.
        return $user->id === $userVerseProgress->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserVerseProgress $userVerseProgress): bool
    {
        // The logic is the same for updating.
        return $user->id === $userVerseProgress->user_id;
    }

    // You can leave the other methods (create, delete, etc.) as they are for now.
}
