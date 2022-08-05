<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class ProductController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des produits
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @OA\Tag(name="Products")
     */
    #[Route('/api/products', name: 'app_products', methods: ['GET'])]
    public function getAllProducts(Request $request, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $this->getParameter('app.limit_per_page_param');
        $offset = (($page * $limit)-$page);
        $products = $productRepository->findBy([],[], $limit, $offset);
        $jsonProducts = $serializer->serialize($products, 'json');

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer un produit en particulier
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @OA\Tag(name="Products")
     */
    #[Route('/api/product/{slug}', name: 'app_product_detail', methods: ['GET'])]
    public function getProductDetail(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
