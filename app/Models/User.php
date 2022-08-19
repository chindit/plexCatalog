<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Ramsey\Uuid\Uuid;

/**
 * @property string server_url
 * @property string server_port
 * @property string server_token
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Authenticatable $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'server_url',
        'server_port',
        'server_token',
        'api_token',
        'last_sync',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'server_port' => 'int',
        'last_sync' => 'datetime'
    ];
}
