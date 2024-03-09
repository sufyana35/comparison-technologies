<?php

namespace App\Controller\AdminPanel;

use App\Entity\Brands;
use App\Form\FormManageBrandsType;
use App\Helper\HelperImageUploadHandler;
use App\Repository\BrandsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminBrandsController extends AbstractController
{
    /**
     * Brands page
     *
     * @param BrandsRepository $brandsRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function brands(
        BrandsRepository $brandsRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->request->get('Id') && $this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($brandsRepository->find($request->request->get('Id')));
            $entityManager->flush();
        }

        return $this->render('adminPanel/brands/adminBrands.html.twig', [
            'page_name'  => 'Brands',
            'brands' => $brandsRepository->getAllBrands()
        ]);
    }

    /**
     * Add new brand
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function brandsAdd(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $brandForm = $this->createForm(FormManageBrandsType::class, new Brands());

        $brandForm->handleRequest($request);
        if ($brandForm->isSubmitted() && $brandForm->isValid()) {
            $brand = $brandForm->getData();
            $brand->setUpdatedAt(new DateTime());

            $entityManager->persist($brand);
            $entityManager->flush();

            $logo = $brandForm->get('logos')->getData();
            if ($logo) {
                $imageUploader = new HelperImageUploadHandler();
                $imageUploader->upload($logo, $brand->getReference(), 'brands/logos', 72, 80, 80);
            }

            $ticker = $brandForm->get('ticker')->getData();
            if ($ticker) {
                $imageUploader = new HelperImageUploadHandler();
                $imageUploader->upload($ticker, $brand->getReference(), 'brands/ticker', 72, null, 30);
            }
        }

        if ($brandForm->isSubmitted() && !$brandForm->isValid()) {
            $errors = $validator->validate($brandForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/brands/adminBrandsManage.html.twig', [
            'page_name' => 'Add A New Brand',
            'form'      => $brandForm,
            'errors'    => $errorMessages ?? null
        ]);
    }

    /**
     * Add or edit brand
     *
     * @param BrandsRepository $brandsRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer $brandId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function brandsManage(
        BrandsRepository $brandsRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $brandId,
        ValidatorInterface $validator
    ): Response {
        $brand = $brandsRepository->findBrand($brandId);
        $brand ?? throw $this->createNotFoundException('The brand does not exist');

        $brandForm = $this->createForm(FormManageBrandsType::class, $brand);
        $brandForm->handleRequest($request);
        if ($brandForm->isSubmitted() && $brandForm->isValid()) {
            $brand = $brandForm->getData();
            $brand->setUpdatedAt(new DateTime());

            $logo = $brandForm->get('logos')->getData();
            if ($logo) {
                $imageUploader = new HelperImageUploadHandler();
                $imageUploader->upload($logo, $brand->getReference(), 'brands/logos', null, 80, 80);
            }

            $ticker = $brandForm->get('ticker')->getData();
            if ($ticker) {
                $imageUploader = new HelperImageUploadHandler();
                $imageUploader->upload($ticker, $brand->getReference(), 'brands/ticker', null, null, 30);
            }

            $entityManager->persist($brand);
            $entityManager->flush();
        }

        if ($brandForm->isSubmitted() && !$brandForm->isValid()) {
            $errors = $validator->validate($brandForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/brands/adminBrandsManage.html.twig', [
            'page_name'         => 'Edit Brand',
            'brand_products'    => $brandsRepository->findBrandProducts($brandId),
            'form'              => $brandForm,
            'errors'            => $errorMessages ?? null
        ]);
    }
}
