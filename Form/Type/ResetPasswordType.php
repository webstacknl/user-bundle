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
use Webstack\UserBundle\Manager\UserManager;

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
     * @var UserManager
     */
    private $userManager;

    /**
     * @var string
     */
    private $userClass;

    /**
     * ResetPasswordType constructor.
     * @param Security $security
     * @param UserManager $userManager
     * @param string $userClass
     */
    public function __construct(Security $security, UserManager $userManager, string $userClass)
    {
        $this->security = $security;
        $this->userManager = $userManager;
        $this->userClass = $userClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
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
