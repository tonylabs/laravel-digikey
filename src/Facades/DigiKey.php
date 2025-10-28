<?php

namespace TONYLABS\DigiKey\Facades;

use Illuminate\Support\Facades\Facade;
use TONYLABS\DigiKey\Product\KeywordSearchRequest;
use TONYLABS\DigiKey\Services\DigiKeyHttpClient;
use TONYLABS\DigiKey\Services\DigiKeyOAuthService;

/**
 * @method static object searchKeyword(string|array|KeywordSearchRequest $search, array $options = [])
 * @method static object getProductDetails(string $productNumber, array $includes = [], array $excludes = [])
 * @method static object getManufacturers()
 * @method static object getCategories()
 * @method static object getCategoryDetails(int $categoryId)
 * @method static object getDigiReelPricing(string $productNumber, int $requestedQuantity)
 * @method static object getRecommendedProducts(string $productNumber)
 * @method static object getProductSubstitutions(string $productNumber)
 * @method static object getProductAssociations(string $productNumber)
 * @method static object getPackageTypeByQuantity(string $productNumber, int $requestedQuantity)
 * @method static array getProductMedia(string $productNumber)
 * @method static array getProductPricing(string $productNumber, int $requestedQuantity)
 * @method static DigiKeyHttpClient getHttpClient()
 * @method static DigiKeyOAuthService getOAuthService()
 */
class DigiKey extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'digikey';
    }
}
