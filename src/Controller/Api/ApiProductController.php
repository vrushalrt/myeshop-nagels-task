<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Services\CsvExporter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * ApiProductController handles API requests for product data.
 *
 * This controller provides endpoints for retrieving and exporting product data.
 * It interacts with the ProductRepository to fetch data and uses caching mechanisms
 * to enhance performance. The controller also includes functionality to export
 * product data in CSV format.
 */
class ApiProductController extends AbstractController
{
    public function __construct(private CacheInterface $cache)
    {
    }

    /**
     * Get Producsts List
     *
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     */
    #[Route('/api/product', name: 'app_api_product', methods: ['GET'])]
    public function getProductsApi(Request $request, ProductRepository $productRepository): JsonResponse
    {
        try {
            $searchProducts = $this->getProducts($request, $productRepository);
            $productList = $this->productListArrayCompiler($searchProducts['items']);

            return new JsonResponse(
                [
                    'pagination' => $searchProducts['pagination'],
                    'data' => $productList,
                ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => ['message' => 'Bad Request.'.$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * CSV export for Product data
     *
     *
     */
    #[Route('/api/product/export', name: 'app_api_product_export', methods: ['GET'])]
    public function getProductsExport(Request $request, ProductRepository $productRepository): Response
    {
        $searchProducts = $this->getProducts($request, $productRepository);
        $productList = $this->productListArrayCompiler($searchProducts['items']);

        return (new CsvExporter())->export($productList, 'products.csv');
    }

    /**
     * Get product data
     *
     * @throws InvalidArgumentException
     */
    private function getProducts($request, $productRepository) : array
    {
        $search = $request->get('search', null);

        $pagination = [
            'page' => (int) $request->get('page', 1),
            'limit' => (int) $request->get('limit', 50)
        ];

        $sort = [
            'column' => $request->get('sortColumn', 'id'),
            'order' => strtoupper( $request->get('sortOrder', 'asc') )
        ];

        $filter = [
            'column' => $request->get('filterColumn', null),
            'value' => $request->get('filterValue', null),
            'operator' => $request->get('filterOp', null),
            'min' => $request->get('filterMin', null),
            'max' => $request->get('filterMax', null)
        ];

        // For Redis key generation unique as per parameters
        $key = 'getProducts_'.md5(serialize([
            $search,
            $pagination['page'],
            $pagination['limit'],
            $sort['column'],
            $sort['order'],
            $filter['column'],
            $filter['value'],
            $filter['operator'],
            $filter['min'],
            $filter['max']
        ]));

        // Cache Logic
        return $this->cache->get($key, function(ItemInterface $item) use ($request, $productRepository, $search, $pagination, $sort, $filter) {

            $item->expiresAfter(3600); // 1 hour
//            $item->expiresAfter(1); // 1 Sec

            return $productRepository->productSearch($search, $pagination, $sort, $filter);
        } ) ;
    }

    /**
     * Product List compiler function.
     *
     */
    private function productListArrayCompiler($products): array
    {
        $productList = [];
        foreach ($products as $productKey => $product)
        {
            $productList[] = [
                'id' =>  $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'quantity' => $product->getQuantity(),
                'price' => $product->getPrice(),
                'isActive' => $product->isActive()
            ];
        }
        return $productList;
    }
}
