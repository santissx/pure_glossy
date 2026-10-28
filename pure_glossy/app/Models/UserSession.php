<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $primaryKey = 'id_session';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'session_start',
        'session_end',
    ];
}