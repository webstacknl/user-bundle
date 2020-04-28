<?php

namespace Webstack\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

/**
 * Class ResetPasswordType
 */
class ResetPasswordType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * ResetPasswordType constructor.
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
        $builder->add('plainPassword', RepeatedType::class, array(
            'label' => 'Wachtwoord',
            'type' => PasswordType::class,
            'options' => array(
                'attr' => array(
                    'autocomplete' => 'new-password',
                ),
            ),
            'first_options' => [
                'label' => 'form.new_password',
                'attr' => [
                    'placeholder' => 'Wachtwoord',
                ]
            ],
            'second_options' => [
                'label' => 'form.new_password_confirmation',
                'attr' => [
                    'placeholder' => 'Herhaal wachtwoord'
                ]
            ],
            'invalid_message' => 'Ingevoerde wachtwoorden komen niet overeen',
        ));
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
        return 'webstack_user_registration_reset_password';
    }
}
