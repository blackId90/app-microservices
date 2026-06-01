<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\UserStoreOrUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\Applications\UserService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\DB;

class UserController extends RestController {

    public function __construct(
        // protected AuthUserService $authUserService
        protected UserService $userService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = (int) $request->query('limit', 10);
        $perPage = min($perPage, 100); // max 100
        $typeList = (int) $request->query('type_list', TypeBrowseEnum::WITHOUT_DELETED);

        $results = $this->userService->listUsers($search, $perPage, $typeList);

        //* Transform items to Resource
        $data = [
            'records' => UserResource::collection($results->items()),
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage()
            ]
        ];

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $data
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreOrUpdateRequest $request): JsonResponse {
        $validated = $request->validated();

        $result = $this->userService->createUser($validated);

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->getMessage('success'),
            data: new UserResource($result)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $userId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $result = $this->userService->getUserById($userId, $typeRead/* , ['authUser'] */);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new UserResource($result)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserStoreOrUpdateRequest $request, string $userId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $result = $this->userService->updateUser($userId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new UserResource($result)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $userId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->userService->deleteUser($userId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->getMessage('success')
        );
    }

    public function optionRoles(Request $request): JsonResponse {
        $search = $request->query('q');

        $results = $this->userService->optionRoles($search);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $results
        );
    }
}
