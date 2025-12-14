<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'office',
        'page',
        'count',
        'last_updated'
    ];

    protected $casts = [
        'last_updated' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get or create a counter for office and page
     */
    public static function getCounter($office, $page)
    {
        return self::firstOrCreate(
            ['office' => $office, 'page' => $page],
            ['count' => 0, 'last_updated' => now()]
        );
    }    /**
     * Increment counter
     */
    public function incrementCounter($amount = 1)
    {
        $this->count += $amount;
        $this->last_updated = now();
        $this->save();
        return $this;
    }

    /**
     * Decrement counter
     */
    public function decrementCounter($amount = 1)
    {
        $this->count = max(0, $this->count - $amount);
        $this->last_updated = now();
        $this->save();
        return $this;
    }

    /**
     * Reset counter
     */
    public function reset()
    {
        $this->count = 0;
        $this->last_updated = now();
        $this->save();
        return $this;
    }

    /**
     * Set counter to specific value
     */
    public function setCount($count)
    {
        $this->count = max(0, $count);
        $this->last_updated = now();
        $this->save();
        return $this;
    }
}
