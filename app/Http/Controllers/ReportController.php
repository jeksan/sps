<?php

namespace App\Http\Controllers;

use App\Client;
use App\Currency;
use App\Utilites\XMLExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Lumen\Routing\Controller;

class ReportController extends Controller
{
    const UNAVAILABLE_CLIENT_ERROR = 'Client not set';
    const EXPORT_HEADERS = [
        'Content-Type' => 'application/xml',
        'Content-Description' =>  'File Transfer',
    ];

    /**
     * @param int $clientId
     * @param string|null $periodStart
     * @param string|null $periodEnd
     * @return array
     */
    private function prepareData(int $clientId, string $periodStart = null, string $periodEnd = null)
    {
        $result = [];
        if ($client = Client::find($clientId)) {
            $purse = $client->purse()->first();
            $currency = $purse->currency()->first();
            $result = [
                'client' => $client->name,
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

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function prepareParameters(Request $request)
    {
        return [
            $request->query('client-id'),
            $request->query('period-start') ? new Carbon($request->query('period-start')) : null,
            $request->query('period-end') ? new Carbon($request->query('period-end')) : null,
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function loadData(Request $request)
    {
        if (!$this->validation($request)) {
            return response(
                self::UNAVAILABLE_CLIENT_ERROR,
                Response::HTTP_BAD_REQUEST
            );
        }
        $params = $this->prepareParameters($request);
        $data = $this->prepareData(...$params);
        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|
     *  \Laravel\Lumen\Http\ResponseFactory|
     *  \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Exception
     */
    public function generateXml(Request $request)
    {
        if (!$this->validation($request)) {
            return response(
                self::UNAVAILABLE_CLIENT_ERROR,
                Response::HTTP_BAD_REQUEST
            );
        }
        $params = $this->prepareParameters($request);
        $rawData = $this->prepareData(...$params);
        $export = new XMLExporter($rawData, $params[1], $params[2]);
        $exportData = $export->asXml();
        $exportFileName = $params[0] . '_'. date('d_m_Y_H_I_s') . '.xml';
        return response()->stream(
            function () use ($exportData) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, $exportData);
                fclose($handle);
            },
            Response::HTTP_OK,
            array_merge(
                self::EXPORT_HEADERS,
                [
                    'Content-Disposition' => 'attachment; filename=' . $exportFileName,
                ]
            )
        );
    }
}
