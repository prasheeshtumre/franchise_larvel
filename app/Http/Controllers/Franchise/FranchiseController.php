<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Franchises\Franchise;
use App\Models\Franchises\FranchiseDetail;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FranchiseController extends Controller
{
    use ApiResponser;

    public function getFranchises(){
        $getFranchises = Franchise::with('generalInformation')->get();
        if($getFranchises){
            return $this->successResponse($getFranchises,'Franchise details fetched Successfully',200);
        }else{
            return $this->errorResponse('Unable to fetch franchise',401);
        }
    }
    public function storeFranchise(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|numeric',
            'name' => 'required|unique:franchises,name|string|max:255',
            'description' => 'nullable|string',
            'file' => 'file|mimes:jpg,png|max:2048', // adjust validation as needed
            'mincash' => 'nullable|numeric',
        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Store the file
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $path = $request->file('file')->store('franchise'); // stores in storage/app/uploads
            // Optionally, save $path to the database if you need to
            // return back()->with('success', 'File uploaded successfully!')->with('path', $path);
        }
        $storeFranchise = Franchise::create([
            'category_id'=>$request->category_id,
            'name'=>trim($request->name),
            'description'=>trim($request->description),
            'imagesrc'=> $path ?? null,
            'mincash'=>$request->mincash,
            'year_founded'=>$request->year_founded ?? 0,
            'units'=>$request->units ?? 0,
            'min_investment'=>$request->min_investment ?? 0,
            'max_investment'=>$request->max_investment ?? 0,
            'franchise_fee'=>$request->franchise_fee ?? 0,
        ]);

        if($storeFranchise){
            $storeFranchiseDetails = FranchiseDetail::create([
                'franchise_id' => $storeFranchise->id,
                'address' => $request->address ?? null,
                'website' => $request->website ?? null,
                'phone_number' => $request->phone_number ?? null,
                'email' => $request->email ?? null
            ]);
        }


        if($storeFranchise){
            return $this->successResponse($storeFranchise,'Created Franchise Successfully',200);
        }else{
            return $this->errorResponse('Unable to create franchise',401);
        }
    }

    public function updateFranchise(Request $request,$id){
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('franchises')->ignore($id) // Ensure uniqueness, ignoring the current franchise
            ],
            'description' => 'nullable|string',
            'imagesrc' => 'nullable|string',
            'mincash' => 'nullable|numeric',
        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the franchise by its ID
        $franchise = Franchise::find($id);

        // Check if the franchise exists
        if (!$franchise) {
            return $this->errorResponse('Franchise not found',404);
        }

         // Update the franchise record with new data
        $franchise->update([
            'category_id' => $request->category_id,
            'name' => trim($request->name),
            'description' => trim($request->description),
            'imagesrc' => $request->imagesrc,
            'mincash' => $request->mincash ?? 0,
            'year_founded'=>$request->year_founded ?? 0,
            'units'=>$request->units ?? 0,
            'min_investment'=>$request->min_investment ?? 0,
            'max_investment'=>$request->max_investment ?? 0,
            'franchise_fee'=>$request->franchise_fee ?? 0,
        ]);
        if($franchise){
            return $this->successResponse($franchise,'Created Category Successfully',200);
        }else{
            return $this->errorResponse('Unable to create Category',401);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/franchises/{id}",
     *     operationId="deleteFranchise",
     *     tags={"Franchises"},
     *     summary="Delete a Franchise (soft delete)",
     *     description="This endpoint marks a franchise as deleted by setting the `is_deleted` field to `1`. It does not remove the franchise from the database.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the franchise to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Franchises deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Franchises deleted Successfully"
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
     *         description="Franchises not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Franchises not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unable to delete Franchises",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to delete Franchises"
     *             )
     *         )
     *     )
     * )
    */
    public function deleteFranchise($id)
    {
        // Find the franchise by its ID
        $franchise = Franchise::find($id);

        // Check if the franchise exists
        if (!$franchise) {
            return response()->json(['message' => 'Franchise not found'], 404);
        }

        // Delete the franchise record
         $franchise->update([
            'is_deleted' => 1
        ]);

        // Return a success response
        if($franchise){
            return $this->successResponse($franchise,'Franchise deleted Successfully',200);
        }else{
            return $this->errorResponse('Unable to delete franchise',401);
        }
    }

}
