<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{

  protected $fillable = [
    'number', 'exp', 'cvv', 'type', 'bin', 'user_id'
  ];

  public function user() {
    return $this->belongsTo('App\User');
  }
}
