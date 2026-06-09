<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

/**
 * Base immutable data carrier for cross-layer communication.
 *
 * Domain DTOs extend this class with readonly constructor properties and optional
 * static factories such as fromRequest() or fromModel() via traits.
 */
abstract class DataTransferObject implements Arrayable, JsonSerializable
{
    /**
     * Properties excluded from {@see toArray()} output (e.g. internal integer id).
     *
     * @return list<string>
     */
    protected function hiddenProperties(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $properties = $this->extractProperties();

        foreach ($this->hiddenProperties() as $hidden) {
            unset($properties[$hidden]);
        }

        return $this->transformArray($properties);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            throw new InvalidArgumentException(static::class.' must declare a constructor for fromArray().');
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (! array_key_exists($name, $data) && ! $parameter->isOptional()) {
                throw new InvalidArgumentException("Missing required DTO property [{$name}].");
            }

            $arguments[] = array_key_exists($name, $data)
                ? $data[$name]
                : $parameter->getDefaultValue();
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractProperties(): array
    {
        $properties = [];

        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (! $property->isInitialized($this)) {
                continue;
            }

            $properties[$property->getName()] = $property->getValue($this);
        }

        return $properties;
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    protected function transformArray(array $properties): array
    {
        $transformed = [];

        foreach ($properties as $key => $value) {
            $transformed[$key] = $this->transformValue($value);
        }

        return $transformed;
    }

    protected function transformValue(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->transformValue($item), $value);
        }

        return $value;
    }
}
