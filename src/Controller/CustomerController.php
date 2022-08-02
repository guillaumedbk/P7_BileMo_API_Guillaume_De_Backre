<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    #[Route('{id}/customers', name: 'app_user_customers', methods:['GET'])]
    public function getAllCustomers(User $user, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customers = $customerRepository->findBy(['user' => $user]);
        $jsonCustomers = $serializer->serialize($customers, 'json');

        return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
    }

    #[Route('/customer/{identifier}', name: 'app_customer_detail', methods: ['GET'])]
    public function getCustomerDetail(Customer $customer, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customer = $customerRepository->findBy(['identifier' => $customer->getIdentifier()]);
        $jsonCustomers = $serializer->serialize($customer, 'json');

        return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
    }
}
