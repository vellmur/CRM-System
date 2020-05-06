<?php

namespace App\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * Class EmailOrPhoneRequired
 * @package App\Validator\Constraints
 * @Annotation
 * @Target({"CLASS"})
 */
class EmailOrPhoneRequired extends Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}