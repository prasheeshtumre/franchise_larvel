<?php

namespace App\Models\Franchises;

use Illuminate\Database\Eloquent\Model;

class FranchiseDetail extends Model
{
    protected $table = 'franchise_details';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function franchise(){
        return $this->belongsTo(Franchise::class,'id','franchise_id');
    }
}
