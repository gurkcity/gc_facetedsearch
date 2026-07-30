<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Constraint;

use PrestaShop\PrestaShop\Adapter\Tools;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class UrlSegmentValidator responsible for validating an URL segment.
 */
class UrlSegmentValidator extends ConstraintValidator
{
    /**
     * @var Tools
     */
    private $tools;

    /**
     * @param Tools $tools
     */
    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UrlSegment) {
            throw new UnexpectedTypeException($constraint, UrlSegment::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (strtolower($value) !== $this->tools->linkRewrite($value)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain('Admin.Notifications.Error')
                ->setParameter('%s', $this->formatValue($value))
                ->addViolation()
            ;
        }
    }
}
