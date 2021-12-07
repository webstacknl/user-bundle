<?php

namespace Webstack\UserBundle\Form\Factory;

use Symfony\Component\Form\FormInterface;

interface FactoryInterface
{
    public function createForm(): FormInterface;
}
