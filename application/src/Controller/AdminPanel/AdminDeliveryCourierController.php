<?php

namespace App\Controller\AdminPanel;

use App\Entity\DeliveryCouriers;
use App\Form\FormDeliveryCouriersManageType;
use App\Repository\DeliveryCouriersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminDeliveryCourierController extends AbstractController
{
    /**
     * Show all Couriers
     *
     * @param DeliveryCouriersRepository $deliveryCouriersRepository
     *
     * @return Response
     */
    public function deliveryCouriers(DeliveryCouriersRepository $deliveryCouriersRepository): Response
    {
        return $this->render('adminPanel/couriers/adminCouriers.html.twig', [
            'page_name' => 'Delivery Couriers',
            'delivery_couriers' => $deliveryCouriersRepository->findAll()
        ]);
    }

    /**
     * Courier Delete
     *
     * @param integer $courierId
     * @param DeliveryCouriersRepository $deliveryCouriersRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function deliveryCouriersDelete(
        int $courierId,
        DeliveryCouriersRepository $deliveryCouriersRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($deliveryCouriersRepository->find($courierId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'deliveryCouriers'
        );
    }

    /**
     * Edit or Add Delivery Courier
     *
     * @param DeliveryCouriersRepository $deliveryCouriersRepositoryy
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer|null $courierId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function deliveryCouriersManage(
        DeliveryCouriersRepository $deliveryCouriersRepositoryy,
        EntityManagerInterface $entityManager,
        Request $request,
        int $courierId = null,
        ValidatorInterface $validator
    ): Response {
        if ($courierId) {
            $deliveryCouriersRepositoryy->find($courierId)
            ?? throw $this->createNotFoundException('The Delivery Courier does not exist');
        }

        $courier = $courierId ? $deliveryCouriersRepositoryy->find($courierId) : new DeliveryCouriers();

        $form = $this->createForm(FormDeliveryCouriersManageType::class, $courier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute(
                'deliveryCouriers'
            );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $validator->validate($form->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/couriers/adminCouriersManage.html.twig', [
            'page_name' => 'Delivery Couriers Manage',
            'form'      => $form,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
