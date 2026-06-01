<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\AuthUserStoreOrRegisterRequest;
use App\Http\Resources\{AuthResource, AuthRoleResource};
use App\Services\Applications\{AuthRoleService, AuthUserService};
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class AuthUserController extends RestController {

    public function __construct(
        protected AuthUserService $authUserService,
        protected AuthRoleService $authRoleService
    ) {}

    /**
     * Display a listing of the resource.
     */
    // public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(AuthUserStoreOrRegisterRequest $request): JsonResponse {
        $validated = $request->validated();

        $result = $this->authUserService->createAuthUser($validated);

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->value,
            data: new AuthResource($result)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $authUserId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $result = $this->authUserService->getAuthUserById($authUserId, $typeRead, ['role']);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->value,
            data: new AuthResource($result)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AuthUserStoreOrRegisterRequest $request, string $authUserId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $result = $this->authUserService->updateAuthUser($authUserId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessUpdate->value,
            data: new AuthResource($result)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $authUserId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->authUserService->deleteAuthUser($authUserId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->value // getMessage('success')
        );
    }

    public function optionRoles(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = 10;
        $typeList = TypeBrowseEnum::WITHOUT_DELETED;
        $filterWhereIn = [];

        $results = $this->authRoleService->optionRoles($search, $perPage, $typeList);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->value,
            data: AuthRoleResource::collection($results->items())
        );
    }
}
