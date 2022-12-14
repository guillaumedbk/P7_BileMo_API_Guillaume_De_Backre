<?php

namespace App\Controller;

use App\DTO\CustomerDTO;
use App\DTO\PutCustomerDTO;
use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
    public function getAllCustomers(User $user, Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache, $limitPerPage): JsonResponse
    {
        $this->denyAccessUnlessGranted('GET', $user);
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
     * @param User $user
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @OA\Tag(name="Customer")
     */
    #[Route('/api/users/{user_id}/customers/{id}', name: 'app_customer_detail', methods: ['GET'])]
    #[ParamConverter('customer', options: ['mapping' => ['id' => 'id', 'user_id' => 'user']])]
    #[ParamConverter('user', options: ['mapping' => ['user_id' => 'id']])]
    public function getCustomerDetail(Customer $customer, User $user, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $this->denyAccessUnlessGranted('GET', $user);
        $context = SerializationContext::create()->setGroups(["getCustomer"]);
        //CACHE MANAGEMENT
        $idCache = "getCustomerDetail-" . $customer->getId();
        $jsonCustomer = $cache->get($idCache, function (ItemInterface $item) use ($customer, $context, $serializer) {
            $item->tag("customerCache");
            return $serializer->serialize($customer, 'json', $context);
        });

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }


    /**
     * Cette méthode permet d'ajouter un nouveau client lié à un utilisateur
     * @param User $user
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @OA\Tag(name="Customer")
     */
    #[Route('/api/users/{id}/customers', name: 'app_add_customer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un nouveau client !')]
    public function addCustomer(User $user, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        //Retrieve data
        $payload = $serializer->deserialize($request->getContent(), CustomerDTO::class, 'json');
        //Data validation
        $validation = $validator->validate($payload);
        $checkError = $validation->count();
        if (!empty($checkError)) {
            return new JsonResponse($serializer->serialize($validation, 'json'), Response::HTTP_UNPROCESSABLE_ENTITY, [], 'json');
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

    #[Route('/api/users/{user_id}/customers/{id}', name: 'app_modify_customer', methods: ['PUT'])]
    #[ParamConverter('customer', options: ['mapping' => ['id' => 'id', 'user_id' => 'user']])]
    #[ParamConverter('user', options: ['mapping' => ['user_id' => 'id']])]
    public function modifyCustomer(User $user, Customer $customer, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('PUT', $user);
        $this->denyAccessUnlessGranted('PUT', $customer);
        //Retrieve User
        $payload = $serializer->deserialize($request->getContent(), PutCustomerDTO::class, 'json');
        //Data validation
        $validation = $validator->validate($payload);
        $checkError = $validation->count();
        if (!empty($checkError)) {
            return new JsonResponse($serializer->serialize($validation, 'json'), Response::HTTP_UNPROCESSABLE_ENTITY, [], 'json');
        }
        //Update Data
        $customer->setFirstname($payload->firstname);
        $customer->setLastname($payload->lastname);
        $customer->setEmail($payload->email);
        $customer->setPassword($payload->password);
        $customer->setUser($user);
        //Save in db
        $entityManager->persist($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

    }

    /**
     * Cette méthode permet de supprimer un client lié à un utilisateur
     * @param Customer $customer
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * @OA\Tag (name="Customer")
     */
    #[Route('/api/users/{user_id}/customers/{id}', name: 'app_delete_customer', methods: ['DELETE'])]
    #[ParamConverter('customer', options: ['mapping' => ['id' => 'id', 'user_id' => 'user']])]
    #[ParamConverter('user', options: ['mapping' => ['user_id' => 'id']])]
    public function deleteCustomer(Customer $customer, User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('DELETE', $user);
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
