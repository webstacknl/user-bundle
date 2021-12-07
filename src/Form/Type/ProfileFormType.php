<?php

namespace Webstack\UserBundle\Form\Type;

use DomainException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ProfileFormType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Voornaam',
            ])
            ->add('lastNamePrefix', TextType::class, [
                'label' => 'Tussenvoegsel',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Achternaam',
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mailadres',
                'disabled' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Gegevens wijzigen',
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
