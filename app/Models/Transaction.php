<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trans_no',
        'wo_no',
        'customer_id',
        'customer_name',
        'card_no',
        'amount',
        'payment_type',
        'type',
        'remark',
        'create_by',
        'division_id',
        'division_name',
        'group_id',
        'group_name',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer',
        'amount' => 'decimal:2',
        'division_id' => 'integer',
        'group_id' => 'integer',
    ];
}
