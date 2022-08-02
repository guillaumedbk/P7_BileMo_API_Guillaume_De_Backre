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
}
