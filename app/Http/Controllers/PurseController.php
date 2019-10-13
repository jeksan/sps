<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Http\Resources\PurseResource;
use App\OperationHistory;
use App\Purse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

class PurseController extends Controller
{
    const SAVE_PURSE_REFILL_ERROR = 'Error save refill data in purse';
    const SAVE_REMITTANCE_ERROR = 'Error in remittance';
    const SAVE_HISTORY_ERROR = 'Error save operation history';
    const PURSE_FROM_NOT_FOUND = 'Purse from not found';
    const PURSE_TO_NOT_FOUND = 'Purse to not found';
    const CURRENCY_TO_NOT_FOUND = 'Currency for remittance not found';
    const CURRENCY_UNAVAILABLE_ERROR = 'Unavailable currency';
    const NOT_ENOUGH_MONEY_ERROR = 'Not enough funds in the account';

    /**
     * @param Request $request
     * @param int $id
     * @return PurseResource|\Illuminate\Http\JsonResponse
     */
    public function refill(Request $request, int $id)
    {
        try {
            DB::beginTransaction();
            $purse = Purse::findOrFail($id);
            $amount = $request->input('amount', 0);
            $purse->balance += $amount;
            if (!$purse->save()) {
                throw new \Exception(self::SAVE_PURSE_REFILL_ERROR);
            }
            $currency = $purse->currency()->first();
            $operationHistory = new OperationHistory;
            $operationHistory->currency()->associate($currency);
            $operationHistory->purseTo()->associate($purse);
            $operationHistory->currency_quote = $currency->quote;
            $operationHistory->amount = $amount;
            $operationHistory->date = date('Y-m-d');
            if (!$operationHistory->save()) {
                throw new \Exception(self::SAVE_HISTORY_ERROR);
            }
            DB::commit();
            return new PurseResource($purse);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remittance(Request $request)
    {
        $purseFrom = $purseTo = $currency = $amount = null;
        try {
            $amount = (float)$request->input('amount');

            if (!($purseFrom = Purse::find($request->input('purse_from')))) {
                throw new \Exception(self::PURSE_FROM_NOT_FOUND);
            }

            if (!($purseTo = Purse::find($request->input('purse_to')))) {
                throw new \Exception(self::PURSE_TO_NOT_FOUND);
            }

            if (!($currency = Currency::where('code', $request->input('currency'))->first())) {
                throw new \Exception(self::CURRENCY_TO_NOT_FOUND);
            }

            if ($purseFrom->currency()->first()->id !== $currency->id &&
                $purseTo->currency()->first()->id !== $currency->id
            ) {
                throw new \Exception(self::CURRENCY_UNAVAILABLE_ERROR);
            }

            if ((float)$purseFrom->balance - ($amount * (float)$currency->quote) < 0) {
                throw new \Exception(self::NOT_ENOUGH_MONEY_ERROR);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }

        try {
            DB::beginTransaction();
            $operationHistory = new OperationHistory;
            $operationHistory->purseFrom()->associate($purseFrom);
            $operationHistory->purseTo()->associate($purseTo);
            $operationHistory->currency()->associate($currency);
            $operationHistory->currency_quote = $currency->quote;
            $operationHistory->amount = $amount;
            $operationHistory->date = date('Y-m-d');
            if (!$operationHistory->save()) {
                throw \Exception(self::SAVE_HISTORY_ERROR);
            }

            $purseFrom->balance -= $this->conversion($purseFrom->currency()->first(), $currency, $amount);
            $purseTo->balance += $this->conversion($purseTo->currency()->first(), $currency, $amount);

            if (!($purseFrom->save() && $purseTo->save())) {
                throw new \Exception(self::SAVE_REMITTANCE_ERROR);
            }
            DB::commit();
            return response()->json(true, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    private function conversion(Currency $currencyFrom, Currency $currencyTo, float $amount)
    {
        if ($currencyFrom->id === $currencyTo->id) {
            return $amount;
        }
        $base = (float)$currencyFrom->quote * $amount;
        $result = $base / (float)$currencyTo->quote;
        return $result;
    }

}
