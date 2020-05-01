<?php

namespace Webstack\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

/**
 * Class ProfileFormType
 */
class ProfileFormType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * ProfileFormType constructor.
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
            ->add('firstName', TextType::class, [
                'label' => 'Voornaam'
            ])
            ->add('lastNamePrefix', TextType::class, [
                'label' => 'Tussenvoegsel',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Achternaam'
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mailadres',
                'disabled' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Gegevens wijzigen',
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
