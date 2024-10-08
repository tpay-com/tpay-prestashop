<?php

namespace Tpay\Service;

class ConstraintValidator
{
    /** @var SurchargeService|null */
    protected $surchargeService;

    public function __construct(\Tpay $module)
    {
        $this->surchargeService = $module->getService('tpay.service.surcharge');
    }

    public function validate(array $constraints, string $browser): bool
    {
        foreach ($constraints as $constraint) {
            switch ($constraint['type']) {
                case 'min':
                    if (!$this->validateMinimalTotal((float) $constraint['value'])) {
                        return false;
                    }

                    break;
                case 'max':
                    if (!$this->validateMaximalTotal((float) $constraint['value'])) {
                        return false;
                    }

                    break;
                case 'supported':
                    return $this->isApplePayPossible($constraint['field'], $browser);
                default:
                    break;
            }
        }

        return true;
    }

    public function isClientCountryValid(bool $isAllowed, string $clientCountry, array $specificCountry): bool
    {
        return $isAllowed && !in_array($clientCountry, $specificCountry);
    }

    private function validateMinimalTotal(float $minimal): bool
    {
        return $this->surchargeService->getTotalOrderAndSurchargeCost() >= $minimal;
    }

    private function validateMaximalTotal(float $maximal): bool
    {
        return $this->surchargeService->getTotalOrderAndSurchargeCost() <= $maximal;
    }

    private function isApplePayPossible(string $browserSupport, string $browser): bool
    {
        if ('ApplePaySession' == $browserSupport && 'Safari' == $browser) {
            return true;
        }

        return false;
    }
}
