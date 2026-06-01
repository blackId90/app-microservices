<?php

namespace App\Traits;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\AppAuthException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Uri;

trait AppAuthTempSignedRoute {

    public static function isValidSignature(Request $request): void {
        $appHost = config('app.url');
        $appKey = config('app.key');

        //* Signature from request
        $receivedSignature = $request->query('signature');

        //* Expires unix timestamp from request
        $receivedExpires = $request->query('expires');

        $originalURL = Uri::of($appHost)
            ->withPath($request->path())
            ->withQuery([
                'expires' => $receivedExpires
            ]);

        //* Check valid signature
        $validSignature = hash_hmac('sha256', (string) $originalURL, $appKey);
        if (!hash_equals($validSignature, $receivedSignature))
            throw new AppAuthException(AppAuthResponseCode::LinkVerificationInvalid);

        //* Check valid expires
        if (!$receivedExpires || ($receivedExpires && Carbon::createFromTimestamp($receivedExpires)->isPast()))
            throw new AppAuthException(AppAuthResponseCode::LinkVerificationExpired);
    }
}
