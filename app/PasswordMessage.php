<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordMessage extends Model
{
    protected $table = 'password_messages';

    public function user() {

        return $this->belongsTo('App/User');

    }
}
