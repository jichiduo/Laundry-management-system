<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'division_id',
        'division_name',
        'customer_id',
        'customer_name',
        'customer_tel',
        'customer_email',
        'customer_address',
        'credit_term',
        'currency',
        'base_currency',
        'exchange_rate',
        'explain',
        'remark',
        'weight',
        'total',
        'discount',
        'tax',
        'grand_total',
        'submit_by_userid',
        'submit_by_username',
        'submit_date',
        'status',
        'pickup_date',
        'group_id',
        'group_name',
        'delivery_status',
        'export_tag',
        'export_date',
        'user_id',
        'app_group_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'division_id' => 'integer',
        'customer_id' => 'integer',
        'exchange_rate' => 'decimal:8',
        'weight' => 'decimal:2',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'submit_by_userid' => 'integer',
        'submit_date' => 'datetime',
        'pickup_date' => 'datetime',
        'group_id' => 'integer',
        'export_tag' => 'boolean',
        'export_date' => 'datetime',
        'user_id' => 'integer',
        'app_group_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AppUser::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function appGroup(): BelongsTo
    {
        return $this->belongsTo(AppGroup::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function submitByUserid(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AppUser::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AppGroup::class);
    }

    public function workOrderItems(): HasMany
    {
        return $this->hasMany(WorkOrderItems::class);
    }
}
