<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Restaurants extends Authenticatable
{
    //use HasFactory;
	use Notifiable;
	protected $guard = 'restaurants';
	protected $table = 'restaurant';
}
