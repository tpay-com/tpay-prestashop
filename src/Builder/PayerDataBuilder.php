<?php

declare(strict_types=1);

namespace Tpay\Builder;

use InvalidArgumentException;
use Tpay\OpenApi\Model\Fields\Address\City;
use Tpay\OpenApi\Model\Fields\Address\Phone;
use Tpay\OpenApi\Model\Fields\Address\PostalCode;
use Tpay\OpenApi\Model\Fields\Payer\Address;

class PayerDataBuilder
{
    private const VALIDATED_FIELDS = [
        'phone' => Phone::class,
        'address' => Address::class,
        'code' => PostalCode::class,
        'city' => City::class,
    ];

    private $payer = [];

    public function set(string $key, $value): self
    {
        if (null !== $value && '' !== $value) {
            $this->payer[$key] = $value;
        }

        return $this;
    }

    public function add(string $key, ?string $value): self
    {
        if (!isset(self::VALIDATED_FIELDS[$key])) {
            return $this;
        }

        $value = trim((string) $value);

        if ('' === $value) {
            return $this;
        }

        $fieldClass = self::VALIDATED_FIELDS[$key];

        try {
            $field = new $fieldClass();
            $field->setValue($value);
            $this->payer[$key] = $value;
        } catch (InvalidArgumentException $e) {
        }

        return $this;
    }

    public function get(): array
    {
        return $this->payer;
    }
}
