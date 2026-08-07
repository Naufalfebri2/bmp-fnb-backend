<?php

namespace App\Observers;

use App\Models\User;
use InvalidArgumentException;

class UserObserver
{
    public function saving(User $user): void
    {
        if ($user->role === 'manager' && !$user->outlet_id) {
            throw new InvalidArgumentException('A user with the manager role must be assigned to an outlet.');
        }
    }
}