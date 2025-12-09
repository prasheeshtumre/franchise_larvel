<?php

namespace App\Http\Controllers\Industry;

use App\Http\Controllers\Controller;
use App\Models\cr;
use App\Models\Industry\Industry;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IndustryController extends Controller
{
    use ApiResponser;
    public function storeIndustry(Request $request){
        $validator = Validator::make($request->all(), [
            'fran_industry_name' => 'required|unique:industries,name'
        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $storeIndustry = Industry::create([
            'name'=>trim($request->fran_industry_name),
            'description'=>trim($request->description),
        ]);
        if($storeIndustry){
            return $this->successResponse($storeIndustry,'Created Industry Successfully',200);
        }else{
            return $this->errorResponse('Unable to create Industry',401);
        }
    }

    public function updateIndustry(Request $request,$id){
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('industries')->ignore($id) // Ensure uniqueness, ignoring the current franchise
            ]

        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the franchise by its ID
        $industry = Industry::find($id);

        // Check if the franchise exists
        if (!$industry) {
            return $this->errorResponse('Industry not found',404);
        }

         // Update the industry record with new data
        $industry->update([
            'name'=>trim($request->name),
            'description'=>trim($request->description),
        ]);
        if($industry){
            return $this->successResponse($industry,'Created Industry Updated',200);
        }else{
            return $this->errorResponse('Unable to create Industry',401);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/industries/delete/{id}",
     *     operationId="deleteIndustry",
     *     tags={"Industry"},
     *     summary="Delete a Industry (soft delete)",
     *     description="This endpoint marks a Industry as deleted by setting the `is_deleted` field to `1`. It does not remove the Industry from the database.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Industry to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Industry deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Industry deleted Successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Industry not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Industry not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unable to delete industry",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to delete Industry"
     *             )
     *         )
     *     )
     * )
    */
    public function deleteIndustry($id)
    {
        // Find the franchise by its ID
        $industry = Industry::find($id);

        // Check if the industry exists
        if (!$industry) {
            return response()->json(['message' => 'Industry not found'], 404);
        }

        // Delete the industry record
         $industry->update([
            'is_deleted' => 1
        ]);

        // Return a success response
        if($industry){
            return $this->successResponse($industry,'Industry deleted Successfully',200);
        }else{
            return $this->errorResponse('Unable to delete Industry',401);
        }
    }

    public function getIndustry(){
        $industry = Industry::where('is_deleted',0)->get();
        if($industry){
            return $this->successResponse($industry,'Industry deleted Successfully',200);
        }else{
            return $this->errorResponse('Unable to delete Industry',401);
        }
    }
}
