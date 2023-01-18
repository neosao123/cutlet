<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Restaurants extends Authenticatable
{
    //use HasFactory;
	use HasApiTokens, HasFactory, Notifiable;
	protected $guard = 'restaurants';
	protected $table = 'restaurant';
}
