<?php

namespace App\Http\Controllers;

use App\Client;
use App\Currency;
use App\Purse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

/**
 * Class ClientController
 * @package App\Http\Controllers
 */
class ClientController extends Controller
{
    const DUBLICATE_CLIENT_ERROR = 'Client is already registered';
    const SAVE_CLIENT_ERROR = 'Error save new client';
    const SAVE_PURSE_ERROR = 'Error save new purse';
    const UNAVAILABLE_CURRENCY_ERROR = 'Currency not found';
    const SEARCH_TERM = 'search';

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $clients = $this->filter($request);
        return ClientResource::collection($clients->get());
    }

    /**
     * @param int $id
     * @return ClientResource|\Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $client = Client::with('purse.currency')
            ->where('id', $id)
            ->first();
        if ($client) {
            return new ClientResource($client);
        }
        return response()
            ->json(self::UNAVAILABLE_CURRENCY_ERROR, Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Request $request
     * @return ClientResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $client = new Client;
        $queryBuilder = Client::query();
        foreach ($client->getFillable() as $field) {
            $queryBuilder->where($field, '=', $request->input($field));
        }
        if ($queryBuilder->first()) {
            return response()
                ->json(self::DUBLICATE_CLIENT_ERROR, Response::HTTP_NOT_FOUND);
        }

        $currency = Currency::where('code', $request->only('code'))
            ->first();
        if (!$currency) {
            return response()
                ->json(self::UNAVAILABLE_CURRENCY_ERROR, Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();
            $client->fill($request->only($client->getFillable()));
            if (!$client->save()) {
                throw new \Exception(self::SAVE_CLIENT_ERROR);
            }
            $purse = new Purse;
            $purse->currency()->associate($currency);
            $purse->client()->associate($client);
            $purse->balance = 0;
            if (!$purse->save()) {
                throw new \Exception(self::SAVE_PURSE_ERROR);
            }
            DB::commit();
            $client->load('purse');
            return new ClientResource($client);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()
                ->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function filter(Request $request)
    {
        $queryBuilder = Client::query();
        if ($request->query(self::SEARCH_TERM)) {
            $queryBuilder->whereRaw(
                'LOWER(name) like ?',
                '%' . mb_strtolower($request->query(self::SEARCH_TERM)) . '%'
            );
        }
        return $queryBuilder;
    }
}
