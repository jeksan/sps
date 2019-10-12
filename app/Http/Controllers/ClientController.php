<?php

namespace App\Http\Controllers;

use App\Client;
use App\Currency;
use App\Purse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    const DUBLICATE_CLIENT_ERROR = 'Client is already registered';
    const SAVE_CLIENT_ERROR = 'Error save new client';
    const SAVE_PURSE_ERROR = 'Error save new purse';

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(Client::all());
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $client = Client::find($id);
        return $client ? response()->json($client) :
            response()->json('Not found', 404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $client = new Client;
        $clientQueryBuilder = Client::query();
        foreach ($client->getFillable() as $field) {
            $clientQueryBuilder->when($field, $request->input($field));
        }
        if ($clientQueryBuilder->get()->isNotEmpty()) {
            return response()->json(self::DUBLICATE_CLIENT_ERROR, 400);
        }
        try {
            DB::beginTransaction();
            $client->fill($request->only($client->getFillable()));
            if (!$client->save()) {
                throw new \Exception(self::SAVE_CLIENT_ERROR);
            }
            $currency = Currency::where('code', $request->only('code'))
                ->firstOrFail();

            $purse = new Purse;
            $purse->currency()->create($currency);
            $purse->client()->create($client);
            $purse->balance = 0;
            if (!$purse->save()) {
                throw new \Exception(self::SAVE_PURSE_ERROR);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }
}
