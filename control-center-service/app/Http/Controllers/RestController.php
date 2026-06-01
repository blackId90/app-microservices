<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseFormatter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class RestController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponseFormatter;
}
