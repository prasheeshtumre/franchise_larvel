<?php

namespace App\Http\Controllers\Hashtag;

use App\Http\Controllers\Controller;
use App\Models\Hashtag\Hashtag;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HashtagController extends Controller
{
    use ApiResponser;

    public function storeHashtag(Request $request){
        $validator = Validator::make($request->all(), [
            'hashtag_name' => 'required|unique:categories,name',
        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $storeHastag = Hashtag::create([
            'hashtag_name'=>trim($request->hashtag_name),
            'is_deleted'=>0
        ]);
        if($storeHastag){
            return $this->successResponse($storeHastag,'Created hashtag Successfully',200);
        }else{
            return $this->errorResponse('Unable to create hashtag',401);
        }
    }

    public function updateHashtag(Request $request,$id){
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'hashtag_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('hashtags')->ignore($id) // Ensure uniqueness, ignoring the current
            ]

        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the hashtag by its ID
        $hashtag = Hashtag::find($id);

        // Check if the hashtag exists
        if (!$hashtag) {
            return $this->errorResponse('Hashtag not found',404);
        }

         // Update the hashtag record with new data
        $hashtag->update([
            'hashtag_name'=>trim($request->hashtag_name),
        ]);
        if($hashtag){
            return $this->successResponse($hashtag,'Created Hashtag Updated',200);
        }else{
            return $this->errorResponse('Unable to update Hashtag',401);
        }
    }
    public function getHashtag(){
        $getHashtag = Hashtag::where('is_deleted',0)->get();
        if($getHashtag){
            return $this->successResponse($getHashtag,'Hashtag Fetched Successfully',200);
        }else{
            return $this->errorResponse('Unable to fetch Hashtag',401);
        }

    }
    public function deleteHashtag($id)
    {
        // Find the Hashtag by its ID
        $hashtag = Hashtag::find($id);

        // Check if the Hashtag exists
        if (!$hashtag) {
            return response()->json(['message' => 'Hashtag not found'], 404);
        }

        // Delete the hashtag record
         $hashtag->update([
            'is_deleted' => 1
        ]);

        // Return a success response
        if($hashtag){
            return $this->successResponse($hashtag,'Hashtag deleted Successfully',200);
        }else{
            return $this->errorResponse('Unable to delete hashtag',401);
        }
    }
}
