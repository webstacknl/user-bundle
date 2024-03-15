<?php

namespace Webstack\UserBundle\Form\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

readonly class FormFactory implements FactoryInterface
{
    /**
     * @param class-string<FormTypeInterface> $type
     */
    public function __construct(
        private FormFactoryInterface $formFactory,
        private string $name,
        private string $type,
        private array $validationGroups = [],
    ) {
    }

    public function createForm(array $options = []): FormInterface
    {
        $options = array_merge([
            'validation_groups' => $this->validationGroups,
        ], $options);

        return $this->formFactory->createNamed($this->name, $this->type, null, $options);
    }
}
