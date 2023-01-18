<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    //use HasFactory;
	use HasApiTokens, HasFactory, Notifiable;
	protected $guard = 'customer';
	protected $table = 'clientmaster';
}
