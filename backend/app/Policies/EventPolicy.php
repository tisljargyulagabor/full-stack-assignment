<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Csak a tulajdonos vagy az admin láthatja/módosíthatja az eseményt.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->id === $event->event_user_id || $user->isAdmin();
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->id === $event->event_user_id || $user->isAdmin();
    }
}
