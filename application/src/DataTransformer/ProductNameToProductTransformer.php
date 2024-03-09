<?php

namespace App\DataTransformer;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductNameToProductTransformer implements DataTransformerInterface
{
    /**
     * @var ProductsRepository
     */
    private $productRepository;

    /**
     * @param ProductsRepository $productRepository
     */
    public function __construct(ProductsRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function transform($value)
    {
        if (null === $value) {
            return '';
        }
        if (!$value instanceof Products) {
            throw new \LogicException('The UserSelectTextType can only be used with Product objects');
        }
        return $value->getName();
    }

    public function reverseTransform($value)
    {
        $product = $this->productRepository->findOneBy(['name' => $value]);
        if (!$product) {
            throw new TransformationFailedException(sprintf('No product found with name "%s"', $value));
        }
        return $product;
    }
}
