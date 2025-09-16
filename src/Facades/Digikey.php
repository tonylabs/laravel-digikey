<?php

namespace TONYLABS\Digikey\Facades;

use Illuminate\Support\Facades\Facade;
use TONYLABS\Digikey\Product\KeywordSearchRequest;
use TONYLABS\Digikey\Product\SearchResponse;
use TONYLABS\Digikey\Services\DigikeyHttpClient;
use TONYLABS\Digikey\Services\DigikeyOAuthService;

/**
 * @method static array searchKeyword(array $searchRequest)
 * @method static array getProductDetails(string $productNumber, array $includes = [], array $excludes = [])
 * @method static array getManufacturers()
 * @method static array getCategories()
 * @method static array getCategoryDetails(int $categoryId)
 * @method static array getDigiReelPricing(string $productNumber, int $requestedQuantity)
 * @method static array getRecommendedProducts(string $productNumber)
 * @method static array getProductSubstitutions(string $productNumber)
 * @method static array getProductAssociations(string $productNumber)
 * @method static array getPackageTypeByQuantity(string $productNumber, int $requestedQuantity)
 * @method static array getProductMedia(string $productNumber)
 * @method static array getProductPricing(string $productNumber, int $requestedQuantity)
 * @method static DigikeyHttpClient getHttpClient()
 * @method static DigikeyOAuthService getOAuthService()
 */
class Digikey extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'digikey';
    }
}