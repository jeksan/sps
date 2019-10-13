<?php

use App\Purse;
use App\OperationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class StartBalanceForClientSeeder extends Seeder
{
    private $maxAmount = 1000;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currentDate = date('Y-m-d');
        foreach (Purse::with('currency')->get() as $purse) {
            try {
                DB::beginTransaction();
                $rndAmount = mt_rand(1, $this->maxAmount) + abs(
                    1-mt_rand(1, $this->maxAmount) / $this->maxAmount
                    ) ;
                $purse->balance = $rndAmount;
                if (!$purse->save()) {
                    throw new Exception('Error refill purse');
                }
                $operationHistory = new OperationHistory;
                $operationHistory->purseTo()->associate($purse);
                $operationHistory->currency()->associate($purse->currency);
                $operationHistory->currency_quote = $purse->currency->quote;
                $operationHistory->date = $currentDate;
                $operationHistory->amount = $rndAmount;
                if (!$operationHistory->save()) {
                    throw new Exception('Error save operations history');
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                print $e->getMessage() . "\n";
            }
        }
    }
}
