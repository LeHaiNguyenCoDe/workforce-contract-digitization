<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'product_id',
        'supplier_id',
        'inspector_id',
        'check_date',
        'status',
        'score',
        'notes',
        'issues'
    ];

    protected $casts = [
        'issues' => 'array',
        'check_date' => 'date',
        'score' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
