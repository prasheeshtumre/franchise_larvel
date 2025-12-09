<?php

namespace App\Models\SocialMedia;

use App\Models\Franchises\FranchiseDetail;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    protected $table = 'franchises';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function generalInformation(){
        return $this->belongsTo(FranchiseDetail::class,'franchise_details_id','id');
    }
}
