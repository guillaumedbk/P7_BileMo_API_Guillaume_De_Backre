<?php

namespace App\Validator\Asserts;

use App\Validator\Validators\EmailUniquenessValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;

/**
 * @Annotation
 */
#[\Attribute]
class EmailUniqueness extends Constraint
{
    public string $message = 'Email already exists';
    public int $limit;

    #[HasNamedArguments]
    public function __construct(int $limit, array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);
        $this->limit = $limit;
    }

    public function validatedBy()
    {
        return EmailUniquenessValidator::class;
    }
}
