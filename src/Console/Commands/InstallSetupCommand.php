<?php

namespace RandomStrInc\LaravelAfricanCurrencies\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:install-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads initial BOZ rates and validates ExchangeRate-API key.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting currency package setup...');

        // 1. Run currency:fetch-boz-rates command
        $this->info('Attempting to fetch initial BOZ rates...');
        try {
            $this->call('currency:fetch-boz-rates');
        } catch (\Exception $e) {
            $this->error('Failed to fetch BOZ rates: ' . $e->getMessage());
            Log::error('InstallSetupCommand: Failed to fetch BOZ rates: ' . $e->getMessage());
        }

        // 2. Validate ExchangeRate-API key
        $this->info('Validating ExchangeRate-API key...');
        $apiKey = config('african-currencies.exchange_rate_api_key');

        if (!$apiKey) {
            $this->warn('EXCHANGE_RATE_API_KEY is not set in your .env file or african-currencies.php config.');
            $this->warn('Please add EXCHANGE_RATE_API_KEY=your_api_key to your .env file for full functionality.');
            Log::warning('InstallSetupCommand: EXCHANGE_RATE_API_KEY not set.');
            return Command::FAILURE;
        }

        try {
            // Test with a common African currency pair, e.g., NGN to USD
            $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/pair/NGN/USD");
            $data = $response->json();

            if ($response->successful() && isset($data['conversion_rate'])) {
                $this->info('ExchangeRate-API key validated successfully.');
            } else {
                $this->error('ExchangeRate-API key validation failed. Response: ' . json_encode($data));
                Log::error('InstallSetupCommand: ExchangeRate-API key validation failed. Response: ' . json_encode($data));
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Error validating ExchangeRate-API key: ' . $e->getMessage());
            Log::error('InstallSetupCommand: Error validating ExchangeRate-API key: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Currency package setup complete.');
        return Command::SUCCESS;
    }
}
