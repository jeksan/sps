<?php

use Illuminate\Database\Seeder;
use App\Client;
use App\Purse;

class ClientSeeder extends Seeder
{
    private $cntClient = 5000;
    /**
     * Run the client seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Client::class, $this->cntClient)
            ->create()
            ->each(function ($client) {
                $client->purse()->save(factory(Purse::class)->make());
            });
    }
}
