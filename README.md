# Laravel Digikey API Package

A Laravel package for integrating with the Digikey Product Information API V4. This package provides OAuth2 authentication and comprehensive access to Digikey's product search and information APIs.

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
php artisan vendor:publish --provider="TONYLABS\Digikey\DigikeyServiceProvider"
```

## Configuration

Add your Digikey API credentials to your `.env` file:

```env
DIGIKEY_CLIENT_ID=your_client_id
DIGIKEY_CLIENT_SECRET=your_client_secret
DIGIKEY_BASE_URL=https://api.digikey.com
DIGIKEY_USE_SANDBOX=false
DIGIKEY_CUSTOMER_ID=your_customer_id
```

## Usage

### Basic Usage with Facade

```php
use TONYLABS\Digikey\Facades\Digikey;
use TONYLABS\Digikey\Product\KeywordSearchRequest;

// Search for products
$searchRequest = new KeywordSearchRequest(
    keywords: 'resistor',
    recordCount: 25,
    recordStartPosition: 0
);

$results = Digikey::searchKeyword($searchRequest->toArray());

// Get product details
$productDetails = Digikey::getProductDetails('296-1173-1-ND');

// Get manufacturers
$manufacturers = Digikey::getManufacturers();
```

### Instantiate with Explicit Credentials

If you prefer not to rely on the published configuration, you can build a
standalone client by supplying the API credentials directly:

```php
use TONYLABS\Digikey\Services\DigikeyApiService;

$digikey = DigikeyApiService::createWithCredentials(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
);

$results = $digikey->searchKeyword([
    'Keywords' => 'resistor',
    'RecordCount' => 10,
]);
```

Optional configuration overrides (such as toggling the sandbox URL or
adjusting locale settings) can be passed as the third argument to
`createWithCredentials`.

### OAuth2 Authentication

The package automatically handles OAuth2 authentication using client credentials flow. Tokens are automatically obtained and cached when needed.

```php
use TONYLABS\Digikey\Services\DigikeyOAuthService;

// Get access token (automatically cached)
$oauth = app(DigikeyOAuthService::class);
$accessToken = $oauth->getAccessToken();

// Check if we have a valid cached token
$hasValidToken = $oauth->hasValidToken();

// Clear cached token if needed
$oauth->clearCachedToken();
```

## API Methods

- `searchKeyword(array $searchRequest)` - Search for products using keywords
- `getProductDetails(string $productNumber)` - Get detailed product information
- `getManufacturers()` - Get list of manufacturers
- `getCategories()` - Get list of categories
- `getProductPricing(string $productNumber, int $quantity)` - Get product pricing
- `getRecommendedProducts(string $productNumber)` - Get recommended products
- And many more...

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
