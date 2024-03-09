<?php

namespace App\Controller\AdminPanel;

use App\Form\FormSettingsManageType;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminSettingsController extends AbstractController
{
    public function settings(
        SettingsRepository $settingsRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $settings = $settingsRepository->find(1);
        $settings ?? throw $this->createNotFoundException('The settings does not exist');

        $settingsForm = $this->createForm(FormSettingsManageType::class, $settings);
        $settingsForm->handleRequest($request);
        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $settings = $settingsForm->getData();

            $entityManager->persist($settings);
            $entityManager->flush();
        }

        if ($settingsForm->isSubmitted() && !$settingsForm->isValid()) {
            $errors = $validator->validate($settingsForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/settings/adminSettingsManage.html.twig', [
            'page_name' => 'Settings',
            'form'      => $settingsForm,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
