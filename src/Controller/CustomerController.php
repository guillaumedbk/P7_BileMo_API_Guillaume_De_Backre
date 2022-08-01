<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    #[Route('{id}/customers', name: 'app_user_customers')]
    public function getAllCustomers(User $user, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customers = $customerRepository->findBy(['user' => $user]);
        $jsonCustomers = $serializer->serialize($customers, 'json');

        return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
    }

    #[Route('/customer/{identifier}', name: 'app_customer_detail')]
    public function getCustomerDetail(string $identifier, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customer = $customerRepository->findBy(['identifier' => $identifier]);
        if ($customer) {
            $jsonCustomers = $serializer->serialize($customer, 'json');
            return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);

    }
}
