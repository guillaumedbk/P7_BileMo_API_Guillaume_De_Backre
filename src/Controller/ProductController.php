<?php

namespace App\Controller;

use App\DTO\ProductDTO;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des produits
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @param $limitPerPage
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @OA\Tag(name="Products")
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     */
    #[Route('/api/products', name: 'app_products', methods: ['GET'])]
    public function getAllProducts(Request $request, ProductRepository $productRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache, $limitPerPage): JsonResponse
    {
        //RETRIEVE PRODUCTS WITH PAGINATION SYSTEM
        $page = $request->get('page', 1);

        //CACHE MANAGEMENT
        $idCache = "getAllProducts-" . $page;
        $jsonProducts = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limitPerPage, $serializer) {
            $item->tag("productCache");
            $productList = $productRepository->retrieveWithPagination($page, $limitPerPage);

            return $serializer->serialize($productList, 'json');
        });

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer un produit en particulier
     * @param Product $product
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @OA\Tag(name="Products")
     */
    #[Route('/api/products/{slug}', name: 'app_product_detail', methods: ['GET'])]
    public function getProductDetail(Product $product, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $slug = $product->getSlug();
        //CACHE MANAGEMENT
        $idCache = "getProductDetail-" . $slug;
        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($product, $serializer) {
            $item->tag("productDetailCache");

            return $serializer->serialize($product, 'json');
        });

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    #[Route('api/products', name: 'app_add_product', methods: ['POST'])]
    public function addProduct(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        //Retrieve data
        $payload = $serializer->deserialize($request->getContent(), ProductDTO::class, 'json');
        //Data validation
        $validation = $validator->validate($payload);
        $checkError = $validation->count();
        if (!empty($checkError)) {
            return new JsonResponse($serializer->serialize($validation, 'json'), Response::HTTP_UNPROCESSABLE_ENTITY, [], 'json');
        }
        //New product
        $newProduct = new Product($payload->brand, $payload->model, $payload->price);
        //Save in db
        $entityManager->persist($newProduct);
        $entityManager->flush();
        //Serialize
        $context = SerializationContext::create()->setGroups(["getProduct"]);
        $newProduct = $serializer->serialize($newProduct, 'json', $context);
        //Send Response
        return new JsonResponse($newProduct, Response::HTTP_CREATED, [], true);
    }
    #[Route('api/products/{slug}', name: 'app_modify_product', methods: ['PUT'])]
    public function modifyProduct(Product $product, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        //Retrieve Product
        $payload = $serializer->deserialize($request->getContent(), ProductDTO::class, 'json');
        //Data validation
        $validation = $validator->validate($payload);
        $checkError = $validation->count();
        if (!empty($checkError)) {
            return new JsonResponse($serializer->serialize($validation, 'json'), Response::HTTP_UNPROCESSABLE_ENTITY, [], 'json');
        }
        //Update Data
        $product->setBrand($payload->brand);
        $product->setModel($payload->model);
        $product->setPrice($payload->price);
        //Save in db
        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/products/{slug}', name: 'app_delete_customer', methods: ['DELETE'])]
    public function deleteCustomer(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
