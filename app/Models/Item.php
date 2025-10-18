<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'stock',
        'unit',
        'threshold',
    ];

    protected $casts = [
        'stock' => 'integer',
        'threshold' => 'integer',
    ];

    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Check if stock is low
    public function isLowStock()
    {
        return $this->stock <= $this->threshold;
    }
}