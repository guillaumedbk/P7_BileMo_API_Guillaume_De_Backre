<?php

namespace App\Controller;

use App\DTO\CustomerDTO;
use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CustomerController extends AbstractController
{
    #[Route('/api/{id}/customers', name: 'app_user_customers')]
    public function getAllCustomers(User $user, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customers = $customerRepository->findBy(['user' => $user]);
        $context = SerializationContext::create()->setGroups(["getCustomer"]);
        $jsonCustomers = $serializer->serialize($customers, 'json', $context);

        return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
    }


    #[Route('/api/customer/{identifier}', name: 'app_customer_detail', methods: ['GET'])]
    public function getCustomerDetail(Customer $customer, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customer = $customerRepository->findBy(['identifier' => $customer->getIdentifier()]);
        $context = SerializationContext::create()->setGroups(["getCustomer"]);
        $jsonCustomers = $serializer->serialize($customer, 'json', $context);

        return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/{id}/customer/add', name: 'app_add_customer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un nouveau client !')]
    public function addCustomer(string $id, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, CustomerRepository $customerRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        //Retrieve data
        $payload = $serializer->deserialize($request->getContent(), CustomerDTO::class, 'json');
        //Related User
        $user = $userRepository->find($id);
        //Data validation
        $validation = $validator->validate($payload);
        $checkError = $validation->count();
        if (!empty($checkError)) {
            return new JsonResponse($serializer->serialize($validation, 'json'), Response::HTTP_BAD_GATEWAY, [], 'json');
        }
        //Check if mail already exist
        $mailExist = $customerRepository->findBy(['email' => $payload->email]);
        if ($mailExist) {
            return new JsonResponse('Mail already exist', Response::HTTP_BAD_GATEWAY, [], 'json');
        }
        //New user
        $newCustomer = new Customer($payload->firstname, $payload->lastname, $payload->email, $payload->password);
        //Set hashed password
        $newCustomer->setPassword($userPasswordHasher->hashPassword($newCustomer, $payload->password));
        //Set User
        $newCustomer->setUser($user);
        //Save in db
        $entityManager->persist($newCustomer);
        $entityManager->flush();

        //Serialize
        $context = SerializationContext::create()->setGroups(["getCustomer"]);
        $newCustomer = $serializer->serialize($newCustomer, 'json', $context);
        //Send Response
        return new JsonResponse($newCustomer, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/customer/{identifier}', name: 'app_delete_customer', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
