<?php

namespace App\Http\Controllers;

use App\Currency;
use App\CurrencyQuote;
use App\Http\Resources\CurrencyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

class CurrencyController extends Controller
{
    const SAVE_ERROR = 'Error in save currency';
    const CURRENCY_UNAVAILABLE_ERROR = 'Currency not found';
    const DUBLICATE_CURRENCY_ERROR = 'Currency is already created';

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return CurrencyResource::collection(Currency::all());
    }

    /**
     * @param string $code
     * @return CurrencyResource|\Illuminate\Http\JsonResponse
     */
    public function show(string $code)
    {
        $currency = Currency::where('code', $code)->first();
        if ($currency) {
            return new CurrencyResource($currency);
        }
        return response()->json(self::CURRENCY_UNAVAILABLE_ERROR, 404);
    }

    /**
     * @param Request $request
     * @return CurrencyResource|\Illuminate\Http\JsonResponse
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
            return new CurrencyResource($currency);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(self::SAVE_ERROR, 500);
        }
    }

    /**
     * @param Request $request
     * @param string $code
     * @return CurrencyResource|\Illuminate\Http\JsonResponse
     */
    public function updateQuote(Request $request, string $code)
    {
        $currency = Currency::where('code', $code)->first();
        if (!$currency) {
            return response()->json(self::CURRENCY_UNAVAILABLE_ERROR, 400);
        }
        try {
            $currentDate = date('Y-m-d');
            $date = $request->input('date', $currentDate);
            if (strtotime($date) === strtotime($currentDate)) {
                $currency->quote = $request->input('quote');
                $currency->save();
            }
            $currencyQuotes = new CurrencyQuote;
            $currencyQuotes->date = date('Y-m-d');
            $currencyQuotes->quote = $request->input('quote');

            if (!$currency->currencyQuotes()->save($currencyQuotes)) {
                throw new \Exception(self::SAVE_ERROR);
            }
            return new CurrencyResource($currency);
        } catch (\Exception $e) {
            return response()->json(self::SAVE_ERROR, 500);
        }
    }
}
