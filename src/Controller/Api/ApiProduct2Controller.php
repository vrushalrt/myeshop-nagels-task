<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use App\Services\CsvExporter;
use App\Services\ProductService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

// With Array cache
class ApiProduct2Controller extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CsvExporter $csvExporter,
        private CacheInterface $cache,
        private ProductService $productService,
    )
    {
    }

    /**
     * Get Producsts List
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/api/product2', name: 'app_api_api_product2')]
    public function getProductsApi(Request $request): JsonResponse
    {
        try {
            $searchProducts = $this->getProducts($request, $this->productRepository);
            $productList = $this->productService->productArrayCompiler($searchProducts['items']);


            return $this->json(
                [
                    'pagination' => $searchProducts['pagination'],
                    'data' => $productList,
                ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json(['error' => ['message' => 'Bad Request.'.$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/product2/export', name: 'app_api_product_export', methods: ['GET'])]
    public function getProductsExport(Request $request): Response
    {
        // Get Products
        $searchProducts = $this->getProducts($request, $this->productRepository);

        // Compile Product List Array
        $productList = $this->productService->productArrayCompiler($searchProducts['items']);

        return $this->csvExporter->export($productList, 'products.csv');
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

        // For Array Cache key generation unique as per parameters
        $key = 'getProducts2_'.md5(serialize([
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

            return $productRepository->productSearch($search, $pagination, $sort, $filter);

        } ) ;
    }
}
