<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $table = 'supply';

    // 2) Tell Eloquent that stock_number is the primary key:
    protected $primaryKey = 'StockNumber';
public $incrementing = false;
protected $keyType = 'string';


    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

      // ← Add this:
    public $timestamps = false;


    protected $fillable = [
        'StockNumber',
        'ItemDescription',
        'Unit',
        'Quantity',
        'Status',
        'Category'
    ];
}
