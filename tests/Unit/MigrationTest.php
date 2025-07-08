<?php

namespace RandomStrInc\LaravelAfricanCurrencies\Tests\Unit;

use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    public function testMigrationFileExists()
    {
        $migrationPath = __DIR__ . '/../../database/migrations/2024_01_01_000000_create_african_currency_exchange_rates_table.php';
        $this->assertFileExists($migrationPath);
        $this->assertTrue(is_readable($migrationPath));
    }

    public function testMigrationFileContainsExpectedContent()
    {
        $migrationPath = __DIR__ . '/../../database/migrations/2024_01_01_000000_create_african_currency_exchange_rates_table.php';
        $content = file_get_contents($migrationPath);
        
        // Check for key elements in the migration
        $this->assertStringContainsString('african_currency_exchange_rates', $content);
        $this->assertStringContainsString('function up()', $content);
        $this->assertStringContainsString('function down()', $content);
        $this->assertStringContainsString('Schema::create', $content);
        $this->assertStringContainsString('Schema::dropIfExists', $content);
        $this->assertStringContainsString('currency_code', $content);
        $this->assertStringContainsString('mid_rate', $content);
        $this->assertStringContainsString('buying_rate', $content);
        $this->assertStringContainsString('selling_rate', $content);
    }

    public function testMigrationHasSyntaxCorrectPhp()
    {
        $migrationPath = __DIR__ . '/../../database/migrations/2024_01_01_000000_create_african_currency_exchange_rates_table.php';
        
        // Check PHP syntax without executing
        exec("php -l " . escapeshellarg($migrationPath), $output, $returnCode);
        $this->assertEquals(0, $returnCode, 'Migration file has PHP syntax errors: ' . implode("\n", $output));
    }
}