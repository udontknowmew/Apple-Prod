<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  protected $fillable = ['username', 'password', 'email_result', 'is_smtp', 'smtp_server', 'smtp_port', 'smtp_user', 'smtp_password', 'token'];
}
