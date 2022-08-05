<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
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
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @OA\Tag(name="Products")
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     */
    #[Route('/api/products', name: 'app_products', methods: ['GET'])]
    public function getAllProducts(Request $request, ProductRepository $productRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        //RETRIEVE PRODUCTS WITH PAGINATION SYSTEM
        $page = $request->get('page', 1);
        $limit = $this->getParameter('app.limit_per_page_param');
        $offset = (($page * $limit)-$page);

        //CACHE MANAGEMENT
        $idCache = "getAllProducts-" . $page;
        $jsonProducts = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit, $offset, $serializer) {
            $item->tag("productCache");
            $productList = $productRepository->findBy([],[], $limit, $offset);

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
    #[Route('/api/product/{slug}', name: 'app_product_detail', methods: ['GET'])]
    public function getProductDetail(Product $product, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        //CACHE MANAGEMENT
        $idCache = "getProductDetail-";
        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($product, $serializer) {
            $item->tag("productDetailCache");

            return $serializer->serialize($product, 'json');
        });

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
