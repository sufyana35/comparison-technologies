<?php

namespace App\Controller\AdminPanel;

use App\Entity\Appliances;
use App\Entity\ProductGroups;
use App\Form\FormAppliancesManageType;
use App\Repository\AppliancesRepository;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminAppliancesController extends AbstractController
{
    /**
     * Appliances homepage
     *
     * @return Response
     */
    public function appliances(): Response
    {
        return $this->render('adminPanel/appliances/adminAppliances.html.twig', [
            'page_name' => 'Appliances'
        ]);
    }

    /**
     * Appliance delete
     *
     * @param integer $applianceId
     * @param AppliancesRepository $appliancesRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function appliancesDelete(
        int $applianceId,
        AppliancesRepository $appliancesRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($appliancesRepository->find($applianceId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'appliances'
        );
    }

    /**
     * Ajax search
     *
     * @param Request $request
     * @param AppliancesRepository $appliancesRepository
     *
     * @return JsonResponse
     */
    public function ajaxAppliancesSearch(Request $request, AppliancesRepository $appliancesRepository): JsonResponse
    {
        $searchTerm  = (string) $request->query->get('term');
        $appliances  = $appliancesRepository->getAjaxAppliancesSearch($searchTerm);

        foreach ($appliances as $appliance) {
            $url = $this->generateUrl('appliancesManage', [
                'applianceId' => $appliance->getId(),
            ]);

            $object = new stdClass();
            $object->id = $appliance->getId();
            $object->url = $url;
            $object->value = $appliance->getName();

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
     * Appliances search page
     *
     * @param AppliancesRepository $appliancesRepository
     * @param Request $request
     *
     * @return Response
     */
    public function appliancesSearch(AppliancesRepository $appliancesRepository, Request $request): Response
    {
        $searchTerm     = (string) $request->query->get('q');
        $pageNumber     = (int) $request->query->get('page');
        $order          = (int) $request->query->get('order');

        $pageNumber     = $pageNumber == 0 ? 1 : $pageNumber;

        $appliances       = $appliancesRepository->getSearchedAppliances($searchTerm, $pageNumber, $order);
        $appliancesCount  = $appliancesRepository->getSearchedAppliancesCount($searchTerm);

        return $this->render('adminPanel/appliances/adminAppliancesSearch.html.twig', [
            'page_name'         => 'Search Appliances',
            'appliances'        => $appliances,
            'page_number'       => $pageNumber,
            'number_of_pages'   => floor(($appliancesCount / 20) + 1),
            'appliances_count'  => $appliancesCount,
            'search_term'       => $searchTerm,
            'order'             => $order
        ]);
    }

    /**
     * Manage appliances
     *
     * @param AppliancesRepository $appliancesRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer|null $applianceId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function appliancesManage(
        AppliancesRepository $appliancesRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $applianceId = null,
        ValidatorInterface $validator
    ): Response {
        if ($applianceId) {
            $appliancesRepository->find($applianceId)
            ?? throw $this->createNotFoundException('The Appliance does not exist');
        }

        $appliance = $applianceId ? $appliancesRepository->find($applianceId) : new Appliances();

        $form = $this->createForm(FormAppliancesManageType::class, $appliance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute(
                'appliances'
            );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $validator->validate($form->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/appliances/adminAppliancesManage.html.twig', [
            'page_name' => 'Appliances Manage',
            'form'      => $form,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
