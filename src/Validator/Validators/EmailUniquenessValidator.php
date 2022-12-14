<?php

namespace App\Validator\Validators;

use App\Repository\CustomerRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class EmailUniquenessValidator extends ConstraintValidator
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        $nb = $this->customerRepository->count(array('email' => $value));
        $limit = $constraint->limit;

        if ($nb > $limit) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
