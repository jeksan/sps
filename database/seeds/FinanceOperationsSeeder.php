<?php

use App\OperationHistory;
use App\Purse;
use App\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceOperationsSeeder extends Seeder
{
    private $cntOperations = 20;
    private $minLimit = 1;
    private $maxPercent = 70;

    /**
     * Run remittance seeder
     * @throws Exception
     */
    public function run()
    {
        $purses = Purse::with('currency')->get();
        foreach ($purses as $purse) {
            $purseFrom = $purse;
            for ($iteration = 1; $iteration < $this->cntOperations; $iteration++) {
                if ($purseFrom->balance < $this->minLimit) {
                    break;
                }

                $rndAmount = round($purseFrom->balance * (random_int(1, $this->maxPercent) * 0.01), Currency::SCALE);
                $rndPurse = $purses->random();
                while ($purseFrom->id === $rndPurse->id) {
                    $rndPurse = $purses->random();
                }
                $currencies = [
                    $purseFrom->currency,
                    $rndPurse->currency
                ];
                $rndCurrency = $currencies[array_rand($currencies)];
                if ($purseFrom = $this->remittance($purseFrom, $rndPurse, $rndAmount, $rndCurrency)) {
                } else {
                    break;
                }
            }
        }
    }

    /**
     * @param Purse $purseFrom
     * @param Purse $purseTo
     * @param float $amount
     * @param Currency $currency
     * @return bool
     */
    private function remittance(Purse $purseFrom, Purse $purseTo, float $amount, Currency $currency)
    {
        $purseFromCurrency = $purseFrom->currency;
        $purseFromAmount = $this->conversion($currency, $purseFromCurrency, $amount);
        if (((float)$purseFrom->balance - $purseFromAmount) < 0) {
            return false;
        }

        try {
            $operationDate = date('Y-m-d H:i:s');
            DB::beginTransaction();
            $purseFrom->balance -= $purseFromAmount;
            $operationHistoryFrom = new OperationHistory([
                'purse_id' => $purseFrom->id,
                'date' => $operationDate,
                'currency_quote' => $purseFromCurrency->quote,
                'amount' => -1.0 * $purseFromAmount,
                'operation_comment' => OperationHistory::OPERATION_REMITTANCE,
            ]);
            if (!$purseFrom->operationHistory()->save($operationHistoryFrom)) {
                throw new Exception('Save history error');
            }

            $purseToCurrency = $purseTo->currency()
                ->first();
            $purseToAmount = $this->conversion($purseToCurrency, $currency, $amount);
            $purseTo->balance += $purseToAmount;

            $operationHistoryTo = new OperationHistory([
                'purse_id' => $purseTo->id,
                'date' => $operationDate,
                'currency_quote' => $purseToCurrency->quote,
                'amount' => $purseToAmount,
                'operation_comment' => OperationHistory::OPERATION_REMITTANCE,
            ]);
            if (!$purseTo->operationHistory()->save($operationHistoryTo)) {
                throw new \Exception('Save history error');
            }

            if (!($purseFrom->save() && $purseTo->save())) {
                throw new \Exception('Save remittance error');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return $purseFrom;
    }

    private function conversion(Currency $currencyFrom, Currency $currencyTo, float $amount)
    {
        if ($currencyFrom->id === $currencyTo->id) {
            return $amount;
        }
        $inBase = (float)$currencyFrom->quote * $amount;
        $result = $inBase / (float)$currencyTo->quote;
        return round($result, Currency::SCALE);
    }
}
