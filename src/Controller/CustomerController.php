<?php

namespace App\Controller;

use App\DTO\CustomerDTO;
use App\Repository\CustomerRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    #[Route('{userId}/customers', name: 'app_user_customers', methods: ['GET'])]
    public function getAllCustomers(string $userId, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customers = $customerRepository->findBy(['user' => $user]);
        $jsonCustomers = $serializer->serialize($customers, 'json');

        return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
    }

    #[Route('/customer/{identifier}', name: 'app_customer_detail', methods: ['GET'])]
    public function getCustomerDetail(string $identifier, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customer = $customerRepository->findBy(['identifier' => $identifier]);
        if ($customer) {
            $jsonCustomers = $serializer->serialize($customer, 'json');
            return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
    }

    #[Route('/customer/add', name: 'app_add_customer', methods: ['POST'])]
    public function addCustomer(Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer)//: JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), CustomerDTO::class, 'json');

        $customer = $serializer->serialize($customer, 'json');

        return new JsonResponse($customer, Response::HTTP_CREATED, [], true);

    }
}
