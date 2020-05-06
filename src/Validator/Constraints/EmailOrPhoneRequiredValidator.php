<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EmailOrPhoneRequiredValidator
 * @package App\Validator\Constraints
 */
class EmailOrPhoneRequiredValidator extends ConstraintValidator
{
    /**
     * @param object $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity->getEmail() && !$entity->getPhone()) {
            $this->context->buildViolation('validation.form.required')
                ->setTranslationDomain('validators')
                ->atPath('email')
                ->addViolation();

            $this->context->buildViolation('validation.form.required')
                ->setTranslationDomain('validators')
                ->atPath('phone')
                ->addViolation();
        }
    }
}