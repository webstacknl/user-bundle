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
     * @var bool
     */
    private $passwordCompromised;

    /**
     * @var int
     */
    protected $minLength;

    /**
     * @var int
     */
    protected $minStrength;

    /**
     * ResetPasswordType constructor.
     * @param Security $security
     * @param string $userClass
     * @param bool $passwordCompromised
     * @param int $minStrength
     * @param int $minLength
     */
    public function __construct(Security $security, string $userClass, bool $passwordCompromised, int $minStrength, int $minLength)
    {
        $this->security = $security;
        $this->userClass = $userClass;
        $this->passwordCompromised = $passwordCompromised;
        $this->minStrength = $minStrength;
        $this->minLength = $minLength;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $notPasswordCompromised = null;
        $passwordStrength = new PasswordStrength([
            'minStrength' => $this->minStrength,
            'minLength' => $this->minLength
        ]);

        if ($this->passwordCompromised) {
            $notPasswordCompromised = [
                new NotCompromisedPassword([
                    'message' => 'Het ingevulde wachtwoord kan niet worden gebruikt omdat deze voorkomt op een lijst met gelekte wachtwoorden.',
                ]),
                $passwordStrength
            ];
        }

        $builder->add('password', RepeatedType::class, [
            'label' => 'Wachtwoord',
            'type' => PasswordType::class,
            'options' => [
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ],
            'constraints' => $notPasswordCompromised ?? [$passwordStrength],
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
