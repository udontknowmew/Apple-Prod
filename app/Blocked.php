<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blocked extends Model
{
  protected $fillable = [ 'ip' ];
}
