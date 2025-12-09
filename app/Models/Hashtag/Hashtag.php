<?php

namespace App\Models\Hashtag;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    protected $table = "hashtags";
    protected $primaryKey = 'id';
    protected $fillable = ['hashtag_name','is_deleted'];
    // protected $gaurded = [];
}
