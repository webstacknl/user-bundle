<?php

namespace Webstack\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ChangePasswordType
 * @package Webstack\UserBundle\Form\Type
 */
class ChangePasswordType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * UserType constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('current_password', PasswordType::class, [
                'label' => 'Huidig wachtwoord',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
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
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'label' => 'Nieuw wachtwoord'
                ],
                'second_options' => [
                    'label' => 'Nieuw wachtwoord herhalen'
                ],
                'invalid_message' => 'fos_user.password.mismatch',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Wachtwoord wijzigen',
                'attr' => [
                    'class' => 'btn btn-theme btn-block'
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => get_class($this->security->getUser())
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'webstack_user_registration_change_password';
    }
}
