<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'password',
        'tel',
        'email',
        'address',
        'member_card',
        'member_level_id',
        'member_level_name',
        'member_discount',
        'balance',
        'remark',
        'create_by',
        'update_by',
        'is_active',
        'group_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'member_level_id' => 'integer',
        'member_discount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'group_id' => 'integer',
    ];
}
