<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\CurrencyQuote;

class CurrencySeeder extends Seeder
{
    private $actualCurrencyRate = 'https://api.openrates.io/latest?base=USD';
    private $scaleRound = 6;
    private $currencies = [
        'AUD' => 'Australian Dollar (AUD)',
        'BRL' => 'Brazilian Real (BRL)',
        'GBP' => 'British Pound Sterline (GBP)',
        'BGN' => 'Bulgarian Lev (BGN)',
        'CAD' => 'Canadian Dollar (CAD)',
        'CNY' => 'Chinese Yuan Renminbi (CNY)',
        'HRK' => 'Croatian Kuna (HRK)',
        'CZK' => 'Czech Koruna (CZK)',
        'DKK' => 'Danish Krone (DKK)',
        'EUR' => 'Euro (EUR)',
        'HKD' => 'Hong Kong Dollar (HKD)',
        'HUF' => 'Hungarian Forint (HUF)',
        'ISK' => 'Icelandic Krona (ISK)',
        'IDR' => 'Indonesian Rupiah (IDR)',
        'INR' => 'Indian Rupee (INR)',
        'ILS' => 'Israeli Shekel (ILS)',
        'JPY' => 'Japanese Yen (JPY)',
        'MYR' => 'Malaysian Ringgit (MYR)',
        'MXN' => 'Mexican Peso (MXN)',
        'NZD' => 'New Zealand Dollar (NZD)',
        'NOK' => 'Norwegian Krone (NOK)',
        'PHP' => 'Philippine Peso (PHP)',
        'PLN' => 'Polish Zloty (PLN)',
        'RON' => 'Romanian Leu (RON)',
        'RUB' => 'Russian Rouble (RUB)',
        'SGD' => 'Singapore Dollar (SGD)',
        'ZAR' => 'South African Rand (ZAR)',
        'KRW' => 'South Korean Won (KRW)',
        'SEK' => 'Swedish Krona (SEK)',
        'CHF' => 'Swiss Franc (CHF)',
        'THB' => 'Thai Baht (THB)',
        'TRY' => 'Turkish Lira (TRY)',
        'USD' => 'US Dollar (USD)',
    ];

    /**
     *  Run currency seeder
     */
    public function run()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($this->actualCurrencyRate)->getBody();
            $response = json_decode($response, true);
            $currentDate = date('Y-m-d');
            foreach ($response['rates'] as $code => $rate) {
                if (!$this->currencies[$code]) {
                    continue;
                }

                $quote = round(1 / $rate, $this->scaleRound);
                try {
                    DB::beginTransaction();
                    $currency = new \App\Currency;
                    $currency->name  = $this->currencies[$code];
                    $currency->code = $code;
                    $currency->quote = $quote;
                    if(!$currency->save()) {
                        throw new Exception('Error save currency');
                    }
                    $currencyQuoteHistory = new CurrencyQuote([
                        'quote' => $quote,
                        'date' => $currentDate
                    ]);
                    if (!$currency->currencyQuoteHistory()->save($currencyQuoteHistory)) {
                        throw new Exception('Error save currency quotes');
                    }
                    unset($currencyQuoteHistory);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                }
            }
        } catch (Exception $e) {
            print $e->getMessage() . "\n";
        }
    }
}
