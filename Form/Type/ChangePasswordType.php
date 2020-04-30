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
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

/**
 * Class ChangePasswordType
 */
class ChangePasswordType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * ChangePasswordType constructor.
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
                'constraints' => [
                    new NotCompromisedPassword([
                        'message' => 'Het ingevulde wachtwoord kan niet worden gebruikt omdat deze voorkomt op een lijst met gelekte wachtwoorden.',
                    ]),
                    new PasswordStrength([
                        'minStrength' => 4,
                        'minLength' => 8
                    ])
                ],
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
                'invalid_message' => 'De ingevoerde wachtwoorden komen niet overeen.',
            ])
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
            'data_class' => get_class($this->security->getUser())
        ]);
    }
}
