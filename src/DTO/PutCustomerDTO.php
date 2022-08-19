<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Asserts as MyConstraint;

class PutCustomerDTO
{
    #[Assert\NotBlank]
    public string $firstname;

    #[Assert\NotBlank]
    public string $lastname;

    #[Assert\NotBlank]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[MyConstraint\EmailUniqueness(limit: 1)]
    public string $email;

    #[Assert\NotBlank]
    public string $password;
}
