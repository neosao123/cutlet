<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens,HasFactory,Notifiable;
    protected $guard = 'admins';
    protected $table = 'usermaster';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	
	
    protected $fillable = [
        'username', 'userEmail', 'password','role','code',
    ];
	
	 /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
	

    
}
