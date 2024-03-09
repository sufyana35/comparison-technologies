<?php

namespace App\Controller\AdminPanel;

use App\Entity\Addresses;
use App\Form\FormManageAddressesType;
use App\Form\FormManageCustomersType;
use App\Repository\AddressesRepository;
use App\Repository\CustomersRepository;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminCustomersController extends AbstractController
{
    /**
     * Customer main page and show 250 latest customers
     *
     * @param CustomersRepository $customersRepository
     *
     * @return Response
     */
    public function customers(CustomersRepository $customersRepository): Response
    {
        $customers = $customersRepository->findBy(array(), array('id' => Criteria::DESC), 500);

        return $this->render('adminPanel/customers/adminCustomers.html.twig', [
            'page_name'  => 'Customers',
            'customers'     => $customers
        ]);
    }

    /**
     * Customer delete
     *
     * @param integer $customerId
     * @param CustomersRepository $customersRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function customersDelete(
        int $customerId,
        CustomersRepository $customersRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($customersRepository->find($customerId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'customers'
        );
    }

    /**
     * AJAX search customer bar functionality
     *
     * @param Request $request
     * @param CustomersRepository $customersRepository
     *
     * @return JsonResponse
     */
    public function ajaxCustomersSearch(Request $request, CustomersRepository $customersRepository): JsonResponse
    {
        $searchTerm  = (string) $request->query->get('term');
        $customers    = $customersRepository->getAjaxProductsSearch($searchTerm);

        foreach ($customers as $customer) {
            $url = $this->generateUrl('customersManage', [
                'customerId' => $customer->getId(),
            ]);

            $object = new stdClass();
            $object->id = $customer->getId();
            $object->url = $url;
            $object->value = sprintf(
                'Account:%s, Phone:%s, Username:%s, Email %s',
                $customer->getAccount(),
                $customer->getPhone(),
                $customer->getUsername(),
                $customer->getEmail()
            );
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
     * Manage Customer
     *
     * @param integer $customerId
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param CustomersRepository $customersRepository
     *
     * @return Response
     */
    public function customersManage(
        int $customerId,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CustomersRepository $customersRepository
    ): Response {
        $customer = $customersRepository->findCustomer($customerId);
        $customer ?? throw $this->createNotFoundException('The customer does not exist');

        $customerForm = $this->createForm(FormManageCustomersType::class, $customer);
        $customerForm->handleRequest($request);
        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            $customer = $customerForm->getData();

            $customer->setUpdatedAt(new DateTime());
            if ($customer->getPassword()) {
                $customer->setPassword($customer->getPassword());
            }

            $entityManager->persist($customer);
            $entityManager->flush();
        }

        if ($customerForm->isSubmitted() && !$customerForm->isValid()) {
            $errors = $validator->validate($customerForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/customers/adminCustomersManage.html.twig', [
            'page_name' => 'Edit Customer',
            'form'      => $customerForm,
            'errors'    => $errorMessages ?? null
        ]);
    }

    /**
     * Manage Customer Addresses
     *
     * @param integer $customerId
     * @param CustomersRepository $customersRepository
     * @param AddressesRepository $addressesRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function viewCustomerAddresses(
        int $customerId,
        CustomersRepository $customersRepository,
        AddressesRepository $addressesRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $customer = $customersRepository->findCustomer($customerId);
        $customer ?? throw $this->createNotFoundException('The customer does not exist');

        if ($request->request->get('Id')) {
            $addressId = $request->request->get('Id');
            $adresses = $addressesRepository->findBy(
                ['customer' => $customer]
            );

            if ($addressId == $customer->getBillingAddress()->getId()) {
                $error[0] = array(
                    'propertyPath' => 'Addresses',
                    'message' => 'You must have at-least one billing address'
                );
                $errorMessages = $error;
            } elseif ($addressId == $customer->getDeliveryAddress()->getId()) {
                $error[0] = array(
                    'propertyPath' => 'Addresses',
                    'message' => 'You must have at-least one delivery address'
                );
                $errorMessages = $error;
            } elseif (count($adresses) == 1) {
                $error[0] = array(
                    'propertyPath' => 'Addresses',
                    'message' => 'You must have at-least one address'
                );
                $errorMessages = $error;
            } else {
                $address = $addressesRepository->find($addressId);
                $entityManager->remove($address);
                $entityManager->flush();
            }
        }

        return $this->render('adminPanel/customers/adminCustomersAddresses.html.twig', [
            'page_name'     => 'View Customer Addresses',
            'customer'      => $customer,
            'errors'        => $errorMessages ?? null
        ]);
    }

    /**
     * Manage address details and preferences
     *
     * @param integer $customerId
     * @param integer $addressId
     * @param CustomersRepository $customersRepository
     * @param AddressesRepository $addressesRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function customersManageAddress(
        int $customerId,
        int $addressId,
        CustomersRepository $customersRepository,
        AddressesRepository $addressesRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): Response {
        $address = $addressesRepository->find($addressId);
        $customer = $customersRepository->findCustomer($customerId);
        $customer ?? throw $this->createNotFoundException('The customer does not exist');
        $address ?? throw $this->createNotFoundException('The address does not exist');

        $addressForm = $this->createForm(FormManageAddressesType::class, $address);
        $addressForm->handleRequest($request);
        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $address = $addressForm->getData();

            $addressPreference = $request->request->get('address_preference');
            if (
                ($customer->getBillingAddress()->getId() == $address->getId() && $addressPreference == 0)
                && ($customer->getDeliveryAddress()->getId() == $address->getId() && $addressPreference == 0)
            ) {
                $error[0] = array(
                    'propertyPath' => 'Addresses',
                    'message' => 'You must have at-least one delivery address and billing address'
                );
                $errorMessages = $error;
            } elseif (($customer->getBillingAddress()->getId() == $address->getId() && $addressPreference == 1)) {
                $error[0] = array(
                    'propertyPath' => 'Addresses',
                    'message' => 'You must have at-least one billing address'
                );
                $errorMessages = $error;
            } elseif (($customer->getDeliveryAddress()->getId() == $address->getId() && $addressPreference == 2)) {
                $error[0] = array(
                    'propertyPath' => 'Addresses',
                    'message' => 'You must have at-least one delivery address'
                );
                $errorMessages = $error;
            } else {
                $address->setUpdatedAt(new DateTime());

                if ($addressPreference == 1) {
                    $customer->setUpdatedAt(new DateTime());
                    $customer->setBillingAddress($address);
                    $entityManager->persist($customer);
                }
                if ($addressPreference == 2) {
                    $customer->setUpdatedAt(new DateTime());
                    $customer->setDeliveryAddressId($address);
                    $entityManager->persist($customer);
                }
                if ($addressPreference == 3) {
                    $customer->setUpdatedAt(new DateTime());
                    $customer->setBillingAddress($address);
                    $customer->setDeliveryAddressId($address);
                    $entityManager->persist($customer);
                }

                $entityManager->persist($address);
                $entityManager->flush();
            }
        }

        if ($addressForm->isSubmitted() && !$addressForm->isValid()) {
            $errors = $validator->validate($addressForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/customers/adminCustomersManageAddress.html.twig', [
            'page_name'     => 'Manage Address',
            'customer'      => $customer,
            'address_form'  => $addressForm,
            'errors'        => $errorMessages ?? null
        ]);
    }

    /**
     * Manage address details and preferences
     *
     * @param integer $customerId
     * @param CustomersRepository $customersRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function customersAddAddress(
        int $customerId,
        CustomersRepository $customersRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): Response {
        $customer = $customersRepository->findCustomer($customerId);
        $customer ?? throw $this->createNotFoundException('The customer does not exist');

        $addressForm = $this->createForm(FormManageAddressesType::class, new Addresses());
        $addressForm->handleRequest($request);
        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $address = $addressForm->getData();

            $addressPreference = $request->request->get('address_preference');

            $address->setUpdatedAt(new DateTime());
            $address->setCustomer($customer);

            $entityManager->persist($address);
            $entityManager->flush();

            if ($addressPreference == 1) {
                $customer->setUpdatedAt(new DateTime());
                $customer->setBillingAddress($address);
                $entityManager->persist($customer);
                $entityManager->flush();
            }
            if ($addressPreference == 2) {
                $customer->setUpdatedAt(new DateTime());
                $customer->setDeliveryAddressId($address);
                $entityManager->persist($customer);
                $entityManager->flush();
            }
            if ($addressPreference == 3) {
                $customer->setUpdatedAt(new DateTime());
                $customer->setBillingAddress($address);
                $customer->setDeliveryAddressId($address);
                $entityManager->persist($customer);
                $entityManager->flush();
            }
        }

        if ($addressForm->isSubmitted() && !$addressForm->isValid()) {
            $errors = $validator->validate($addressForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/customers/adminCustomersManageAddress.html.twig', [
            'page_name'     => 'Manage Address',
            'customer'      => $customer,
            'address_form'  => $addressForm,
            'errors'        => $errorMessages ?? null
        ]);
    }
}
