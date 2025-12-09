<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class SwaggerController extends Controller
{
    public function index()
    {
        // Generate the Swagger documentation (swagger.json)
        $swagger = \OpenApi\Generator::scan([app_path('Http/Controllers')]);

        // Save the generated swagger.json file to the public directory
        $swaggerJson = $swagger->toJson();
        File::put(public_path('swagger.json'), $swaggerJson);

        return Response::json($swaggerJson);
    }
}

