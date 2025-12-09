<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponser;
    public function getUser(Request $request){
        $user = $request->user();
        return  $this->successResponse($user);
    }

    public function getUserDetails($id){
        $getUser = User::where('id',$id)->where('is_deleted',0)->first();
        if($getUser){
            return $this->successResponse($getUser,'User Details fetched successfully',200);
        }else{
            return $this->errorResponse('User details not found',404);
        }

    }
}
