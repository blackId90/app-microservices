<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\CountryStoreOrUpdate;
use App\Http\Resources\CountryResource;
use App\Services\Applications\{ContinentService, CountryService, CurrencyService};
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class CountryController extends RestController {

    public function __construct(
        protected CountryService $countryService,
        protected ContinentService $continentService,
        protected CurrencyService $currencyService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = (int) $request->query('limit', 10);
        $perPage = min($perPage, 100); // max 100
        $typeList = (int) $request->query('type_list', TypeBrowseEnum::WITHOUT_DELETED);

        $results = $this->countryService->listCountries($search, $perPage, $typeList);

        //* Transform items to Resource
        $data = [
            'records' => CountryResource::collection($results->items()),
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
    public function store(CountryStoreOrUpdate $request): JsonResponse {
        $validated = $request->validated();

        $result = $this->countryService->createCountry($validated);

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->getMessage('success'),
            data: new CountryResource($result)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $countryId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $result = $this->countryService->getCountryById($countryId, $typeRead, ['continent', 'currency']);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new CountryResource($result)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CountryStoreOrUpdate $request, string $countryId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $result = $this->countryService->updateCountry($countryId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new CountryResource($result)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $countryId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->countryService->deleteCountry($countryId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->getMessage('success')
        );
    }

    public function optionCurrencies(Request $request): JsonResponse {
        $search = $request->query('q');

        $results = $this->currencyService->optionCurrencies($search);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $results
        );
    }

    public function optionContinents(Request $request): JsonResponse {
        $search = $request->query('q');

        $results = $this->continentService->optionContinent($search);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $results
        );
    }
}
