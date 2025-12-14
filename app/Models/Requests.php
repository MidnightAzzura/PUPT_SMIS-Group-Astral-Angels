<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requests extends Model
{
    use HasFactory;
    
    protected $table = 'requests';

    protected $fillable = [
        'user_id',
        'id', 
        'StockNumber',
        'Office', 
        'supply_name', 
        'Purpose', 
        'supply_quantity',
        'status',
        'Unit',
        'issue_quantity',
        'stock_available',
        'remarks',
        'received_by',
        'received_position',
        'ris_file_path',
        'approved_at',
        'updated_by',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    /**
     * Check if the request has expired
     */
    public function isExpired(): bool
    {
        if ($this->status !== 'Approved' || !$this->approved_at) {
            return false;
        }

        $expirationDate = $this->approved_at->copy()->addDays(5);
        return now()->greaterThan($expirationDate);
    }

    /**
     * Get the number of days until expiration
     */
    public function daysUntilExpiration(): ?int
    {
        if ($this->status !== 'Approved' || !$this->approved_at) {
            return null;
        }

        $expirationDate = $this->approved_at->copy()->addDays(5);
        $diff = now()->diffInDays($expirationDate, false);
        
        return $diff >= 0 ? (int)$diff : 0;
    }

    /**
     * Get the expiration date
     */
    public function getExpirationDate(): ?string
    {
        if ($this->status !== 'Approved' || !$this->approved_at) {
            return null;
        }

        return $this->approved_at->copy()->addDays(5)->format('Y-m-d H:i:s');
    }
}
