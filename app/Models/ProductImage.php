<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable=['color_id','image'];

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}
