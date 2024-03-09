<?php

namespace App\Controller\AdminPanel;

use App\Entity\Countries;
use App\Form\FormCountriesManageType;
use App\Repository\CountriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminCountriesController extends AbstractController
{
    /**
     * Show all countries
     *
     * @param CountriesRepository $countriesRepository
     *
     * @return Response
     */
    public function countries(CountriesRepository $countriesRepository): Response
    {
        return $this->render('adminPanel/countries/adminCountries.html.twig', [
            'page_name' => 'Delivery Countries',
            'countries' => $countriesRepository->findAll()
        ]);
    }

    /**
     * Manage Countries
     *
     * @param CountriesRepository $countriesRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer|null $countryId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function countriesManage(
        CountriesRepository $countriesRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $countryId = null,
        ValidatorInterface $validator
    ): Response {
        if ($countryId) {
            $countriesRepository->find($countryId)
            ?? throw $this->createNotFoundException('The Country does not exist');
        }

        $country = $countryId ? $countriesRepository->find($countryId) : new Countries();

        $countriesForm = $this->createForm(FormCountriesManageType::class, $country);
        $countriesForm->handleRequest($request);
        if ($countriesForm->isSubmitted() && $countriesForm->isValid()) {
            $countryData = $countriesForm->getData();

            $entityManager->persist($countryData);
            $entityManager->flush();

            return $this->redirectToRoute(
                'countries'
            );
        }

        if ($countriesForm->isSubmitted() && !$countriesForm->isValid()) {
            $errors = $validator->validate($countriesForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/countries/adminCountriesManage.html.twig', [
            'page_name' => 'Countries Manage',
            'form'      => $countriesForm,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
