<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        return new JsonResponse([
            'products' => $products
        ]);
    }
}
