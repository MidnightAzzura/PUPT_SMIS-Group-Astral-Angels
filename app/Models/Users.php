<?php

namespace App\Models;

use App\Http\Middleware\Authenticate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;


class Users extends Authenticatable
{

    use HasFactory, Notifiable, HasRoles, HasPermissions;

    protected $guard_name = 'web'; // Ensure this is set to match the guard used in your application
    protected $table = 'users';

    protected $fillable = ['id' , 'first_name', 'middle_initial', 'last_name', 'office', 'email', 'password', 'role'];

    protected $hidden = ['password'];


    // Remove the cast to allow Spatie permissions relationship to work properly
    // protected $casts = [
    //     'permissions' => 'array', // Cast permissions to array
    // ];
    

    
}
