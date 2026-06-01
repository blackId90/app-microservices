<?php

namespace App\Http\Middleware;

use App\Enums\AppAuthResponseCode;
use App\Enums\PermissionTypeActionEnum;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\AppControlCenterException;
use App\Services\Clients\MenuSigninClient;
use App\Traits\LogAudit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class HasAccessMiddleware {
    use LogAudit;

    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected MenuSigninClient $menuSigninClient,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        //* Get full route name
        // $routeName = $request->route()->getName();
        $fullRouteName = Route::currentRouteName();

        //* Split argument route name, parameter default, is check
        [$argRouteName, $argParameter, $argType] = array_pad(explode(':', $fullRouteName, 3), 3, null);

        //* Get configs exclude
        $configHeaderSource = config('hasaccess.exclude.header', []);
        $configRouteName = config('hasaccess.exclude.route_name', []);

        //* Check exclude header X-Request-Source
        $headerSource = $request->header('X-Request-Source');
        if ($configHeaderSource && in_array($headerSource, $configHeaderSource, true)) {
            $this->setRequestAttributes($request, $argRouteName, $argParameter, $argType);

            return $next($request);
        }

        //* Check exclude route name
        if (in_array($argRouteName, $configRouteName, true)) {
            $this->setRequestAttributes($request, $argRouteName, $argParameter, $argType);

            return $next($request);
        }

        //* Validation request (user and role id)
        $this->handleValidateRequest($request, $argRouteName);

        try {
            $requestParameter = $this->handleRequestQueryParams($request, $argRouteName);

            //* Check and handle type route name options.
            $argRouteName = $this->handleRequestTypeOptions($argRouteName);

            //* Get auth has access permission data (remote auth service)
            $resultAccessPermission = $this->checkPermissionAuthService($argRouteName, $requestParameter);

            //* Set request attributes from response $resultAccessPermission
            $this->setRequestAttributes($request, $resultAccessPermission['request_route_name'], $resultAccessPermission['request_parameter'], $argType);

            return $next($request);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function handleValidateRequest(Request $request, ?string $routeName = null): void {
        if (!$routeName)
            throw new AppControlCenterException(codeName: AppAuthResponseCode::UnexpectedError, context: LogAudit::setContexLog(aliasCodeName: AppAuthResponseCode::RouteNameMissing));

        //* Get user from request
        $user = $request->user();
        if (!$user)
            throw new AccessDeniedException(codeName: AppAuthResponseCode::Unauthorized, context: LogAudit::setContexLog(isLog: true, level: 'warning', message: 'Unauthorized: Request user not found'));

        //* Get auth_user_id from request attributes
        $authUserId = $request->attributes->get('userId');
        $roleId = $request->attributes->get('roleId');
        if (!$authUserId || !$roleId)
            throw new AccessDeniedException(AppAuthResponseCode::Unauthorized, context: LogAudit::setContexLog(isLog: true, level: 'warning', message: 'Request attributes auth user / auth role not found'));
    }

    private function handleRequestQueryParams(Request $request, string $routeName): ?string {
        $queryParams = $request->query();
        if (empty($queryParams))
            return null;

        $permissionTypeAction = PermissionTypeActionEnum::resolveFromRouteName($routeName);
        $typeQueryParams = match ($permissionTypeAction) {
            PermissionTypeActionEnum::MODULE, PermissionTypeActionEnum::CREATE, PermissionTypeActionEnum::UPDATE, PermissionTypeActionEnum::IMPORT, PermissionTypeActionEnum::EXPORT => '',
            PermissionTypeActionEnum::BROWSE => 'type_list',
            PermissionTypeActionEnum::READ => 'type_detail',
            PermissionTypeActionEnum::DELETE => 'type_deleted',
            default => null,
        };

        if (empty($typeQueryParams) || !$typeQueryParams)
            return null;

        $configAction = config('hasaccess.action', []);
        $configActionType = $configAction[$permissionTypeAction?->value];

        //* Check key query params is valid
        if (!array_key_exists($configActionType['type'], $queryParams))
            throw new AccessDeniedException(context: LogAudit::setContexLog(isLog: true, level: 'warning', message: 'Key query params is not valid'));

        //* Check value query params is valid
        $valueParameter = $request->query($typeQueryParams);
        if (!in_array($valueParameter, $configActionType['value'], true))
            throw new AccessDeniedException(context: LogAudit::setContexLog(isLog: true, level: 'warning', message: 'Value query params is not valid'));

        return $valueParameter;
    }

    private function handleRequestTypeOptions(string $argRouteName): string {
        //* Cek apakah route diawali dengan 'options.'
        if (str_starts_with($argRouteName, 'options.')) {
            //* Pecah string berdasarkan titik (.)
            //* Contoh: "read_options.status.auth_roles" menjadi ['options', 'status', 'auth_roles']
            $segments = explode('.', $argRouteName);

            //* Ambil elemen terakhir sebagai nama modul induk
            $moduleName = end($segments); // Hasil: "auth_roles"

            $typeModule = PermissionTypeActionEnum::MODULE->value;
            $parentModulePermission = "{$typeModule}.{$moduleName}";

            return $parentModulePermission;
        }

        return $argRouteName;
    }

    private function checkPermissionAuthService(string $routeName, ?string $requestParameter = null): array {
        //* Get auth has access permission data (remote auth service)
        $dataBody = [
            'request_route_name' => $routeName,
            'request_parameter' => $requestParameter,
        ];
        $resultHasAccessPermission = $this->menuSigninClient->hasAccessPermissionDataAuth($dataBody);

        //* Check service availability
        $serviceAvailable = !isset($resultHasAccessPermission['fallback']);
        $serviceAvailableStatus = isset($resultHasAccessPermission['status']);
        $isServiceSuccess = $serviceAvailable && $serviceAvailableStatus && $resultHasAccessPermission['status'] === 200;
        if (!$isServiceSuccess)
            throw new AccessDeniedException();

        if (isset($resultHasAccessPermission['data']['has_permission']) && !$resultHasAccessPermission['data']['has_permission'])
            throw new AccessDeniedException();

        if (isset($resultHasAccessPermission['data']['permission_parameter']) && !$resultHasAccessPermission['data']['permission_parameter'] && ($resultHasAccessPermission['data']['permission_parameter'] <= $requestParameter))
            throw new AccessDeniedException();

        return $resultHasAccessPermission['data'];
    }

    private function setRequestAttributes(Request $request, string $routeName, ?string $parameter = null, ?string $type = null) {
        $request->attributes->set('permission_slug', $routeName);
        $request->attributes->set('permission_type', !empty($type) ? $type : null);
        $request->attributes->set('permission_parameter', !empty($parameter) ? $parameter : null);
    }
}
