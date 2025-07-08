# Laravel African Currencies

A Laravel package to manage African currencies and perform currency conversions.

## Installation

YouYou can install the package via composer:

```bash
composer require random-str-inc/laravel-african-currencies
```

For Laravel 12 or higher, the package will be automatically discovered.

You can publish the configuration file using:

```bash
php artisan vendor:publish --provider="RandomStrInc\LaravelAfricanCurrencies\LaravelAfricanCurrenciesServiceProvider" --tag="african-currencies-config"
```

## Initial Setup

After installation, run the setup command to fetch initial BOZ rates and validate your ExchangeRate-API key:

```bash
php artisan currency:install-setup
```

## Usage


### Fetching Bank of Zambia Exchange Rates

To fetch the latest ZMW/USD exchange rates from the Bank of Zambia, run the following Artisan command:

```bash
php artisan currency:fetch-boz-rates
```

This command will download the latest rates, parse them, and store them in your database. It uses local caching to avoid repeated downloads within a short period.

### Get all supported African currencies

```php
use RandomStrInc\LaravelAfricanCurrencies\Facades\AfricanCurrency;

$currencies = AfricanCurrency::getAllCurrencies();
// Returns an array of currency details
```

### Get details of a specific currency

```php
use RandomStrInc\LaravelAfricanCurrencies\Facades\AfricanCurrency;

$zmwDetails = AfricanCurrency::getCurrencyDetails('ZMW');
// Returns an array with ZMW details or null if not found
```

### Convert currency

```php
use RandomStrInc\LaravelAfricanCurrencies\Facades\AfricanCurrency;

$convertedAmount = AfricanCurrency::convert(100, 'ZMW', 'NGN');
// Converts 100 ZMW to NGN based on the configured exchange rates
```

## Configuration

The `config/african-currencies.php` file allows you to define the African currencies supported by the package and their exchange rates relative to USD. You can add, remove, or modify currency entries as needed.

### Exchange Rate API Key

To use the ExchangeRate-API for non-ZMW conversions, you need to obtain an API key from [exchangerate-api.com](https://www.exchangerate-api.com/) and add it to your `.env` file:

```dotenv
EXCHANGE_RATE_API_KEY=your_api_key_here
```

### Currency Definitions

```php
// config/african-currencies.php
return [
    'exchange_rate_api_key' => env('EXCHANGE_RATE_API_KEY'),
    'currencies' => [
        'ZAR' => [
            'name' => 'South African Rand',
            'symbol' => 'R',
            'code' => 'ZAR',
            'country' => 'South Africa',
            'exchange_rate_to_usd' => null,
        ],
        // ... more currencies
    ],
];
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover a security vulnerability within this package, please send an e-mail to [Cyb3rK1d](mailto:cyb3rk1d@duck.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

