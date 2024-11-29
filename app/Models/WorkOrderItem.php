<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wo_no',
        'barcode',
        'name',
        'description',
        'quantity',
        'unit',
        'price',
        'total',
        'discount',
        'tax_rate',
        'tax',
        'sub_total',
        'turnover',
        'acc_code',
        'acc_name',
        'status',
        'remark',
        'location',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'quantity' => 'decimal:2',
        'price' => 'decimal:4',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'turnover' => 'decimal:2',
    ];
}
