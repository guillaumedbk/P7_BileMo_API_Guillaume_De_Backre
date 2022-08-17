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

    public function validatedBy()
    {
        return EmailUniquenessValidator::class;
    }
}
