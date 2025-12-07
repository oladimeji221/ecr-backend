<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LikePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Like $like)
    {
        return $user->id === $like->user_id;
    }
}
