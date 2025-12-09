<?php

namespace App\Models\Franchises;

use Illuminate\Database\Eloquent\Model;

class Franchise extends Model
{
    protected $table = 'franchises';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function generalInformation(){
        return $this->hasOne(FranchiseDetail::class,'franchise_id','id');
    }
}
