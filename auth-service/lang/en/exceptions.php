<?php

return [
    //* Request Exceptions
    'method_not_allowed' => 'The method is not supported',
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
    'company_not_found' => 'Company not found',
    'role_inactive' => 'Your role is inactive. Please contact administrator.',
    'invalid_credential' => 'Invalid credential',
    'email_not_verified' => 'Email not verified',
    'email_already_exists' => 'Email already exists',
    'username_already_exists' => 'Username already exists',
    'role_not_found' => 'Role not found',
    'token_not_provided' => 'Token not provided',
    'expired_token' => 'Token has expired',
    'could_not_create_token' => 'Could not create token',
    'token_replace' => 'Session expired. You have been logged in from another device',
    'banned_token' => 'The token has been blacklisted',

    'user_profile_mismatch' => 'User profile does not match authentication',
    'company_mismatch' => 'Company does not match authentication',
    'company_inactive' => 'Your company has been inactive or suspended. Please contact administrator',
    'company_unpaid' => 'Company has unpaid invoices',
    'company_expired' => 'Company billing has expired',
    'company_suspended' => 'Company is suspended',
    'company_trial_expired' => 'Company trial period has expired',
    'company_paid_expired' => 'Company subscription has expired',
    'company_billing_invalid' => 'Company billing status is invalid',
    'company_config_auth_not_found' => 'Configurations authentication company not found',
    'company_config_auth_missing' => 'Configurations authentication company is missing',
    'invalid_signature' => 'Expired verification link or invalid digital signature',
    'link_verification_expired' => 'Link email verification expired',
    'link_verification_invalid' => 'Link email verification not valid',
    'email_already_verified' => 'Email is already verified',

    'user_has_no_active_token' => 'User has no active token',
    'token_already_banned' => 'Token is already blacklist',
    'unbanned_token_not_found' => 'Token is not currently blacklist',
    'too_many_attempts' => 'Too many attempts',
    'too_many_requests' => 'Too many requests',
    'unexpected_error' => 'An unexpected error occurred',

    //* Internal Service Exception
    'service_unavailable' => 'Service temporarily unavailable',
    'invalid_service_response' => 'Invalid response received from internal service',
];
