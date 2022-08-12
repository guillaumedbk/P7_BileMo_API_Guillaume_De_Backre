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
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class CustomerController extends AbstractController
{
    /**
     * Cette méthode retourne l'ensemble des clients liés à un utilisateur
     * @param Request $request
     * @param User $user
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @param $limitPerPage
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @OA\Tag(name="Customer")
     * * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     */
    #[Route('/api/users/{id}/customers', name: 'app_user_customers', methods: ['GET'])]
    public function getAllCustomers(Request $request, User $user, CustomerRepository $customerRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache, $limitPerPage): JsonResponse
    {
        $page = $request->get('page', 1);
        $context = SerializationContext::create()->setGroups(["getCustomer"]);

        //CACHE MANAGEMENT
        $idCache = "getAllCustomers-" . $page;
        $jsonCustomers = $cache->get($idCache, function (ItemInterface $item) use ($customerRepository, $page, $user, $limitPerPage, $context, $serializer) {
            $item->tag("customersCache");
            $customerList = $customerRepository->retrieveWithPagination($user, $page, $limitPerPage);

            return $serializer->serialize($customerList, 'json', $context);
        });

        return new JsonResponse($jsonCustomers, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer le détail d'un client en particulier
     * @param Customer $customer
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @OA\Tag(name="Customer")
     */
    #[Route('/api/users/{id}/customers/{identifier}', name: 'app_customer_detail', methods: ['GET'])]
    public function getCustomerDetail(Customer $customer, CustomerRepository $customerRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getCustomer"]);
        $identifier = $customer->getIdentifier();
        //CACHE MANAGEMENT
        $idCache = "getCustomerDetail-" . $identifier;
        $jsonCustomer = $cache->get($idCache, function (ItemInterface $item) use ($customer, $customerRepository, $context, $serializer) {
            $item->tag("customerCache");
            $customer = $customerRepository->findBy(['identifier' => $customer->getIdentifier()]);

            return $serializer->serialize($customer, 'json', $context);
        });

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }


    /**
     * Cette méthode permet d'ajouter un nouveau client lié à un utilisateur
     * @param string $id
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param CustomerRepository $customerRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @OA\Tag(name="Customer")
     */
    #[Route('/api/users/{id}/customers', name: 'app_add_customer', methods: ['POST'])]
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

    /**
     * Cette méthode permet de supprimer un client lié à un utilisateur
     * @param Customer $customer
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * @OA\Tag (name="Customer")
     */
    #[Route('/api/users/{id}/customers/{identifier}', name: 'app_delete_customer', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
