<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;


#[Fillable([
    'name',
    'email',
    'password',
    'role',
])]

#[Hidden([
    'password',
    'remember_token',
])]

class User extends Authenticatable
{

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;




    /**
     * Attributes Casts
     */
    protected function casts(): array
    {
        return [

            'email_verified_at' => 'datetime',

            'password' => 'hashed',

            'must_change_password' => 'boolean',

        ];
    }





    /**
     * User has one Employee
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }





    /**
     * Role Helpers
     */


    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }



    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }



    public function isHr(): bool
    {
        return $this->role === 'hr';
    }



    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }


}