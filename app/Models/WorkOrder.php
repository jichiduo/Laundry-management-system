<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wo_no',
        'customer_id',
        'customer_name',
        'customer_tel',
        'customer_email',
        'customer_address',
        'currency',
        'base_currency',
        'exchange_rate',
        'explain',
        'weight',
        'total',
        'discount',
        'tax',
        'grand_total',
        'status',
        'pickup_date',
        'collect_date',
        'is_express',
        'user_id',
        'user_name',
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
        'exchange_rate' => 'decimal:8',
        'weight' => 'decimal:2',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'pickup_date' => 'datetime',
        'collect_date' => 'datetime',
        'is_express' => 'boolean',
        'user_id' => 'integer',
        'division_id' => 'integer',
        'group_id' => 'integer',
    ];
}
