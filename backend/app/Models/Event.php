<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'event_id';

    public $timestamps = false;

    protected $fillable = [
        'event_user_id',
        'event_name',
        'event_date',
        'event_description',
        'event_created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
