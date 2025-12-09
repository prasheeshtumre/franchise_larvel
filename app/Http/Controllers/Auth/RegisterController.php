<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Emails\EmailController;
use App\Models\Franchises\Franchise;
use App\Models\Register\SignupFlow;
use App\Models\Register\UserDetails;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="User Registration",
     *     description="Register a new user and send email verification link",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration details",
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered and email verification link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User registered successfully! Please check your email for the verification link."),
     *             @OA\Property(property="data", type="object", example="Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not Created")
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            // Send the email verification link
            $user->sendEmailVerificationNotification();

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully! Please check your email for the verification link.',
                'data' => $user,
            ], 201);
        } else {
            return response()->json(['error' => 'Not Created'], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/registration/store_franchisor_details",
     *     summary="Store or update franchisor details",
     *     description="Create or update franchisor details for a given user in multiple steps.",
     *     tags={"Franchisor"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Franchisor details for user",
     *         @OA\JsonContent(
     *             required={"step", "user_id"},
     *             @OA\Property(property="step", type="string", enum={"step1", "step2", "step3"}, description="The step of the process"),
     *             @OA\Property(property="user_id", type="integer", example=1, description="The user ID"),
     *             @OA\Property(property="search_franchisor", type="integer", example=0, description="The franchisor search ID, 0 for new franchisor"),
     *             @OA\Property(property="franchise_name", type="string", example="Franchise X", description="The name of the franchise if creating a new one"),
     *             @OA\Property(property="franchise_location", type="string", example="New York", description="The location of the franchise if creating a new one"),
     *             @OA\Property(property="url", type="string", example="http://franchise.com", description="URL of the franchise"),
     *             @OA\Property(property="franchisor_industry", type="string", example="Food & Beverage", description="The industry of the franchisor"),
     *             @OA\Property(property="investment_range", type="string", example="50,000 - 100,000", description="Investment range of the franchisor"),
     *             @OA\Property(property="support_type", type="string", example="Marketing", description="Type of support provided by the franchisor"),
     *             @OA\Property(property="hashtag_ids", type="array", items=@OA\Items(type="integer", example=1), description="List of hashtag IDs related to the franchisor")
     *         )
     *     ),
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Franchisor details successfully stored or updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Franchisor Details created successfully!"),
     *             @OA\Property(property="data", example="Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error, missing or incorrect data",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to create or update franchisor details",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not Created/updated")
     *         )
     *     )
     * )
     */
    public function storeFranchisorDetails(Request $request)
    {
        // Step 1: Validate based on conditions
        if ($request->step == 'step1') {
            // Validate search_franchisor or franchise_name and franchise_location
            if ($request->search_franchisor == '') {
                $validator = Validator::make($request->all(), [
                    'search_franchisor' => 'required',
                ]);
            } elseif ($request->search_franchisor == 0) {
                $validator = Validator::make($request->all(), [
                    'franchise_name' => 'required|string|max:255',
                    'franchise_location' => 'required|string|max:255',
                    'franchise_name' => 'required|unique:franchises,name|string|max:255',
                ]);
            }

            if (isset($validator) && $validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }

        // Step 2: Fetch franchisor details
        $getFranchisorDetails = SignupFlow::where('role_id',$request->role_id)->where('is_deleted', 0)->where('user_id', $request->user_id)->first();
        $message = "Franchisor Details created successfully!";
        // Step 3: If franchisor exists, update the details
        if ($getFranchisorDetails) {
            $message = "Franchisor details updated successfully!";
            $updateData = [];
            if ($request->step == 'step1') {
                if($request->search_franchisor == 0){
                    $storeFranchise = Franchise::create([
                        'category_id' => 0,
                        'name'=>trim($request->franchise_name),
                        'other_franchise' => 1
                    ]);
                    $updateData = [
                        'franchise_id' => $storeFranchise->id,
                        'name' => $request->franchise_name,
                        'location' => $request->franchise_location,
                        'url' => $request->url ?? "",
                    ];
                }else{
                    $updateData = [
                        'franchise_id' => $request->search_franchisor ? $request->search_franchisor : 0,
                        'name' => null,
                        'location' => null,
                        'url' => null,
                    ];
                }

            } elseif ($request->step == 'step2') {
                $updateData = [
                    'industry' => $request->franchisor_industry ?? 0,
                    'investment_range' => $request->franchisor_investment_range ?? 0,
                    'support_type' => $request->support_type ?? 0,
                ];
            } elseif ($request->step == 'step3') {
                $updateData = [
                    'hashtags' => $request->hashtag_ids,
                ];
            }

            $franchisorDetails = $getFranchisorDetails->update($updateData);
        } else {
            // If no franchisor exists, create a new user and franchisor details
            if ($request->step == 'step1') {
                $user = User::where('is_deleted', 0)->findOrFail($request->user_id); // Ensures the user exists
                $user->update(['role_id' => $request->role_id]);
            }
            $createData = [];
            // Determine createData based on search_franchisor value
            if ($request->search_franchisor != 0) {
                $createData = [
                    'user_id' => $request->user_id,
                    'franchise_id' => $request->search_franchisor ? $request->search_franchisor : 0,
                    'url' => $request->url,
                    'industry' => $request->industry ?? 0,
                    'investment_range' => $request->franchisor_investment_range ?? 0,
                    'support_type' => $request->support_type ?? 0,
                    'hashtags' => $request->hashtag_ids,
                    'role_id' => $request->role_id
                ];
            } else {
                $storeFranchise = Franchise::create([
                    'category_id' => 0,
                    'name'=>trim($request->franchise_name),
                    'other_franchise' => 1
                ]);
                $createData = [
                    'user_id' => $request->user_id,
                    'franchise_id' => $storeFranchise->id,
                    'name' => $request->franchise_name,
                    'location' => $request->franchise_location,
                    'url' => $request->url,
                    'industry' => $request->industry ?? 0,
                    'investment_range' => $request->franchisor_investment_range ?? 0,
                    'support_type' => $request->support_type ?? 0,
                    'hashtags' => $request->hashtag_ids,
                    'role_id' => $request->role_id
                ];
            }
            $franchisorDetails = SignupFlow::create($createData);
        }

        // Step 4: Return success or failure response
        if (isset($franchisorDetails) && $franchisorDetails) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $franchisorDetails,
            ], 201);
        } else {
            return response()->json(['error' => 'Not Created/updated'], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/registration/store_franchisee_details",
     *     summary="Store or update franchisee details",
     *     description="This endpoint stores or updates franchisee details. It handles three steps:
     *     step 1 for basic franchisee details, step 2 for additional franchisee details, and step 3 for hashtags.",
     *     tags={"Franchisee"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "step"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="step", type="string", enum={"step1", "step2", "step3"}, example="step1"),
     *             @OA\Property(property="search_franchisee", type="integer", example=1),
     *             @OA\Property(property="franchisee_name", type="string", example="Franchisee Name"),
     *             @OA\Property(property="franchisee_location", type="string", example="Franchisee Location"),
     *             @OA\Property(property="url", type="string", example="https://franchisee-url.com"),
     *             @OA\Property(property="franchisee_industry", type="integer", example=1),
     *             @OA\Property(property="franchisee_investment_range", type="integer", example=100000),
     *             @OA\Property(property="support_type", type="integer", example=1),
     *             @OA\Property(property="timeline_type", type="integer", example=1),
     *             @OA\Property(property="franchisee_expert_advice", type="integer", example=1),
     *             @OA\Property(property="hashtag_ids", type="array", @OA\Items(type="integer"), example={1,2,3})
     *         )
     *     ),
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Franchisee details successfully stored or updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Franchisee Details created successfully!"),
     *             @OA\Property(property="data", example="Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, failed to store or update franchisee details",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not Created/updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error in the request body",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function storeFranchiseeDetails(Request $request)
    {
        // Step 1: Validate based on conditions
        if ($request->step == 'step1') {
            // Validate search_franchisor or franchise_name and franchise_location
            if ($request->search_franchisee == '') {
                $validator = Validator::make($request->all(), [
                    'search_franchisee' => 'required',
                ]);
            } elseif ($request->search_franchisee == 0) {
                $validator = Validator::make($request->all(), [
                    'franchisee_name' => 'required|string|max:255',
                    'franchisee_location' => 'required|string|max:255',
                    'franchisee_name' => 'required|unique:franchises,name|string|max:255',
                ]);
            }

            if (isset($validator) && $validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }

        // Step 2: Fetch franchisee details
        $getFranchiseeDetails = SignupFlow::where('role_id',$request->role_id)->where('is_deleted', 0)->where('user_id', $request->user_id)->first();
        $message = "Franchisee Details created successfully!";
        // Step 3: If franchisor exists, update the details
        if ($getFranchiseeDetails) {
            $message = "Franchisee details updated successfully!";
            $updateData = [];
            if ($request->step == 'step1') {
                if($request->search_franchisee == 0){
                    $storeFranchise = Franchise::create([
                        'category_id' => 0,
                        'name'=>trim($request->franchisee_name),
                        'other_franchise' => 1
                    ]);
                    $updateData = [
                        'franchise_id' => $storeFranchise->id,
                        'name' => $request->franchisee_name,
                        'location' => $request->franchisee_location,
                        'url' => $request->franchisee_website_url,
                    ];
                }else{
                    $updateData = [
                        'franchise_id' => $request->search_franchisee ? $request->search_franchisee : 0,
                        'name' => null,
                        'location' => null,
                        'url' => null,
                    ];
                }

            } elseif ($request->step == 'step2') {
                $updateData = [
                    'industry' => $request->franchisee_industry ?? 0,
                    'investment_range' => $request->franchisee_investment_range ?? 0,
                    'support_type' => $request->franchisee_support_type ?? 0,
                    'start_your_franchise' => $request->timeline_type ?? 0,
                    'expert_advice' => $request->franchisee_expert_advice ?? 0,
                ];
            } elseif ($request->step == 'step3') {
                $updateData = [
                    'type_of_franchises' => $request->franchiseCategories,
                ];
            }

            $franchiseeDetails = $getFranchiseeDetails->update($updateData);
        } else {
            // If no franchisor exists, create a new user and franchisor details
            if ($request->step == 'step1') {
                $user = User::where('is_deleted', 0)->findOrFail($request->user_id); // Ensures the user exists
                $user->update(['role_id' => $request->role_id]);
            }
            $createData = [];
            // Determine createData based on search_franchisee value
            if ($request->search_franchisee != 0) {
                $createData = [
                    'user_id' => $request->user_id,
                    'franchise_id' => $request->search_franchisee ? $request->search_franchisee : 0,
                    'url' => $request->franchisee_website_url,
                    'industry' => $request->franchisee_industry ?? 0,
                    'investment_range' => $request->franchisee_investment_range ?? 0,
                    'start_your_franchise' => $request->timeline_type ?? 0,
                    'expert_advice' => $request->franchisee_expert_advice ?? 0,
                    'role_id'=>$request->role_id
                ];
            } else {
                $storeFranchise = Franchise::create([
                    'category_id' => 0,
                    'name'=>trim($request->franchisee_name),
                    'other_franchise' => 1
                ]);
                $createData = [
                    'user_id' => $request->user_id,
                    'franchise_id' => $storeFranchise->id,
                    'name' => $request->franchisee_name,
                    'location' => $request->franchisee_location,
                    'url' => $request->franchisee_website_url,
                    'industry' => $request->franchisee_industry ?? 0,
                    'investment_range' => $request->franchisee_investment_range ?? 0,
                    'start_your_franchise' => $request->timeline_type ?? 0,
                    'expert_advice' => $request->franchisee_expert_advice ?? 0,
                    'role_id'=>$request->role_id
                ];
            }
            $franchiseeDetails = SignupFlow::create($createData);
        }

        // Step 4: Return success or failure response
        if (isset($franchiseeDetails) && $franchiseeDetails) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $franchiseeDetails,
            ], 201);
        } else {
            return response()->json(['error' => 'Not Created/updated'], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/registration/store_buyer_details",
     *     summary="Store or update buyer details",
     *     description="This endpoint stores or updates buyer details. It handles three steps: step 1 for franchise categories, step 2 for additional details, and step 3 for hashtags.",
     *     tags={"Buyer"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "step"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="step", type="string", enum={"step1", "step2", "step3"}, example="step1"),
     *             @OA\Property(property="franchiseCategories", type="array", @OA\Items(type="integer"), example={1, 2}),
     *             @OA\Property(property="franchisee_industry", type="integer", example=1),
     *             @OA\Property(property="franchisee_investment_range", type="integer", example=50000),
     *             @OA\Property(property="support_type", type="integer", example=2),
     *             @OA\Property(property="timeline_type", type="integer", example=1),
     *             @OA\Property(property="franchisee_expert_advice", type="integer", example=1),
     *             @OA\Property(property="hashtag_ids", type="array", @OA\Items(type="integer"), example={1, 3}),
     *             @OA\Property(property="search_franchisee", type="integer", example=1),
     *             @OA\Property(property="franchise_name", type="string", example="Franchisee Name"),
     *             @OA\Property(property="franchise_location", type="string", example="Franchisee Location"),
     *             @OA\Property(property="url", type="string", example="https://buyer-url.com")
     *         )
     *     ),
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Buyer details successfully stored or updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Buyer Details created successfully!"),
     *             @OA\Property(property="data", example="Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, failed to store or update buyer details",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not Created/updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error in the request body",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function storeBuyerDetails(Request $request)
    {
        // Step 1: Fetch franchisee details
        $getFranchiseeDetails = SignupFlow::where('role_id',$request->role_id)->where('is_deleted', 0)->where('user_id', $request->user_id)->first();
        $message = "Buyer Details created successfully!";
        // Step 3: If franchisor exists, update the details
        if ($getFranchiseeDetails) {
            $message = "Buyer details updated successfully!";
            $updateData = [];
            if ($request->step == 'step1') {
                $updateData = [
                    'type_of_franchises' => $request->franchiseCategories,
                ];
            } elseif ($request->step == 'step2') {
                $updateData = [
                    'industry' => $request->buyer_industry ?? 0,
                    'investment_range' => $request->buyer_investment_range ?? 0,
                    'support_type' => $request->support_type ?? 0,
                    'start_your_franchise' => $request->timeline_type ?? 0,
                    'expert_advice' => $request->buyer_expert_advice ?? 0,
                ];
            } elseif ($request->step == 'step3') {
                $updateData = [
                    'hashtags' => $request->hashtag_ids,
                ];
            }

            $franchiseeDetails = $getFranchiseeDetails->update($updateData);
        } else {
            // If no franchisor exists, create a new user and franchisor details
            if ($request->step == 'step1') {
                $user = User::where('is_deleted', 0)->findOrFail($request->user_id); // Ensures the user exists
                $user->update(['role_id' => $request->role_id]);
            }
            $createData = [];
            // Determine createData based on search_franchisor value
            // if ($request->search_franchisee != 0) {
            //     $createData = [
            //         'user_id' => $request->user_id,
            //         'industry' => $request->buyer_industry ?? 0,
            //         'investment_range' => $request->buyer_investment_range ?? 0,
            //         'start_your_franchise' => $request->timeline_type ?? 0,
            //         'expert_advice' => $request->franchisee_expert_advice ?? 0,
            //     ];
            // } else {
                $createData = [
                    'user_id' => $request->user_id,
                    'role_id' => $request->role_id,
                    'type_of_franchises' => $request->franchiseCategories,
                    'industry' => $request->buyer_industry ?? 0,
                    'investment_range' => $request->buyer_investment_range ?? 0,
                    'start_your_franchise' => $request->timeline_type ?? 0,
                    'expert_advice' => $request->buyer_expert_advice ?? 0,
                ];
            // }
            $franchiseeDetails = SignupFlow::create($createData);
        }

        // Step 4: Return success or failure response
        if (isset($franchiseeDetails) && $franchiseeDetails) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $franchiseeDetails,
            ], 201);
        } else {
            return response()->json(['error' => 'Not Created/updated'], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/registration/store_consultant_details",
     *     summary="Store or update consultant details",
     *     description="This endpoint stores or updates consultant details. It handles step 2 for services, experience, and franchise, and step 3 for hashtags.",
     *     tags={"Consultant"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "step"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="step", type="string", enum={"step2", "step3"}, example="step2"),
     *             @OA\Property(property="services", type="string", example="Consulting services for franchisees"),
     *             @OA\Property(property="experience", type="string", example="5 years of experience in franchising"),
     *             @OA\Property(property="franchise_id", type="integer", example=3),
     *             @OA\Property(property="hashtag_ids", type="array", @OA\Items(type="integer"), example={1, 2}),
     *             @OA\Property(property="role_id", type="integer", example=2)
     *         )
     *     ),
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Consultant details successfully stored or updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consultant details created successfully!"),
     *             @OA\Property(property="data", example="Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, failed to store or update consultant details",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not Created/updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error in the request body",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function storeConsultantDetails(Request $request)
    {

        // Step 1: Validate based on conditions
        if ($request->step == 'step2') {
            // Validate search_franchisor or franchise_name and franchise_location

            $validator = Validator::make($request->all(), [
                'services' => 'required',
                'experience' => 'required',
                'franchise_id' => 'required',
            ]);


            if (isset($validator) && $validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }
        // Step 1: Fetch franchisee details
        $message = "Consultant Details created successfully!";
        $getConsultantDetails = SignupFlow::where('role_id',$request->role_id)->where('is_deleted', 0)->where('user_id', $request->user_id)->first();
        if ($getConsultantDetails) {
            $message = "Consultant details updated successfully!";
            $updateData = [];
            if ($request->step == 'step2') {
                $updateData = [
                    'services' => $request->services,
                    'experience' => $request->experience,
                    'franchise_id' => $request->franchise_id,
                ];
            } elseif ($request->step == 'step3') {
                $updateData = [
                    'hashtags' => $request->hashtag_ids,
                ];
            }
            $consultantDetails = $getConsultantDetails->update($updateData);
        } else {
            $user = User::where('is_deleted', 0)->findOrFail($request->user_id); // Ensures the user exists
            $user->update(['role_id' => $request->role_id]);
            $createData = [];
            $createData = [
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                // 'name' => $request->franchise_name,
                // 'location' => $request->franchise_location,
                // 'url' => $request->url,
                // 'industry' => $request->franchisee_industry ?? 0,
                // 'investment_range' => $request->franchisee_investment_range ?? 0,
                // 'start_your_franchise' => $request->timeline_type ?? 0,
                // 'expert_advice' => $request->franchisee_expert_advice ?? 0,
            ];
            $consultantDetails = SignupFlow::create($createData);
        }
        if (isset($consultantDetails) && $consultantDetails) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $consultantDetails,
            ], 201);
        } else {
            return response()->json(['error' => 'Not Created/updated'], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/registration/get_signup_flow_details/{user_id}",
     *     summary="Get Signup Flow Details",
     *     description="Fetches the details from the signup flow for a specific user by user_id. Requires authentication.",
     *     tags={"Signup Flow"},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="The ID of the user to fetch the signup flow details for.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *      @OA\Header(
     *         header="Authorization",
     *         description="Bearer token for user authentication",
     *         required=true,
     *         @OA\Schema(type="string", example="Bearer {your_token_here}")
     *     ),
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Signup Flow Details fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="get Signup Flow Details fetched successfully!"),
     *             @OA\Property(property="data", example="Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to fetch Signup Flow Details",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not fetched")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function getSignupFlowDetails($user_id)
    {
        $getSignupFlowDetails = User::join('signup_flow_details as sfd', 'users.id', '=', 'sfd.user_id')
            ->where('users.is_deleted', 0)
            ->where('users.role_id', '!=',0)
            ->where('sfd.is_deleted', 0)
            ->where('users.id', $user_id)
            ->select('users.*', 'sfd.franchise_id','sfd.name','sfd.location','sfd.url','sfd.industry','sfd.investment_range','sfd.start_your_franchise')
            ->first();

        if ($getSignupFlowDetails) {
            return response()->json([
                'status' => 'success',
                'message' => 'get Signup Flow Details fetched successfully!',
                'data' => $getSignupFlowDetails,
            ], 201);
        } else {
            return response()->json(['error' => 'Not fetched'], 400);
        }
    }
}
