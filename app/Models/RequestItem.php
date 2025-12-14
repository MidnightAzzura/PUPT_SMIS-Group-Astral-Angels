<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;

    protected $table = 'requests'; // Table name

    protected $fillable = [
        'username',
        'action',
        'supply_name',
        'supply_description',
        'supply_quantity',
        'status',
    ];

    public $timestamps = false; // Since we only have `created_at`

    // Define relationships if needed
    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
