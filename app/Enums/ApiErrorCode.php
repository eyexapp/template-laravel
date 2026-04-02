<?php

namespace App\Enums;

enum ApiErrorCode: string
{
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case CONFLICT = 'CONFLICT';
    case UNAUTHORIZED = 'UNAUTHORIZED';
    case FORBIDDEN = 'FORBIDDEN';
    case INTERNAL_ERROR = 'INTERNAL_ERROR';
}
