<?php

namespace Webstack\UserBundle\Form\Type;

use DomainException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use Webstack\UserBundle\Manager\UserManager;

class ChangePasswordType extends AbstractType
{
    private Security $security;
    private UserManager $userManager;

    public function __construct(Security $security, UserManager $userManager)
    {
        $this->security = $security;
        $this->userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('current_password', PasswordType::class, [
                'label' => 'Huidig wachtwoord',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Er is geen huidig wachtwoord ingevuld.',
                    ]),
                    new UserPassword([
                        'message' => 'Uw huidig wachtwoord is niet juist.',
                    ]),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'constraints' => $this->userManager->getPasswordConstraints(),
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'label' => 'Nieuw wachtwoord',
                ],
                'second_options' => [
                    'label' => 'Nieuw wachtwoord herhalen',
                ],
                'invalid_message' => 'De ingevoerde wachtwoorden komen niet overeen.',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Wachtwoord wijzigen',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $user = $this->security->getUser();

        if (null === $user) {
            throw new DomainException();
        }

        $resolver->setDefaults([
            'data_class' => get_class($user),
        ]);
    }
}
