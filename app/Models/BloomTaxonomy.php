<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloomTaxonomy extends Model
{
    use HasFactory;

    protected $table = 'bloom_taxonomies';

    protected $fillable = [
        'name',
        'code',
        'sort_order',
    ];
}
