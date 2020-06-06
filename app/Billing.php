<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{

  protected $fillable = [
    'first_name', 'last_name', 'middle_name', 'address1', 'address2', 'state', 'city', 'postal', 'country', 'phone', 'user_id', 'addition'
  ];

  protected $hidden   = [
    ''
  ];

  public function user() {
    return $this->belongsTo('App\User');
  }
}
