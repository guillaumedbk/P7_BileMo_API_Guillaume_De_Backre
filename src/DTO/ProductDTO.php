<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    #[Assert\NotBlank]
    public string $brand;

    #[Assert\NotBlank]
    public string $model;

    #[Assert\NotBlank]
    public int $price;
}