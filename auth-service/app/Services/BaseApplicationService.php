<?php

namespace App\Services;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\AppAuthException;
use App\Traits\LogAudit;

abstract class BaseApplicationService {
    use LogAudit;

    protected function validationQueryParamsTypeList(int $typeList = TypeBrowseEnum::WITHOUT_DELETED): void {
        if (!in_array($typeList, [TypeBrowseEnum::WITHOUT_DELETED, TypeBrowseEnum::DELETED_ONLY, TypeBrowseEnum::ALL_DATA]))
            throw new AppAuthException(
                codeName: AppAuthResponseCode::BadRequest,
                context: LogAudit::setContexLog(
                    isLog: true,
                    level: 'warning',
                    message: 'Invalid type_browse parameter'
                )
            );
    }

    protected function validationQueryParamsTypeRead(int $typeRead = TypeReadEnum::WITHOUT_DELETED): void {
        if (!in_array($typeRead, [TypeReadEnum::WITHOUT_DELETED, TypeReadEnum::WITH_DELETED]))
            throw new AppAuthException(
                codeName: AppAuthResponseCode::BadRequest,
                context: LogAudit::setContexLog(
                    isLog: true,
                    level: 'warning',
                    message: 'Invalid type_read parameter'
                )
            );
    }

    protected function validationQueryParamsTypeUpdate(int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): void {
        if (!in_array($typeUpdate, [TypeUpdateEnum::WITHOUT_DELETED, TypeUpdateEnum::WITH_DELETED]))
            throw new AppAuthException(
                codeName: AppAuthResponseCode::BadRequest,
                context: LogAudit::setContexLog(
                    isLog: true,
                    level: 'warning',
                    message: 'Invalid type_update parameter'
                )
            );
    }

    protected function validationQueryParamsTypeDelete(int $typeDelete = TypeDeleteEnum::SOFT_DELETE): void {
        if (!in_array($typeDelete, [TypeDeleteEnum::SOFT_DELETE, TypeDeleteEnum::RESTORE, TypeDeleteEnum::PERMANENT_DELETE, TypeDeleteEnum::HARD_DELETE]))
            throw new AppAuthException(
                codeName: AppAuthResponseCode::BadRequest,
                context: LogAudit::setContexLog(
                    isLog: true,
                    level: 'warning',
                    message: 'Invalid type_delete parameter'
                )
            );
    }

    protected function DetermineTypeReadBaseOnTypeDelete(int $typeDelete = TypeDeleteEnum::SOFT_DELETE) {
        $typeRead = match ($typeDelete) {
            TypeDeleteEnum::SOFT_DELETE, TypeDeleteEnum::HARD_DELETE => TypeReadEnum::WITHOUT_DELETED,
            TypeDeleteEnum::RESTORE, TypeDeleteEnum::PERMANENT_DELETE => TypeReadEnum::WITH_DELETED,
            default => TypeReadEnum::WITHOUT_DELETED,
        };

        return $typeRead;
    }

    protected function MappingMessageTypeDelete(int $typeDelete = TypeDeleteEnum::SOFT_DELETE): AppAuthResponseCode {
        $message = match ($typeDelete) {
            TypeDeleteEnum::RESTORE => AppAuthResponseCode::SuccessRestoreDelete,
            TypeDeleteEnum::PERMANENT_DELETE => AppAuthResponseCode::SuccessDeleteFromTrash,
            TypeDeleteEnum::HARD_DELETE => AppAuthResponseCode::SuccessHardDelete,
            default => AppAuthResponseCode::SuccessSoftDelete,
        };

        return $message;
    }
}
