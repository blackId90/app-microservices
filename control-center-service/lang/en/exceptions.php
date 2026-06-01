<?php

return [
    //* Request Exceptions
    'bad_request' => 'Bad request',
    'unauthorized' => 'Unauthorized',
    'forbidden' => 'Access to the resource is prohibited',
    'not_found' => 'The requested resource could not be found',
    'unprocessable_content' => 'Unprocessable content',
    'unexpected_error' => 'Unexpected error',

    //* Auth Exceptions
    'invalid_token' => 'Token Signature could not be verified',
    'account_blocked' => 'Your account has been blocked by the administrator',
    'account_not_found' => 'Account not found',
    'invalid_credential' => 'Invalid credential',
    'email_not_verified' => 'Email not verified',
    'token_not_provided' => 'Token not provided',
    'expired_token' => 'Token has expired',
    'could_not_create_token' => 'Could not create token',
    'token_replace' => 'Session expired. You have been logged in from another device',
    'banned_token' => 'The token has been blacklisted',

    //* Register & Verify
    'link_verification_invalid' => 'Link email verification not valid',
    'email_already_verified' => 'Email is already verified',

    'user_has_no_active_token' => 'User has no active token',
    'token_already_banned' => 'Token is already blacklist',
    'unbanned_token_not_found' => 'Token is not currently blacklist',
    'too_many_requests' => 'Too many requests',
    'unexpected_error' => 'An unexpected error occurred',

    //* Internal Service Exception
    'service_unavailable' => 'Service temporarily unavailable',
    'invalid_service_response' => 'Invalid response received from internal service',
];
