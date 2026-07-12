<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Registration and verification updates contain account data, so only admins
// may join this channel. The role check is repeated server-side on actions.
Broadcast::channel('admin.accounts', function (User $user) {
    return $user->account_type === 'admin';
});

// Only the two participants may subscribe to a private conversation channel.
Broadcast::channel('chat.conversation.{conversation}', function (User $user, Conversation $conversation) {
    return $conversation->hasParticipant($user);
});
