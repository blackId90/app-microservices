<?php

namespace App\Repositories;

use App\Enums\LoginAttemptTypeEnum;
use App\Models\LoginAttempt;
use App\Repositories\Interfaces\LoginAttemptRepositoryInterface;

class LoginAttemptRepository implements LoginAttemptRepositoryInterface {

    public function getAll() {
        return LoginAttempt::with('user')->orderByDesc('created_at')->get();
    }

    public function getByIdentifier(string $identifier) {
        return LoginAttempt::with('user')
            ->where('login_attempt_type', $identifier)
            ->orderByDesc('created_at')
            ->get();
    }

    public function log(string $identifier, bool $status = false, ?string $userId = null): void {
        $request = request();
        $reqPathname = $request->path();
        $reqIpAddress = $request->getClientIp();
        $reqUserAgent = $request->userAgent();
        $isStatus = $status && $userId;
        $typeAction = LoginAttemptTypeEnum::resolve($reqPathname);

        // DB::enableQueryLog();
        $loginAttempt = new LoginAttempt;

        $loginAttempt->login_attempt_type = $typeAction;
        $loginAttempt->login_attempt_identifier = $identifier;
        $loginAttempt->login_attempt_ip_address = $reqIpAddress;
        $loginAttempt->login_attempt_user_agent = $reqUserAgent;
        $loginAttempt->login_attempt_is_status = $isStatus;
        $loginAttempt->created_by = $isStatus ? $userId : null;

        $loginAttempt->save();
        // var_dump(DB::getQueryLog());
    }

    public function getLatest(string $userId): ?LoginAttempt {
        return LoginAttempt::where('created_by', $userId)
            ->where('login_attempt_type', LoginAttemptTypeEnum::ActionLogin)
            ->where('login_attempt_is_status', true)
            ->latest('created_at')
            ->first();
    }
}
