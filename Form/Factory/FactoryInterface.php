<?php

namespace Webstack\UserBundle\Form\Factory;

use Symfony\Component\Form\FormInterface;

/**
 * Interface FactoryInterface
 */
interface FactoryInterface
{
    /**
     * @return FormInterface
     */
    public function createForm(): FormInterface;
}
