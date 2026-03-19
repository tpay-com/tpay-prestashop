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
    private $payer = [];

    private $validatedFields;

    public function __construct()
    {
        $this->validatedFields = [
            'phone' => new Phone(),
            'address' => new Address(),
            'code' => new PostalCode(),
            'city' => new City(),
        ];
    }

    public function set(string $key, $value): self
    {
        if (null !== $value && '' !== $value) {
            $this->payer[$key] = $value;
        }

        return $this;
    }

    public function add(string $key, ?string $value): self
    {
        if (!isset($this->validatedFields[$key])) {
            return $this;
        }

        $value = trim((string) $value);

        if ('' === $value) {
            return $this;
        }

        $field = $this->validatedFields[$key];

        try {
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
