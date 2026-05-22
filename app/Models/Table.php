<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $fillable = [
        'number',
        'qr_code',
        'status',
    ];

    /**
     * Get the orders for the table.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
