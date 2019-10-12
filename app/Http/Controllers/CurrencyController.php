<?php

namespace App\Http\Controllers;

use App\Currency;
use App\CurrencyQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    const SAVE_ERROR = 'Error in save currency';
    const DUBLICATE_CURRENCY_ERROR = 'Currency is already created';

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(Currency::all(), 200);
    }

    /**
     * @param string $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $code)
    {
        $currency = Currency::where('code', $code)->first();
        return $currency ? response()->json($currency) :
            response()->json('Not found', 404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if (Currency::where('code', $request->input('code'))
                ->orWhere('name', $request->input('name'))
                ->first()) {
                return response()->json(self::DUBLICATE_CURRENCY_ERROR, 400);
            }
            DB::beginTransaction();

            $currency = new Currency;
            $currency->fill($request->only($currency->getFillable()));
            $currency->save();

            $currencyQuotes = new CurrencyQuote;
            $currencyQuotes->date = date('Y-m-d');
            $currencyQuotes->quote = $request->input('quote');
            $currency->currencyQuotes()->save($currencyQuotes);
            DB::commit();
            return response()->json($currency, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(self::SAVE_ERROR, 500);
        }
    }

    /**
     * @param Request $request
     * @param string $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $code)
    {
        if ($currency = Currency::where('code', $code)->first()) {
            try {
                $currentDate = date('Y-m-d');
                $date = $request->input('date', $currentDate);
                if (strtotime($date) !== strtotime($currentDate)) {
                    $currency->quote = $request->input('quote');
                    $currency->save();
                }
                $currencyQuotes = new CurrencyQuote;
                $currencyQuotes->date = date('Y-m-d');
                $currencyQuotes->quote = $request->input('quote');

                if (!$currency->currencyQuotes()->save($currencyQuotes)) {
                    throw new \Exception(self::SAVE_ERROR);
                }
            } catch (\Exception $e) {
                return response()->json(self::SAVE_ERROR, 500);
            }
        } else {
            return response()->json('Not Found', 404);
        }
    }
}
