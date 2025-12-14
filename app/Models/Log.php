<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'Logs';
    public $timestamps = false;
    protected $fillable = ['timestamp', 'supply_name', 'description', 'quantity'];
}
