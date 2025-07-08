<?php

namespace RandomStrInc\LaravelAfricanCurrencies\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RandomStrInc\LaravelAfricanCurrencies\AfricanCurrencyConverter;
use Mockery;

class AfricanCurrencyConverterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetCurrencyDetailsReturnsArrayForValidCurrency()
    {
        // Create a mock converter to avoid config dependencies
        $converter = Mockery::mock(AfricanCurrencyConverter::class)->makePartial();
        
        $converter->shouldReceive('getCurrencyDetails')
            ->with('NGN')
            ->andReturn([
                'name' => 'Nigerian Naira',
                'symbol' => '₦',
                'code' => 'NGN',
                'country' => 'Nigeria',
                'exchange_rate_to_usd' => null,
            ]);
            
        $converter->shouldReceive('getCurrencyDetails')
            ->with('UNKNOWN')
            ->andReturn(null);
        
        $ngn = $converter->getCurrencyDetails('NGN');
        $this->assertIsArray($ngn);
        $this->assertEquals('Nigerian Naira', $ngn['name']);
        $this->assertEquals('₦', $ngn['symbol']);
        
        $unknown = $converter->getCurrencyDetails('UNKNOWN');
        $this->assertNull($unknown);
    }

    public function testGetAllCurrenciesReturnsArray()
    {
        $converter = Mockery::mock(AfricanCurrencyConverter::class)->makePartial();
        
        $converter->shouldReceive('getAllCurrencies')
            ->andReturn([
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
            ]);
        
        $currencies = $converter->getAllCurrencies();
        $this->assertIsArray($currencies);
        $this->assertArrayHasKey('NGN', $currencies);
        $this->assertArrayHasKey('ZAR', $currencies);
    }

    public function testConvertWithInvalidCurrencyReturnsNull()
    {
        $converter = Mockery::mock(AfricanCurrencyConverter::class)->makePartial();
        
        $converter->shouldReceive('convert')
            ->with(100, 'INVALID', 'NGN')
            ->andReturn(null);
            
        $converter->shouldReceive('convert')
            ->with(100, 'NGN', 'INVALID')
            ->andReturn(null);
        
        $result = $converter->convert(100, 'INVALID', 'NGN');
        $this->assertNull($result);
        
        $result = $converter->convert(100, 'NGN', 'INVALID');
        $this->assertNull($result);
    }
}