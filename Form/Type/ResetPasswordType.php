<?php

namespace Webstack\UserBundle\Form\Type;

use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

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
     * @var string
     */
    private $userClass;

    /**
     * ResetPasswordType constructor.
     * @param Security $security
     * @param string $userClass
     */
    public function __construct(Security $security, string $userClass)
    {
        $this->security = $security;
        $this->userClass = $userClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class, array(
            'label' => 'Wachtwoord',
            'type' => PasswordType::class,
            'options' => array(
                'attr' => array(
                    'autocomplete' => 'new-password',
                ),
            ),
            'constraints' => [
                new NotCompromisedPassword([
                    'message' => 'Het ingevulde wachtwoord kan niet worden gebruikt omdat deze voorkomt op een lijst met gelekte wachtwoorden.',
                ]),
                new PasswordStrength([
                    'minStrength' => 4,
                    'minLength' => 8
                ])
            ],
            'first_options' => [
                'label' => 'Nieuw wachtwoord',
            ],
            'second_options' => [
                'label' => 'Nieuw wachtwoord herhalen',
            ],
            'invalid_message' => 'Ingevoerde wachtwoorden komen niet overeen',
            ))
            ->add('submit', SubmitType::class, [
                'label' => 'Wachtwoord wijzigen',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->userClass,
            'csrf_token_id' => 'resetting',
        ]);
    }
}