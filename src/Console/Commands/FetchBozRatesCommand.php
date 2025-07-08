<?php

namespace RandomStrInc\LaravelAfricanCurrencies\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchBozRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:fetch-boz-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches daily ZMW/USD exchange rates from the Bank of Zambia.';

    protected $bozUrl = 'https://www.boz.zm/DAILY_RATES.xlsx';
    protected $cacheDisk = 'local'; // Using local storage disk
    protected $cachePath = 'boz_rates.xlsx';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching Bank of Zambia exchange rates...');

        try {
            $filePath = $this->downloadFile();
            if (!$filePath) {
                $this->error('Failed to download or retrieve BOZ rates file.');
                return Command::FAILURE;
            }

            $this->parseAndStoreRates($filePath);

            $this->info('Bank of Zambia exchange rates fetched and stored successfully.');
        } catch (\Exception $e) {
            Log::error('Error fetching BOZ rates: ' . $e->getMessage());
            $this->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function downloadFile()
    {
        // Check if file exists in cache and is recent enough (e.g., less than 1 hour old)
        if (Storage::disk($this->cacheDisk)->exists($this->cachePath) &&
            Storage::disk($this->cacheDisk)->lastModified($this->cachePath) > (time() - 3600)) {
            $this->info('Using cached BOZ rates file.');
            return Storage::disk($this->cacheDisk)->path($this->cachePath);
        }

        $this->info('Downloading BOZ rates file...');
        try {
            $response = Http::get($this->bozUrl);
            $response->throw(); // Throw an exception if a client or server error occurred

            Storage::disk($this->cacheDisk)->put($this->cachePath, $response->body());

            return Storage::disk($this->cacheDisk)->path($this->cachePath);
        } catch (\Exception $e) {
            Log::error('Failed to download BOZ rates file: ' . $e->getMessage());
            return null;
        }
    }

    protected function parseAndStoreRates(string $filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            $ratesToStore = [];

            // Assuming data starts from row 2 or 3 based on typical Excel formats
            // You might need to adjust this based on the actual structure of the BOZ XLSX
            for ($row = 2; $row <= $highestRow; $row++) {
                $dateValue = $sheet->getCellByColumnAndRow(2, $row)->getValue(); // Column B
                $timeValue = $sheet->getCellByColumnAndRow(3, $row)->getValue(); // Column C
                $buyingRate = $sheet->getCellByColumnAndRow(4, $row)->getValue(); // Column D
                $midRate = $sheet->getCellByColumnAndRow(5, $row)->getValue(); // Column E
                $sellingRate = $sheet->getCellByColumnAndRow(6, $row)->getValue(); // Column F

                // Convert Excel date to PHP DateTime object
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                    $formattedDate = $date->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning("Skipping row {$row} due to invalid date value: {$dateValue}");
                    continue;
                }

                // Assuming timeValue is in 'H:i' format or similar, or Excel time format
                // If it's Excel time, convert it
                if (is_numeric($timeValue)) {
                    $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($timeValue)->format('H:i:s');
                } else {
                    $time = $timeValue; // Assume it's already a string like 'HH:MM:SS'
                }

                // Validate rates are numeric
                if (!is_numeric($buyingRate) || !is_numeric($midRate) || !is_numeric($sellingRate)) {
                    Log::warning("Skipping row {$row} due to non-numeric rate values.");
                    continue;
                }

                $ratesToStore[] = [
                    'currency_code' => 'ZMW',
                    'date' => $formattedDate,
                    'time' => $time,
                    'buying_rate' => (float) $buyingRate,
                    'mid_rate' => (float) $midRate,
                    'selling_rate' => (float) $sellingRate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Sort rates to ensure the latest time for each date is processed last
            usort($ratesToStore, function($a, $b) {
                return strtotime($a['date'] . ' ' . $a['time']) <=> strtotime($b['date'] . ' ' . $b['time']);
            });

            // Store rates, replacing older entries for the same date and time
            DB::transaction(function () use ($ratesToStore) {
                foreach ($ratesToStore as $rate) {
                    DB::table('african_currency_exchange_rates')->updateOrInsert(
                        ['currency_code' => $rate['currency_code'], 'date' => $rate['date'], 'time' => $rate['time']],
                        $rate
                    );
                }
            });

        } catch (\Exception $e) {
            Log::error('Error parsing or storing BOZ rates: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by handle()
        }
    }
}
