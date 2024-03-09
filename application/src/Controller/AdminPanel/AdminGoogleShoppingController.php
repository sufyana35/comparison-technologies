<?php

namespace App\Controller\AdminPanel;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AdminGoogleShoppingController extends AbstractController
{
    /**
     *
     * @param ProductsRepository $productsRepository
     * @param integer $pageNumber
     *
     * @return Response
     */
    public function googleShopping(ProductsRepository $productsRepository, int $pageNumber = 0): Response
    {
        $productCount = $productsRepository->getProductCountGoogleShopping();

        $filteredGoogleShoppingProducts = [];
        for ($x = 1; $x < floor(($productCount / 5000) + 2); $x++) {
            $googleShoppingProducts = $productsRepository->getAllProductsForGoogleShopping($x);

            foreach ($googleShoppingProducts as $googleShoppingProduct) {
                if (
                    file_exists($_ENV["UPLOAD_DIRECTORY"] . '/products/large/'
                    . $googleShoppingProduct['dhsSku'] . '.jpg') /** @phpstan-ignore-line */
                ) {
                    $filteredGoogleShoppingProducts[] = $googleShoppingProduct;
                }
            }
        }

        return $this->render('adminPanel/shopping/adminGoogleShopping.html.twig', [
            'page_name' => 'Google Shopping Feed',
            'product_count' => count($filteredGoogleShoppingProducts),
            'page_number'       => $pageNumber,
            'number_of_pages'   => floor((count($filteredGoogleShoppingProducts) / 5000) + 1),
            'google_shoppings' => $filteredGoogleShoppingProducts
        ]);
    }
}
