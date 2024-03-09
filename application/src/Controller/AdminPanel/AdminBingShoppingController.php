<?php

namespace App\Controller\AdminPanel;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AdminBingShoppingController extends AbstractController
{
    /**
     * @param ProductsRepository $productsRepository
     * @param integer $pageNumber
     *
     * @return Response
     */
    public function bingShopping(ProductsRepository $productsRepository, int $pageNumber = 0): Response
    {
        $productCount = $productsRepository->getProductCountBingShopping();

        $filteredBingShoppingProducts = [];
        for ($x = 1; $x < floor(($productCount / 5000) + 2); $x++) {
            $bingShoppingProducts = $productsRepository->getAllProductsForBingShopping($x);

            foreach ($bingShoppingProducts as $bingShoppingProduct) {
                if (
                    file_exists($_ENV["UPLOAD_DIRECTORY"] . '/products/large/'
                    . $bingShoppingProduct['dhsSku'] . '.jpg') /** @phpstan-ignore-line */
                ) {
                    $filteredBingShoppingProducts[] = $bingShoppingProduct;
                }
            }
        }

        return $this->render('adminPanel/shopping/adminBingShopping.html.twig', [
            'page_name' => 'Bing Shopping Feed',
            'product_count' => count($filteredBingShoppingProducts),
            'page_number'       => $pageNumber,
            'number_of_pages'   => floor((count($filteredBingShoppingProducts) / 5000) + 1),
            'bing_shoppings' => $filteredBingShoppingProducts
        ]);
    }
}
