<?php

namespace Webstack\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webstack\UserBundle\Manager\UserManager;

class ResetPasswordType extends AbstractType
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly string $userClass,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class, [
            'label' => 'Wachtwoord',
            'type' => PasswordType::class,
            'options' => [
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ],
            'constraints' => $this->userManager->getPasswordConstraints(),
            'first_options' => [
                'label' => 'Wachtwoord',
            ],
            'second_options' => [
                'label' => 'Wachtwoord herhalen',
            ],
            'invalid_message' => 'Ingevoerde wachtwoorden komen niet overeen',
        ])
            ->add('submit', SubmitType::class, [
                'label' => 'Opslaan',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->userClass,
            'csrf_token_id' => 'resetting',
        ]);
    }
}
