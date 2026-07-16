<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\WireHydration;

use BackedEnum;
use InvalidArgumentException;
use Mmt\IamServiceSdk\WireHydration\Attributes\WireMapped;
use ReflectionClass;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use Throwable;
use UnitEnum;

class WireHydrator
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $castToFqcn
     * @return ($data is list ? list<T> : T)
     */
    public function hydrate(mixed $data, string $castToFqcn): mixed
    {
        if (is_array($data) && array_is_list($data)) {
            $out = [];
            foreach ($data as $row) {
                if (! is_array($row)) {
                    throw new InvalidArgumentException('List items for wire hydration must be arrays.');
                }
                $out[] = $this->hydrateObject($row, $castToFqcn);
            }

            return $out;
        }

        if (! is_array($data)) {
            throw new InvalidArgumentException('Wire hydration expects an associative array or a list of associative arrays.');
        }

        return $this->hydrateObject($data, $castToFqcn);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $className
     * @return T
     */
    public function hydrateObject(array $payload, string $className): object
    {
        $reflectionClass = new ReflectionClass($className);

        $classMapped = $reflectionClass->getAttributes(WireMapped::class) !== [];

        $object = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propWireMappedAttr = $property->getAttributes(WireMapped::class)[0] ?? null;

            if (! $classMapped && $propWireMappedAttr === null) {
                continue;
            }

            $wireKey = $this->resolveWireKey($property, $propWireMappedAttr);

            if (! array_key_exists($wireKey, $payload)) {
                if (! $classMapped) {
                    continue;
                }

                $this->applyMissingKeyForClassMapped($object, $property);

                continue;
            }

            $raw = $payload[$wireKey];
            $value = $this->hydrateValueForProperty($raw, $property);

            try {
                $property->setValue($object, $value);
            } catch (Throwable $e) {
                throw new InvalidArgumentException(
                    sprintf('Cannot hydrate property %s::$%s: %s', $className, $property->getName(), $e->getMessage()),
                    0,
                    $e
                );
            }
        }

        return $object;
    }

    private function resolveWireKey(ReflectionProperty $property, ?object $propWireMappedAttr): string
    {
        if ($propWireMappedAttr !== null) {
            /** @var WireMapped $mapped */
            $mapped = $propWireMappedAttr->newInstance();

            return $mapped->wireKey ?? $property->getName();
        }

        return $property->getName();
    }

    private function applyMissingKeyForClassMapped(object $object, ReflectionProperty $property): void
    {
        $type = $property->getType();

        if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
            try {
                $property->setValue($object, null);
            } catch (Throwable $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Missing wire key for nullable property %s::$%s and could not set null: %s',
                        $property->getDeclaringClass()->getName(),
                        $property->getName(),
                        $e->getMessage()
                    ),
                    0,
                    $e
                );
            }

            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Missing wire key for required property %s::$%s.',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            )
        );
    }

    private function hydrateValueForProperty(mixed $raw, ReflectionProperty $property): mixed
    {
        $type = $property->getType();

        if ($type === null) {
            return $raw;
        }

        if ($raw === null && $type->allowsNull()) {
            return null;
        }

        return $this->hydrateValueForType($raw, $type, $property);
    }

    private function hydrateValueForType(mixed $raw, ReflectionType $type, ReflectionProperty $property): mixed
    {
        if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
            return $this->hydrateArrayProperty($raw, $property);
        }

        return $this->normalizeScalarOrObject($raw, $type);
    }

    private function hydrateArrayProperty(mixed $raw, ReflectionProperty $property): array
    {
        if ($raw === null) {
            return [];
        }

        if (! is_array($raw)) {
            throw new InvalidArgumentException(sprintf('Expected array for %s::$%s.', $property->getDeclaringClass()->getName(), $property->getName()));
        }

        $elementClass = $this->arrayElementClassFromDocblock($property);

        if ($elementClass === null) {
            return $raw;
        }

        if (array_is_list($raw)) {
            $items = [];
            foreach ($raw as $item) {
                if (! is_array($item)) {
                    throw new InvalidArgumentException(sprintf('List elements for %s::$%s must be arrays.', $property->getDeclaringClass()->getName(), $property->getName()));
                }
                $items[] = $this->hydrateObject($item, $elementClass);
            }

            return $items;
        }

        return $raw;
    }

    /**
     * @return class-string|null
     */
    private function arrayElementClassFromDocblock(ReflectionProperty $property): ?string
    {
        $doc = $property->getDocComment();
        if ($doc === false || $doc === '') {
            return null;
        }

        if (! preg_match('/@var\s+([\w\\\\]+)\[\]/', $doc, $matches)) {
            return null;
        }

        $name = $matches[1];

        if (str_starts_with($name, '\\')) {
            /** @var class-string $fqcn */
            $fqcn = $name;

            return $fqcn;
        }

        $namespace = $property->getDeclaringClass()->getNamespaceName();

        /** @var class-string $fqcn */
        $fqcn = $namespace.'\\'.$name;

        return $fqcn;
    }

    private function normalizeScalarOrObject(mixed $raw, ReflectionType $type): mixed
    {
        if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
            return $this->coerceBuiltin($raw, $type->getName());
        }

        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $this->hydrateTypedObject($raw, $type->getName());
        }

        throw new InvalidArgumentException('Unsupported property type for wire hydration.');
    }

    private function coerceBuiltin(mixed $raw, string $builtin): mixed
    {
        return match ($builtin) {
            'string' => (string) $raw,
            'int' => is_int($raw) ? $raw : (int) $raw,
            'float' => is_float($raw) || is_int($raw) ? (float) $raw : (float) $raw,
            'bool' => (bool) $raw,
            'array' => is_array($raw) ? $raw : throw new InvalidArgumentException('Expected array.'),
            default => $raw,
        };
    }

    /**
     * @param  class-string  $className
     */
    private function hydrateTypedObject(mixed $raw, string $className): mixed
    {
        if ($raw === null) {
            return null;
        }

        if (enum_exists($className)) {
            $reflectionEnum = new ReflectionEnum($className);
            if ($reflectionEnum->isBacked()) {
                /** @var class-string<BackedEnum> $className */
                $backing = $reflectionEnum->getBackingType();
                $value = $raw;
                if ($backing?->getName() === 'int' && (is_string($value) && is_numeric($value)) && ! is_int($value)) {
                    $value = (int) $value;
                }
                if ($backing?->getName() === 'string' && is_int($value)) {
                    $value = (string) $value;
                }
                $enum = $className::tryFrom($value);
                if ($enum === null) {
                    throw new InvalidArgumentException(sprintf('Invalid backed enum value for %s.', $className));
                }

                return $enum;
            }

            /** @var class-string<UnitEnum> $className */
            foreach ($reflectionEnum->getCases() as $case) {
                if ($case->getName() === (string) $raw) {
                    return $case;
                }
            }

            throw new InvalidArgumentException('Invalid enum value.');
        }

        if (! is_array($raw)) {
            throw new InvalidArgumentException(sprintf('Expected object-shaped array for %s.', $className));
        }

        return $this->hydrateObject($raw, $className);
    }
}
