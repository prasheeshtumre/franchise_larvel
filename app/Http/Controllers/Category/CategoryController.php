<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category\Category;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use ApiResponser;

    public function storeCategory(Request $request){
        $validator = Validator::make($request->all(), [
            'fran_cat_name' => 'required|unique:categories,name',
            'fran_cat_icon' => 'required',
        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $storeCategory = Category::create([
            'industry_id'=>$request->fran_cat_industry_id,
            'name'=>trim($request->fran_cat_name),
            'description'=>trim($request->description),
            'status'=>$request->status ?? 0,
            'icon'=>trim($request->fran_cat_icon),
        ]);
        if($storeCategory){
            return $this->successResponse($storeCategory,'Created Category Successfully',200);
        }else{
            return $this->errorResponse('Unable to create Category',401);
        }
    }

    public function updateCategory(Request $request,$id){
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($id) // Ensure uniqueness, ignoring the current franchise
            ],
            'industry_id' => 'required',

        ]);

        if (isset($validator) && $validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the franchise by its ID
        $category = Category::find($id);

        // Check if the franchise exists
        if (!$category) {
            return $this->errorResponse('Category not found',404);
        }

         // Update the category record with new data
        $category->update([
            'industry_id'=>$request->industry_id,
            'name'=>trim($request->name),
            'description'=>trim($request->description),
            'status'=>$request->status ?? 0,
            'icon'=>trim($request->icon),
        ]);
        if($category){
            return $this->successResponse($category,'Created Category Updated',200);
        }else{
            return $this->errorResponse('Unable to create Category',401);
        }
    }

    public function getCategory(){
        $getCategory = Category::where('is_deleted',0)->get();
        if($getCategory){
            return $this->successResponse($getCategory,'Category Fetched Successfully',200);
        }else{
            return $this->errorResponse('Unable to fetch Category',401);
        }

    }

    /**
     * @OA\Delete(
     *     path="/api/category/delete/{id}",
     *     operationId="deleteCategory",
     *     tags={"Category"},
     *     summary="Delete a Category (soft delete)",
     *     description="This endpoint marks a Category as deleted by setting the `is_deleted` field to `1`. It does not remove the Category from the database.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Category to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category deleted Successfully"
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
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unable to delete category",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to delete Category"
     *             )
     *         )
     *     )
     * )
    */
    public function deleteCategory($id)
    {
        // Find the franchise by its ID
        $category = Category::find($id);

        // Check if the category exists
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Delete the category record
         $category->update([
            'is_deleted' => 1
        ]);

        // Return a success response
        if($category){
            return $this->successResponse($category,'Category deleted Successfully',200);
        }else{
            return $this->errorResponse('Unable to delete Category',401);
        }
    }
}
