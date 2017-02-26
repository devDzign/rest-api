<?php
/**
 * Created by PhpStorm.
 * User: mc
 * Date: 26/02/2017
 * Time: 01:21
 */

namespace Mc\ApiBundle\Form\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PriceTypeUnique extends Constraint
{
    public $message = 'A place cannot contain prices with same type';
}