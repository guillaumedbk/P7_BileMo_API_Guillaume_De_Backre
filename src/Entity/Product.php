<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @Hateoas\Relation (
 *     "self",
 *     href = @Hateoas\Route(
 *          "app_product_detail",
 *          parameters = { "slug" = "expr(object.getSlug())" }
 *     ),
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "app_add_product",
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getProduct")
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "app_modify_product",
 *          parameters = {
 *              "slug" = "expr(object.getSlug())"
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getCustomer")
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_delete_product",
 *          parameters = {
 *              "slug" = "expr(object.getSlug())"
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getProduct")
 * )
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getProduct"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProduct"])]
    private string $brand;

    #[ORM\Column(length: 255)]
    #[Groups(["getProduct"])]
    private string $model;

    #[ORM\Column]
    #[Groups(["getProduct"])]
    private int $price;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    public function __construct(string $brand, string $model, int $price)
    {
        $this->brand = $brand;
        $this->model = $model;
        $this->price = $price;
        $this->slug = Uuid::v1();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }


}
