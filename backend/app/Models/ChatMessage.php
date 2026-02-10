<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'session_id',
        'sender',
        'message',
        'needs_admin',
        'is_closed',
        'chat_message_user_id'
    ];
}
