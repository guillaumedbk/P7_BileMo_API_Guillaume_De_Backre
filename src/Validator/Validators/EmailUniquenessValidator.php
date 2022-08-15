<?php

namespace App\Validator\Validators;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class EmailUniquenessValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        $repository = $this->em->getRepository(Customer::class);
        $mailExist = $repository->findOneBy(array(
            'email' => $value
        ));
        if ($mailExist) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
