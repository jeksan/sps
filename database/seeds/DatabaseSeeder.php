<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('CurrencySeeder');
        $this->call('ClientSeeder');
        $this->call('StartBalanceForClientSeeder');
        $this->call('FinanceOperationsSeeder');
    }
}
