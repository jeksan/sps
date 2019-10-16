<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Http\Resources\PurseResource;
use App\OperationHistory;
use App\Purse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
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
    const REMITTANCE_YOURSELF_ERROR = 'Don’t transfer money to yourself';

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
            $operationHistory->purse()->associate($purse);
            $operationHistory->currency_quote = $currency->quote;
            $operationHistory->amount = $amount;
            $operationHistory->date = date('Y-m-d H:i:s');
            $operationHistory->operation_comment = OperationHistory::OPERATION_REFILL;
            if (!$operationHistory->save()) {
                throw new \Exception(self::SAVE_HISTORY_ERROR);
            }
            DB::commit();
            return new PurseResource($purse);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()
                ->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remittance(Request $request)
    {
        $purseFrom = $purseTo = $purseFromCurrency = $purseFromAmount = $currency = $amount = null;
        try {
            $amount = (float)$request->input('amount');

            if (!($purseFrom = Purse::find($request->input('purse_from')))) {
                throw new \Exception(self::PURSE_FROM_NOT_FOUND);
            }

            if (!($purseTo = Purse::find($request->input('purse_to')))) {
                throw new \Exception(self::PURSE_TO_NOT_FOUND);
            }

            if ($purseFrom->id === $purseTo->id) {
                throw new \Exception(self::REMITTANCE_YOURSELF_ERROR);
            }

            if (!($currency = Currency::where('code', $request->input('currency'))->first())) {
                throw new \Exception(self::CURRENCY_TO_NOT_FOUND);
            }

            $purseFromCurrency = $purseFrom->currency()->first();
            if ($purseFromCurrency->id !== $currency->id &&
                $purseTo->currency()->first()->id !== $currency->id
            ) {
                throw new \Exception(self::CURRENCY_UNAVAILABLE_ERROR);
            }

            $purseFromAmount = $this->conversion($currency, $purseFromCurrency, $amount);
            if (((float)$purseFrom->balance - $purseFromAmount) < 0) {
                throw new \Exception(self::NOT_ENOUGH_MONEY_ERROR);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
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
                throw \Exception(self::SAVE_HISTORY_ERROR);
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
                throw \Exception(self::SAVE_HISTORY_ERROR);
            }

            if (!($purseFrom->save() && $purseTo->save())) {
                throw new \Exception(self::SAVE_REMITTANCE_ERROR);
            }
            DB::commit();
            return response()->json(true, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()
                ->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @param float $amount
     * @return float
     */
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
