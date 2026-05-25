<?php
/**MIT License
@license MIT

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Builder;

use InvalidArgumentException;
use Tpay\OpenApi\Model\Fields\Address\City;
use Tpay\OpenApi\Model\Fields\Address\Phone;
use Tpay\OpenApi\Model\Fields\Address\PostalCode;
use Tpay\OpenApi\Model\Fields\Payer\Address;

class PayerDataBuilder
{
    private const MIN_LENGTH = 3;

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

        if ('' === $value || mb_strlen($value) < self::MIN_LENGTH) {
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
