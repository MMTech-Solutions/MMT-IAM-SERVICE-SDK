<?php

return [
    'base_url' => rtrim((string) env('USER_IAM_BASE_URL', env('RBAC_IAM_BASE_URL', '')), '/'),
    'api_key' => (string) env('RBAC_IAM_INTERNAL_TOKEN', ''),
    'source_service' => (string) env('RBAC_INTERNAL_CALLER_SOURCE', env('IAM_SOURCE_SERVICE', 'ms-broker')),
    'timeout' => max(1, (int) ceil((int) env('USER_IAM_TIMEOUT_MS', env('RBAC_IAM_TIMEOUT_MS', 1500)) / 1000)),
    'api_prefix' => '/api/iam/v1',
];
