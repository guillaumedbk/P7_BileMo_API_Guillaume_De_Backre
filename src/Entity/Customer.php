<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation (
 *     "self",
 *     href = @Hateoas\Route(
 *          "app_customer_detail",
 *          parameters = { "identifier" = "expr(object.getIdentifier())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getCustomer")
 * )
 *
 *  * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "app_add_customer",
 *          parameters = { "id" = "expr(object.getUser().getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getCustomer")
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_delete_customer",
 *          parameters = { "identifier" = "expr(object.getIdentifier())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getCustomer")
 * )
 *
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCustomer"])]
    private string $firstname;

    #[ORM\Column(length: 255)]
    #[Groups(["getCustomer"])]
    private string $lastname;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(["getCustomer"])]
    private string $email;

    #[ORM\Column(length: 255)]
    #[Groups(["getCustomer"])]
    private string $password;

    #[ORM\ManyToOne(inversedBy: 'customer')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(length: 255)]
    #[Groups(["getCustomer"])]
    private string $identifier;

    public function __construct(string $firstname, string $lastname, string $email, string $password)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
        $this->identifier = $firstname . '-' . $lastname . '-' . Uuid::v1();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getUsername(): string
    {
        return $this->getIdentifier();
    }

}
