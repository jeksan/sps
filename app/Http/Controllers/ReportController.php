<?php

namespace App\Http\Controllers;

use App\Client;
use App\Currency;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ReportController extends Controller
{
    const UNAVAILABLE_CLIENT_ERROR = 'Client not set';

    private function prepareData(int $clientId, string $periodStart = null, string $periodEnd = null)
    {
        $result = [];
        if ($client = Client::find($clientId)) {
            $purse = $client->purse()->first();
            $currency = $purse->currency()->first();
            $result = [
                'purse' => $purse->id,
                'balance' => $purse->balance,
                'currencyCode' => $currency->code,
                'sumOperations' => 0.0,
                'sumOperationsUSD' => 0.0,
            ];
            $history = [];
            $historyOperations = $purse->operationHistory();
            if ($periodStart || $periodEnd) {
                $periodStart && ($historyOperations->where('date', '>=', $periodStart));
                $periodEnd && ($historyOperations->where('date', '<=', $periodEnd));
            }
            foreach ($historyOperations->get() as $item) {
                $history[] = [
                    'date' => $item->date,
                    'amount' => (float)$item->amount,
                    'operation' => $item->operation_comment,
                ];
                $result['sumOperations'] += round(
                    abs((float)$item->amount),
                    Currency::SCALE
                );
                $result['sumOperationsUSD'] += round(
                    abs((float)$item->amount) * $item->currency_quote,
                    Currency::SCALE
                );
            }
            $result['history'] = $history;
            unset($item, $history);
        }
        return $result;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function validation(Request $request)
    {
        if (!$request->query('client-id')) {
            return false;
        }
        return true;
    }

    public function loadData(Request $request)
    {
        if (!$this->validation($request)) {
            return response(self::UNAVAILABLE_CLIENT_ERROR, 400);
        }

        $clientId = $request->query('client-id');
        $periodStart = $request->query('period-start');
        $periodEnd = $request->query('period-end');

        $data = $this->prepareData($clientId, $periodStart, $periodEnd);
        return response()->json($data, 200);
    }

    public function generateCSV(Request $request)
    {
        if (!$this->validation($request)) {
            return response(self::UNAVAILABLE_CLIENT_ERROR, 400);
        }

        $clientId = $request->query('client-id');
        $periodStart = $request->query('period-start');
        $periodEnd = $request->query('period-end');
        $data = $this->prepareData($clientId, $periodStart, $periodEnd);
    }
}
