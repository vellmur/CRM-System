<?php

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateType as BaseDate;

class DateType extends BaseDate
{
    public function __construct()
    {
        /**
         * This trick is required to cover date validation with changed locale.
         * For example: Russian locale expects russian names of months and error will happen if months not translated.
         * For better fix, need to add months translation for a date.
         */
        \Locale::setDefault('en');
    }
}
