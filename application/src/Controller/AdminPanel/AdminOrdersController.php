<?php

namespace App\Controller\AdminPanel;

use App\Form\FormManageOrderStatusType;
use App\Form\FormOrderCustomerManageType;
use App\Repository\CustomersRepository;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminOrdersController extends AbstractController
{
    /**
     * Orders main page
     *
     * @param OrdersRepository $ordersRepository
     *
     * @return Response
     *
     * #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_ORDERS")'))] example
     */
    public function orders(OrdersRepository $ordersRepository): Response
    {
        $listOfOrders           = $ordersRepository->getOrdersForPage();
        $ordersnotDownloaded    = $ordersRepository->getOrderCountNotProcessed();

        return $this->render('adminPanel/orders/adminOrders.html.twig', [
            'page_name'              => 'Orders',
            'orders'                 => $listOfOrders,
            'orders_not_downloaded'  => $ordersnotDownloaded
        ]);
    }

    /**
     * AJAX search order bar functionality
     *
     * @param Request $request
     * @param OrdersRepository $ordersRepository
     *
     * @return JsonResponse
     */
    public function ajaxOrdersSearch(Request $request, OrdersRepository $ordersRepository): JsonResponse
    {
        $searchTerm = (string) $request->query->get('term');
        $results    = $ordersRepository->getAjaxSearchOrders($searchTerm);

        foreach ($results as $result) {
            $url = $this->generateUrl('orderDetails', [
                'orderId' => $result->getId(),
            ]);

            $object = new stdClass();
            $object->id = $result->getId();
            $object->url = $url;
            $object->value = sprintf(
                '%s - %s %s',
                'DHS' . $result->getId(),
                $result->getBillingForename(),
                $result->getBillingSurname()
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
     * Return list of searched orders
     *
     * @param Request $request
     * @param OrdersRepository $ordersRepository
     *
     * @return Response
     */
    public function ordersSearch(Request $request, OrdersRepository $ordersRepository): Response
    {
        $searchTerm      = (string) $request->query->get('q');
        $pageNumber      = (int) $request->query->get('page');
        $column          = (string) $request->query->get('column');
        $sortCriteria    = (string) $request->query->get('sort');

        $pageNumber      = $pageNumber == 0 ? 1 : $pageNumber;

        $orders          = $ordersRepository->getSearchedOrders($searchTerm, $column, $sortCriteria, $pageNumber);
        $ordersCount     = $ordersRepository->getSearchedOrdersCount($searchTerm);

        return $this->render('adminPanel/orders/adminOrdersSearch.html.twig', [
            'page_name'         => 'Search Orders',
            'orders'            => $orders,
            'orders_count'      => $ordersCount,
            'search_term'       => $searchTerm,
            'page_number'       => $pageNumber,
            'number_of_pages'   => floor(($ordersCount / 25) + 1),
            'column'            => $column,
            'sort_criteria'     => $sortCriteria
        ]);
    }

    /**
     * View Order Details
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer $orderId
     * @param OrdersRepository $ordersRepository
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function orderDetails(
        EntityManagerInterface $entityManager,
        Request $request,
        int $orderId,
        OrdersRepository $ordersRepository,
        ValidatorInterface $validator
    ): Response {
        $order           = $ordersRepository->find($orderId);
        $order ?? throw $this->createNotFoundException('The order does not exist');

        $formOrderStatus = $this->createForm(FormManageOrderStatusType::class, $order);

        $formOrderStatus->handleRequest($request);
        if ($formOrderStatus->isSubmitted() && $formOrderStatus->isValid()) {
            $orderStatus = $formOrderStatus->getData();

            $entityManager->persist($orderStatus);
            $entityManager->flush();
        }

        if ($formOrderStatus->isSubmitted() && !$formOrderStatus->isValid()) {
            $errors = $validator->validate($formOrderStatus->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/orders/adminOrderDetails.html.twig', [
            'page_name'         => sprintf('View Order: %s%u', 'DHS', $orderId),
            'order'             => $order,
            'form_order_status' => $formOrderStatus,
            'errors'            => $errorMessages ?? null
        ]);
    }

    /**
     * Manage order customer details
     *
     * @param integer $orderId
     * @param EntityManagerInterface $entityManager
     * @param CustomersRepository $customersRepository
     * @param OrdersRepository $ordersRepository
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function orderCustomerManage(
        int $orderId,
        EntityManagerInterface $entityManager,
        CustomersRepository $customersRepository,
        OrdersRepository $ordersRepository,
        ValidatorInterface $validator
    ): Response {
        $order = $ordersRepository->find($orderId);
        $customer = $customersRepository->find($order->getCustomer()->getId());

        $order ?? throw $this->createNotFoundException('The order does not exist');
        $customer ?? throw $this->createNotFoundException('The order customer does not exist');

        $formOrderCustomerManageType = $this->createForm(FormOrderCustomerManageType::class, $order);
        if ($formOrderCustomerManageType->isSubmitted() && $formOrderCustomerManageType->isValid()) {
            $orderCustomerDetails = $formOrderCustomerManageType->getData();

            $entityManager->persist($orderCustomerDetails);
            $entityManager->flush();
        }

        if ($formOrderCustomerManageType->isSubmitted() && !$formOrderCustomerManageType->isValid()) {
            $errors = $validator->validate($formOrderCustomerManageType->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/orders/adminOrderCustomerManage.html.twig', [
            'page_name'                         => sprintf('Edit Order Customer For %s%u', 'DHS', $orderId),
            'order_id'                          => $orderId,
            'orders'                            => $customer->getOrders(),
            'customer'                          => $customer,
            'form_order_customer_manage_type'   => $formOrderCustomerManageType,
            'errors'                            => $errorMessages ?? null
        ]);
    }
}
