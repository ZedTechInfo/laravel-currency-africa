<?php

namespace RandomStrInc\LaravelAfricanCurrencies;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RandomStrInc\LaravelAfricanCurrencies\AfricanCurrencyConverter
 */
class AfricanCurrency extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'african-currency';
    }
}
