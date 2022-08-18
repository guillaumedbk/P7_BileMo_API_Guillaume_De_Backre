<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation (
 *     "self",
 *     href = @Hateoas\Route(
 *          "app_customer_detail",
 *          parameters = {
 *              "user_id" = "expr(object.getUser().getId())",
 *              "id" = "expr(object.getIdentifier())"
 *          }
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
 *          parameters = {
 *              "user_id" = "expr(object.getUser().getId())",
 *              "id" = "expr(object.getIdentifier())"
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getCustomer")
 * )
 *
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id; //UUID

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
    private string $password;

    #[ORM\ManyToOne(inversedBy: 'customer')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(string $firstname, string $lastname, string $email, string $password)
    {
        $this->id = Uuid::v1();
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId(): string
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

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
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
        return $this->id;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials()
    {

    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
}
