<?php

namespace App\Enums;

use App\Contracts\AppAuthEnumCodeContract;

enum AppAuthResponseCode: string implements AppAuthEnumCodeContract {
    //* Request Success
    case SuccessRequest = 'success';
    case SuccessRetrieveData = 'success_retrieve_data';
    case SuccessCreate = 'success_create';
    case SuccessUpdate = 'success_update';
    case SuccessDelete = 'success_delete';
    case SuccessSoftDelete = 'success_soft_delete';
    case SuccessRestoreDelete = 'success_restore_delete';
    case SuccessDeleteFromTrash = 'success_deleted_from_trash';
    case SuccessHardDelete = 'success_hard_deleted';

    case EmailVerificationSuccess = 'email_verification_success';
    case LoginSuccess = 'login_success';
    case LogoutSuccess = 'logout_success';
    case RefreshSuccess = 'refresh_success';

    //* Request Exceptions
    case BadRequest = 'bad_request';
    case Unauthorized = 'unauthorized';
    case Forbidden = 'forbidden';
    case NotFound = 'not_found';
    case UnprocessableContent = 'unprocessable_content';
    case UnexpectedError = 'unexpected_error';

    //* Auth Exceptions
    case RequiredToken = 'required_token'; // Unauthorized (401)
    case ExpiredToken = 'expired_token'; // Expired token (401)
    case RequiredRefreshToken = 'required_refresh_token'; // Unauthorized (401)
    case ExpiredRefreshToken = 'expired_refresh_token'; // Expired refresh token (401)
    case InvalidToken = 'invalid_token'; // Token Invalid (400)
    case AccountBlocked = 'account_blocked';
    case BannedToken = 'banned_token'; // Token has been banned (403)
    case TokenReplace = 'token_replace'; // logged in from another device (401)
    case UserNotFoundFromToken = 'user_not_found_from_token'; // Token has been banned (404)
    case UnknownToken = 'unknown_token'; // Unknown (401)
    case UserToManyRequest = 'too_many_requests'; // Rate limitter by token (429)
    case CouldNotCreateToken = 'could_not_create_token'; // Could not create token (500)

    case TokenNotProvided = 'token_not_provided';
    case InvalidCredential = 'invalid_credential';
    case AccountNotFound = 'account_not_found';
    case EmailNotVerified = 'email_not_verified';
    case LinkVerificationInvalid = 'link_verification_invalid';
    case EmailAlreadyVerified = 'email_already_verified';

    //* Banned
    case BannedTokenSuccess = 'token_banned_successfully';
    case BannedUserHasNoActiveToken = 'user_has_no_active_token';
    case BannedTokenAlready = 'token_already_banned';
    case BannedTokenHasLogout = 'token_has_logout';

    //* Unbanned
    case UnbannedTokenNotFound = 'unbanned_token_not_found';
    case UnbannedJTIMissing = 'unbanned_jti_missing';
    case UnbannedTokenSuccess = 'unbanned_token_success';

    //*
    case RouteNameMissing = 'route_name_missing';

    //* Communication Internal Service to Service
    case ServiceUnavailable = 'service_unavailable';
    case InvalidServiceResponse = 'invalid_service_response';

    public static function resolve(string $value): ?self {
        return self::tryFrom($value);
    }

    public static function fromName(string $name): ?self {
        foreach (self::cases() as $case) {
            if ($case->name === $name)
                return $case;
        }

        return null;
    }

    public function getMessage(string $type = 'error', ?string $locale = null): string {
        return match ($type) {
            'success' => trans(key: "success.{$this->value}", locale: $locale),
            default => trans(key: "exceptions.{$this->value}", locale: $locale),
        };
    }

    public function getStatusCode(): int {
        return match ($this) {
            //* Request Success
            self::SuccessRequest => 200,
            self::SuccessCreate => 201,
            self::SuccessUpdate => 200,
            self::SuccessDelete => 200,
            self::SuccessSoftDelete => 200,
            self::SuccessRestoreDelete => 200,
            self::SuccessDeleteFromTrash => 200,
            self::SuccessHardDelete => 200,

            self::EmailVerificationSuccess => 200,
            self::LoginSuccess => 200,
            self::LogoutSuccess => 200,
            self::RefreshSuccess => 200,

            //* Request Exceptions
            self::BadRequest => 400,
            self::Unauthorized => 401,
            self::Forbidden => 403,
            self::NotFound => 404,
            self::UnprocessableContent => 422,
            self::UnexpectedError => 500,

            //* Auth Exceptions
            self::RequiredToken => 401,
            self::ExpiredToken => 401,
            self::RequiredRefreshToken => 401,
            self::ExpiredRefreshToken => 401,
            self::InvalidToken => 400,
            self::AccountBlocked => 403,
            self::BannedToken => 403,
            self::TokenReplace => 401,
            self::UserNotFoundFromToken => 404,
            self::UnknownToken => 401,
            self::UserToManyRequest => 429,
            self::CouldNotCreateToken => 500,

            self::InvalidCredential => 401,
            self::AccountNotFound => 404,
            self::EmailNotVerified => 403,
            self::LinkVerificationInvalid => 403,
            self::EmailAlreadyVerified => 422,

            //* Banned
            self::BannedTokenSuccess => 200,
            self::BannedUserHasNoActiveToken => 404,
            self::BannedTokenAlready => 409,
            self::BannedTokenHasLogout => 409,

            //* Unbanned
            self::UnbannedTokenNotFound => 404,
            self::UnbannedJTIMissing => 400,
            self::UnbannedTokenSuccess => 200,

            //*
            self::RouteNameMissing => 500,

            //* Communication Internal Service to Service
            self::ServiceUnavailable => 500,
            self::InvalidServiceResponse => 500,
        };
    }
}
