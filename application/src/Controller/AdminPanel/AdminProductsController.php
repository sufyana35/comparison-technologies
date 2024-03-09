<?php

namespace App\Controller\AdminPanel;

use App\Entity\Products;
use App\Form\FormProductManageType;
use App\Helper\HelperImageUploadHandler;
use App\Repository\ProductsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminProductsController extends AbstractController
{
    /**
     * Products Home Page
     *
     * @return Response
     */
    public function products(): Response
    {
        return $this->render('adminPanel/products/adminProducts.html.twig', [
            'page_name' => 'Products'
        ]);
    }

    /**
     * AJAX search products bar functionality
     *
     * @param Request $request
     * @param ProductsRepository $productsRepository
     *
     * @return JsonResponse
     */
    public function ajaxProductsSearch(Request $request, ProductsRepository $productsRepository): JsonResponse
    {
        $searchTerm  = (string) $request->query->get('term');
        $products    = $productsRepository->getAjaxProductsSearch($searchTerm);

        foreach ($products as $product) {
            $url = $this->generateUrl('productsManage', [
                'productId' => $product->getId(),
            ]);

            $object = new stdClass();
            $object->id = $product->getId();
            $object->url = $url;
            $object->value = $product->getTitle();

            $data[] = $object;
        }

        return new JsonResponse(
            json_encode($data ?? []),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * Products search page
     *
     * @param ProductsRepository $productsRepository
     * @param Request $request
     *
     * @return Response
     */
    public function productsSearch(ProductsRepository $productsRepository, Request $request): Response
    {
        $searchTerm     = (string) $request->query->get('q');
        $pageNumber     = (int) $request->query->get('page');
        $order          = (int) $request->query->get('order');

        $pageNumber     = $pageNumber == 0 ? 1 : $pageNumber;

        $products       = $productsRepository->getSearchedProducts($searchTerm, $pageNumber, $order);
        $productsCount  = $productsRepository->getSearchedProductsCount($searchTerm);

        return $this->render('adminPanel/products/adminProductsSearch.html.twig', [
            'page_name'         => 'Search Products',
            'products'          => $products,
            'page_number'       => $pageNumber,
            'number_of_pages'   => floor(($productsCount / 20) + 1),
            'products_count'    => $productsCount,
            'search_term'       => $searchTerm,
            'order'             => $order
        ]);
    }

    /**
     * Product delete
     *
     * @param integer $productId
     * @param ProductsRepository $productsRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function productsDelete(
        int $productId,
        ProductsRepository $productsRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($productsRepository->find($productId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'products'
        );
    }

    /**
     * Manage Product
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param integer $productId
     * @param ProductsRepository $productsRepository
     *
     * @return Response
     */
    public function productsManage(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator,
        int $productId,
        ProductsRepository $productsRepository
    ): Response {
        $product = $productsRepository->findProduct($productId);
        $product ?? throw $this->createNotFoundException('The product does not exist');
        $hasDuplicates = null;
        $productFilterValuesValidation = null;

        $formProductManageType = $this->createForm(FormProductManageType::class, $product);
        $formProductManageType->handleRequest($request);
        if ($formProductManageType->isSubmitted() && $formProductManageType->isValid()) {
            $submittedProductData = $formProductManageType->getData();
            $submittedProductData->setUpdatedAt(new DateTime());

            $categoryIds = [];
            foreach ($product->getCategoryProducts() as $categoryProducts) {
                $categoryIds[] = $categoryProducts->getCategory()->getId();

                //Is category unique
                $hasDuplicates = count($categoryIds) > count(array_unique($categoryIds));
                if ($hasDuplicates) {
                    $error[0] = array(
                        'propertyPath' => 'categoryProducts',
                        'message' => 'You have more than one same category selected'
                    );
                    $errorMessages = $error;
                }
            }

            //Product Filter Values validation
            foreach ($submittedProductData->getProductFilterValues() as $productFilterValues) {
                $condition = in_array($productFilterValues->getProductFilter()->getCategory()->getId(), $categoryIds);
                if (!$condition) {
                    $productFilterValuesValidation = true;
                }
            }

            if ($productFilterValuesValidation) {
                $error[0] = array(
                    'propertyPath' => 'productFilterValues',
                    'message' => "The selected Product Filter Values don't match the product categories"
                );
                $errorMessages = $error;
            }


            if (!$hasDuplicates && !$productFilterValuesValidation) {
                $entityManager->persist($submittedProductData);
                $entityManager->flush();

                $image_1 = $formProductManageType->get('image_1')->getData();
                if ($image_1) {
                    $imageUploader = new HelperImageUploadHandler();
                    $imageUploader->productUpload($image_1, $submittedProductData->getDhsSku(), 72);
                }
            }
        }

        if ($formProductManageType->isSubmitted() && !$formProductManageType->isValid()) {
            $errors = $validator->validate($formProductManageType->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/products/adminProductsManage.html.twig', [
            'page_name'                  => 'Edit Product',
            'product'                    => $product,
            'form_product_manage_type'   => $formProductManageType,
            'errors'                     => $errorMessages ?? null
        ]);
    }

    /**
     * Product Add
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function productsAdd(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $formProductManageType = $this->createForm(FormProductManageType::class, new Products());
        $formProductManageType->handleRequest($request);
        $hasDuplicates = null;
        $productFilterValuesValidation = null;

        if ($formProductManageType->isSubmitted() && $formProductManageType->isValid()) {
            $submittedProductData = $formProductManageType->getData();
            $submittedProductData->setCreatedAt(new DateTime());
            $submittedProductData->setDhsName('');
            $submittedProductData->setNssName('');
            $submittedProductData->setCpsCostExVat(0);
            $submittedProductData->setCpsSellExVat(0);
            $submittedProductData->setReviewRating(0);
            $submittedProductData->setReviewCount(0);
            $submittedProductData->setQuantitySold(0);
            $submittedProductData->setSalesRank(0);

            $categoryIds = [];
            foreach ($submittedProductData->getCategoryProducts() as $categoryProducts) {
                $categoryIds[] = $categoryProducts->getCategory()->getId();

                $hasDuplicates = count($categoryIds) > count(array_unique($categoryIds));
                if ($hasDuplicates) {
                    $error[0] = array(
                        'propertyPath' => 'categoryProducts',
                        'message' => 'You have more than one same category selected'
                    );
                    $errorMessages = $error;
                }
            }

            //Product Filter Values validation
            foreach ($submittedProductData->getProductFilterValues() as $productFilterValues) {
                $condition = in_array($productFilterValues->getProductFilter()->getCategory()->getId(), $categoryIds);
                if (!$condition) {
                    $productFilterValuesValidation = true;
                }
            }

            if ($productFilterValuesValidation) {
                $error[0] = array(
                    'propertyPath' => 'productFilterValues',
                    'message' => "The selected Product Filter Values don't match the product categories"
                );
                $errorMessages = $error;
            }

            if (!$hasDuplicates && !$productFilterValuesValidation) {
                $entityManager->persist($submittedProductData);
                $entityManager->flush();

                $image_1 = $formProductManageType->get('image_1')->getData();
                if ($image_1) {
                    $imageUploader = new HelperImageUploadHandler();
                    $imageUploader->productUpload($image_1, $submittedProductData->getDhsSku(), 72);
                }

                return $this->redirectToRoute(
                    'productsManage',
                    [
                        'productId' => $submittedProductData->getId()
                    ]
                );
            }
        }

        if ($formProductManageType->isSubmitted() && !$formProductManageType->isValid()) {
            $errors = $validator->validate($formProductManageType->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/products/adminProductsManage.html.twig', [
            'page_name'                  => 'Add Product',
            'form_product_manage_type'   => $formProductManageType,
            'errors'                     => $errorMessages ?? null
        ]);
    }
}
