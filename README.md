# Laravel DigiKey API Package

A Laravel package for integrating with the DigiKey Product Information API V4. This package provides OAuth2 authentication and comprehensive access to DigiKey's product search and information APIs.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher (optimized for Laravel 11+)
- GuzzleHTTP 7.0+

## Installation

Install the package via Composer:

```bash
composer require tonylabs/laravel-digikey
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="TONYLABS\DigiKey\DigiKeyServiceProvider"
```

## Configuration

Add your DigiKey API credentials to your `.env` file:

```env
DIGIKEY_CLIENT_ID=your_client_id
DIGIKEY_CLIENT_SECRET=your_client_secret
DIGIKEY_BASE_URL=https://api.digikey.com
DIGIKEY_USE_SANDBOX=false
DIGIKEY_CUSTOMER_ID=your_customer_id
DIGIKEY_SCOPE=productinformation
```

## Usage

### Quick Start

```php
use TONYLABS\DigiKey\DigiKey;

$digikey = (new DigiKey())
    ->setCategoryFilter(872)
    ->setManufacturerFilter(1904)
    ->setLocaleLanguage('en')
    ->setLocaleCurrency('USD')
    ->setLocaleSite('US')
    ->setLimit(50)
    ->setOffset(100);

$results = $digikey->searchKeyword('DCP0606QTRY');

// Per-call overrides still work as before
$results = $digikey->searchKeyword('DCP0606QTRY', [
    'limit' => 25,
    'offset' => 0,
]);

// Pure category/manufacturer browse (no keywords)
$results = (new DigiKey())
    ->setCategoryFilter(872)
    ->setManufacturerFilter(1904)
    ->searchKeyword(null, [
        'limit' => 25,
    ]);
```

Calling `new DigiKey()` reads `DIGIKEY_CLIENT_ID` and `DIGIKEY_CLIENT_SECRET` from your configuration. You can override them explicitly:

```php
$digikey = new DigiKey(client_id: 'your_client_id', client_secret: 'your_client_secret');
```

The fluent helpers work alongside array payloads or `KeywordSearchRequest` instances:

```php
use TONYLABS\DigiKey\Product\KeywordSearchRequest;

$request = (new KeywordSearchRequest('resistor'))
    ->setMinimumQuantityAvailable(100);

$results = (new DigiKey())->searchKeyword($request);
```

To remove previously configured helpers, call `resetFilters()`. Use `resetPagination()` to clear any fluent limit or offset. The client
validates and refreshes OAuth tokens automatically through the existing
`validateToken()` flow.

Locale overrides may be cleared with `resetLocale()` which reverts to the configuration defaults.

If you would rather control credentials directly, instantiate
`new DigiKey(client_id: '...', client_secret: '...')` and use the instance.

### Basic Usage with Facade

```php
use TONYLABS\DigiKey\Facades\DigiKey;

// Search for products without constructing a request object
$results = DigiKey::searchKeyword('resistor');

// Optionally provide overrides using the same helper keys as DigiKey::searchKeyword
$stockedChips = DigiKey::searchKeyword('stm32', [
    'limit' => 50,
    'filters' => [
        'FilterOptionsRequest' => [
            'MinimumQuantityAvailable' => 25,
        ],
    ],
]);

// Get product details
$productDetails = DigiKey::getProductDetails('296-1173-1-ND');

// Get manufacturers
$manufacturers = DigiKey::getManufacturers();

// Access the lower-level array API if needed
// $raw = DigiKey::getHttpClient();
```

### OAuth2 Authentication

The package automatically handles OAuth2 authentication using client credentials flow. Tokens are automatically obtained and cached when needed.

```php
use TONYLABS\DigiKey\Services\DigiKeyOAuthService;

// Get access token (automatically cached)
$oauth = app(DigiKeyOAuthService::class);
$accessToken = $oauth->getAccessToken();

// Check if we have a valid cached token
$hasValidToken = $oauth->hasValidToken();

// Clear cached token if needed
$oauth->clearCachedToken();
```

## API Methods

- `searchKeyword(string|array|KeywordSearchRequest $search, array $options = [])` - Search for products using keywords
- `getProductDetails(string $productNumber)` - Get detailed product information
- `getManufacturers()` - Get list of manufacturers
- `getCategories()` - Get list of categories
- `getProductPricing(string $productNumber, int $quantity)` - Get product pricing
- `getRecommendedProducts(string $productNumber)` - Get recommended products
- And many more...

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
