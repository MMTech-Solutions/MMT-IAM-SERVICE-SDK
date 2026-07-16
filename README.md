# mmt/laravel-iam-service-sdk

Cliente Laravel (**SDK**) para el **MMT IAM Service**: perfiles de usuario, red IB, settings IB y 2FA, con transporte intercambiable, respuestas tipadas (`ObjectResponses` + `WireHydration`) y contratos documentados.

Espejo de `mmt/laravel-trading-service-sdk` (sin capa `Session`; IAM es stateless).

## Requisitos

- PHP `^8.3`
- Guzzle `^7.2`
- Laravel `^11|^12|^13` (Illuminate Support)

## Instalación

```bash
composer require mmt/laravel-iam-service-sdk
php artisan vendor:publish --tag=laravel-iam-service-sdk-config
```

Publicado en Packagist como [`mmt/laravel-iam-service-sdk`](https://packagist.org/packages/mmt/laravel-iam-service-sdk). Repo: [MMT-IAM-SERVICE-SDK](https://github.com/MMTech-Solutions/MMT-IAM-SERVICE-SDK).

## Configuración

| Variable | Descripción |
|----------|-------------|
| `RBAC_IAM_BASE_URL` / `USER_IAM_BASE_URL` | URL base del IAM MS |
| `RBAC_IAM_INTERNAL_TOKEN` | Token interno (`X-Internal-Token`) |
| `RBAC_INTERNAL_CALLER_SOURCE` | Identificador del MS llamador (`X-Internal-Source`, default `ms-broker`) |
| `USER_IAM_TIMEOUT_MS` / `RBAC_IAM_TIMEOUT_MS` | Timeout HTTP en ms |

## Uso

```php
use Mmt\IamServiceSdk\IamService;
use Mmt\IamServiceSdk\Domains\Users\ObjectResponses\UserProfileItem;

$iam = app(IamService::class);

$result = $iam->users()->getProfile($userId);

if ($result->isSuccess()) {
    /** @var UserProfileItem $profile */
    $profile = $result->getMappedData(UserProfileItem::class);
}
```

Dominios disponibles:

- `$iam->referralNetwork()` — upline, downline, direct referrals
- `$iam->ibSettings()` — `getNetworkSettings()`
- `$iam->auth()` — `confirmTwoFactor($code, $extraHeaders)`
- `$iam->users()` — `getProfile($userId)` (lookups fuera-de-request)
- `$iam->health()` — health check

## Transporte intercambiable

```php
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportInterface;

$this->app->instance(TransportInterface::class, $fakeTransport);
```

`ActionResultInterface` expone `getStatus(): ?int` además de `getData(?FQCN)` y `getMappedData(?FQCN)` para soportar semántica IAM (422 `USER_NOT_IB`, 4xx vs 5xx en 2FA).

## Arquitectura

```
broker Features → User Gateway (adaptadores + mappers) → IamService → TransportInterface → IAM MS
```

`mmtech/iam-rbac` sigue manejando auth/permisos/Kafka; este SDK cubre consumo de métodos de negocio IAM.

## Licencia

MIT
