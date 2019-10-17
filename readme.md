# Simple payment system (SPS)

A simple payment system solves problems:
- customer and wallet registration in the currency registered in the system
- registration of currencies and downloading quotes to USD
- wallet replenishment
- transfers between wallets
- generation of a report on operations with a wallet with the ability to upload in xml format

## Based 
 [Laravel Lumen](https://lumen.laravel.com/) and 
 [Vue](https://vuejs.org/)

## Install

### Requirements
- PHP ^7.2
- PostgreSQL 10.10
- nodejs v10, npm v6.9

### Steps
```
composer install
```
```
php artisan migrate
```
##### Install with demo data
```
php artisan migrate --seed
```

```
npm install
```
```
npm run prod
```

##### For start local server without Apache, Nginx and other http servers:
````
php artisan serve
````
### View report
To access the visual interface, go to /

### API Routes
#### Clients 
**[GET] /api/v1/clients** - list all clients

parameters: 

   **search** - for search by name
    
**[GET] /api/v1/clients/{id}** - show concrete client by id

**[POST] /api/v1/clients** - registration new client

    body: {
        'name': 'Name client',
        'country': 'Country client',
        'city': 'City client',
        'code': 'Code registered currency'
    }
    
    
#### Currencies
**[GET] /api/v1/currencies** - list all currencies

**[GET] /api/v1/currencies/{code}** - show concrete client by code
    
   **{code}** - Char code currency

**[POST] /api/v1/currencies** - registration new currency
    
    body : {
    	'name': 'Name currency',
    	'code': 'Char code',
    	'quote': 'Rate by USD'
    }
    
**[POST] /api/currencies/{code}/quote** - update rate for concrete currency
    
   **{code}** - Char code currency
    
    body: {
        'quote': 'Rate by USD',
        'date': 'Quote date'
    }
    
    
#### Purses
**[POST] /api/purses/{id}/refill** - refill concrete purse
   
   **{id}** - id purse for refill
    
    body: {
        'amount': 'Amount refill'
    }
    
**[POST] /api/purses/remittance** - remittance between purses
    
    body: {
        'purse_from': 'Id purse donor',
        'purse_to': 'Id purse acceptor',
        'amount': 'Amount for remittance',
        'currency': 'Code currency for remittance'
    }    
    
    
##### Report
**[GET] /api/report** - fetch data by report for finance operations

parameters:

   **client-id** - 'Id of the client whose operations will be built report'
   
   **period-start** - 'Date for start period'
   
   **period-end** - 'Date for end period'
   
**[GET] /api/export** - export operations report in xml
   
parameters:
   
   **client-id** - 'Id of the client whose operations will be built report'
      
   **period-start** - 'Date for start period'
      
   **period-end** - 'Date for end period'

