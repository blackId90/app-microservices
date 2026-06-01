<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseFormatter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Routing\Controller as LaravelController;

// class RestController extends Controller {
abstract class RestController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponseFormatter;
}
