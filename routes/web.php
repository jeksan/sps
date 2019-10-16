<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'api/v1'], function() use ($router) {
    $router->get('clients', 'ClientController@index');
    $router->get('clients/{id}', 'ClientController@show');
    $router->post('clients', 'ClientController@store');

    $router->get('currencies', 'CurrencyController@index');
    $router->get('currencies/{code}', 'CurrencyController@show');
    $router->post('currencies', 'CurrencyController@store');
    $router->post('currencies/{code}/quote', 'CurrencyController@updateQuote');

    $router->post('purses/{id}/refill', 'PurseController@refill');
    $router->post('purses/remittance', 'PurseController@remittance');

    $router->get('report', 'ReportController@loadData');
    $router->get('report/export', 'ReportController@generateCSV');
});

$router->get('/{route:.*}/', function ()  {
    return view('app');
});
