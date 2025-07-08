<?php

namespace RandomStrInc\LaravelAfricanCurrencies;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricanCurrencyConverter
{
    protected $currencies;
    protected $exchangeRateApiKey;

    public function __construct()
    {
        $this->currencies = config('african-currencies.currencies');
        $this->exchangeRateApiKey = config('african-currencies.exchange_rate_api_key');
    }

    public function convert(float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if (!isset($this->currencies[$fromCurrency]) || !isset($this->currencies[$toCurrency])) {
            Log::warning("Attempted to convert unknown currency: {$fromCurrency} or {$toCurrency}");
            return null; // Or throw an exception
        }

        // Handle ZMW conversions using BOZ rates
        if ($fromCurrency === 'ZMW' || $toCurrency === 'ZMW') {
            $zmwRate = $this->getLatestZmwUsdMidRate();
            if ($zmwRate === null) {
                Log::error('ZMW exchange rate not available from BOZ. Cannot perform ZMW conversion.');
                return null; // Cannot convert if ZMW rate is not available
            }

            // Convert both to/from USD using the ZMW rate
            $rateFrom = ($fromCurrency === 'ZMW') ? $zmwRate : ($this->currencies[$fromCurrency]['exchange_rate_to_usd'] ?? null);
            $rateTo = ($toCurrency === 'ZMW') ? $zmwRate : ($this->currencies[$toCurrency]['exchange_rate_to_usd'] ?? null);

            // If the non-ZMW currency doesn't have a hardcoded USD rate, try fetching from API
            if ($fromCurrency !== 'ZMW' && $rateFrom === null) {
                $rateFrom = $this->fetchExchangeRateFromApi($fromCurrency, 'USD');
            }
            if ($toCurrency !== 'ZMW' && $rateTo === null) {
                $rateTo = $this->fetchExchangeRateFromApi($toCurrency, 'USD');
            }

        } else {
            // For non-ZMW conversions, use ExchangeRate-API
            $rateFrom = $this->fetchExchangeRateFromApi($fromCurrency, 'USD');
            $rateTo = $this->fetchExchangeRateFromApi($toCurrency, 'USD');
        }

        if ($rateFrom === null || $rateTo === null || $rateFrom === 0.0) {
            Log::error("Missing or invalid exchange rates for conversion: {$fromCurrency} to {$toCurrency}. From Rate: {$rateFrom}, To Rate: {$rateTo}");
            return null; // Or throw an exception if rates are missing or invalid
        }

        // Convert to USD first, then to the target currency
        $amountInUsd = $amount / $rateFrom;
        $convertedAmount = $amountInUsd * $rateTo;

        return round($convertedAmount, 2);
    }

    public function getCurrencyDetails(string $currencyCode): ?array
    {
        $currencyCode = strtoupper($currencyCode);
        return $this->currencies[$currencyCode] ?? null;
    }

    public function getAllCurrencies(): array
    {
        return $this->currencies;
    }

    protected function getLatestZmwUsdMidRate(): ?float
    {
        $latestRate = DB::table('african_currency_exchange_rates')
                        ->where('currency_code', 'ZMW')
                        ->orderByDesc('date')
                        ->orderByDesc('time')
                        ->first();

        return $latestRate ? (float) $latestRate->mid_rate : null;
    }

    protected function fetchExchangeRateFromApi(string $from, string $to): ?float
    {
        if (!$this->exchangeRateApiKey) {
            Log::error('EXCHANGE_RATE_API_KEY is not configured. Cannot fetch rates from ExchangeRate-API.');
            return null;
        }

        try {
            $response = Http::get("https://v6.exchangerate-api.com/v6/{$this->exchangeRateApiKey}/pair/{$from}/{$to}");
            $data = $response->json();

            if ($response->successful() && isset($data['conversion_rate'])) {
                return (float) $data['conversion_rate'];
            } else {
                Log::error("Failed to fetch exchange rate from ExchangeRate-API for {$from} to {$to}. Response: " . json_encode($data));
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Error fetching exchange rate from ExchangeRate-API for {$from} to {$to}: " . $e->getMessage());
            return null;
        }
    }
}