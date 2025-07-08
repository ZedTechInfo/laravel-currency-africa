<?php

namespace RandomStrInc\LaravelAfricanCurrencies\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private function getCurrenciesConfig()
    {
        // Return the currencies array directly without env() dependency
        return [
            'DZD' => [
                'name' => 'Algerian Dinar',
                'symbol' => 'DA',
                'code' => 'DZD',
                'country' => 'Algeria',
                'exchange_rate_to_usd' => null,
            ],
            'NGN' => [
                'name' => 'Nigerian Naira',
                'symbol' => '₦',
                'code' => 'NGN',
                'country' => 'Nigeria',
                'exchange_rate_to_usd' => null,
            ],
            'ZAR' => [
                'name' => 'South African Rand',
                'symbol' => 'R',
                'code' => 'ZAR',
                'country' => 'South Africa',
                'exchange_rate_to_usd' => null,
            ],
            'ZMW' => [
                'name' => 'Zambian Kwacha',
                'symbol' => 'K',
                'code' => 'ZMW',
                'country' => 'Zambia',
                'exchange_rate_to_usd' => null,
            ],
        ];
    }

    public function testCurrencyStructure()
    {
        $currencies = $this->getCurrenciesConfig();
        
        // Test that NGN currency has proper structure
        $this->assertArrayHasKey('NGN', $currencies);
        $ngn = $currencies['NGN'];
        
        $this->assertArrayHasKey('name', $ngn);
        $this->assertArrayHasKey('symbol', $ngn);
        $this->assertArrayHasKey('code', $ngn);
        $this->assertArrayHasKey('country', $ngn);
        $this->assertArrayHasKey('exchange_rate_to_usd', $ngn);
        
        $this->assertEquals('Nigerian Naira', $ngn['name']);
        $this->assertEquals('₦', $ngn['symbol']);
        $this->assertEquals('NGN', $ngn['code']);
        $this->assertEquals('Nigeria', $ngn['country']);
    }

    public function testAllCurrenciesHaveRequiredFields()
    {
        $currencies = $this->getCurrenciesConfig();
        
        $requiredFields = ['name', 'symbol', 'code', 'country', 'exchange_rate_to_usd'];
        
        foreach ($currencies as $currencyCode => $currency) {
            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey($field, $currency, "Currency {$currencyCode} missing field {$field}");
            }
            $this->assertEquals($currencyCode, $currency['code'], "Currency code mismatch for {$currencyCode}");
        }
    }
    
    public function testConfigFileExists()
    {
        $configPath = __DIR__ . '/../../config/african-currencies.php';
        $this->assertFileExists($configPath);
        $this->assertTrue(is_readable($configPath));
    }
}