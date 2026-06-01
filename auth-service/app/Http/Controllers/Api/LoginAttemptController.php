<?php

namespace App\Http\Controllers\Api;

use App\Enums\LoginAttemptTypeEnum;
use App\Http\Controllers\RestController;
use App\Rules\EnumValue;
use App\Services\Applications\LoginAttemptService;
use Illuminate\Http\Request;

class LoginAttemptController extends RestController {
    protected LoginAttemptService $loginAttemptService;

    public function construct(LoginAttemptService $loginAttemptService) {
        $this->loginAttemptService = $loginAttemptService;
    }

    public function index() {
        return response()->json($this->loginAttemptService->listAll());
    }

    public function byIdentifier(Request $request) {
        $request->validate([
            'login_attempt_type' => ['required', 'string', new EnumValue(LoginAttemptTypeEnum::class)]
        ]);

        return response()->json($this->loginAttemptService->listByIdentifier($request->identifier));
    }
}
