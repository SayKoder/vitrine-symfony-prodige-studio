<?php

namespace App\Catalogue\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ContactMessage>
 */
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Telephone (facultatif)',
                'required' => false,
            ])
            ->add('sujet', ChoiceType::class, [
                'label' => 'Sujet',
                'placeholder' => 'Choisissez un sujet',
                'choices' => [
                    'Portrait' => 'Portrait',
                    'Mariage' => 'Mariage',
                    'Corporate' => 'Corporate',
                    'Evenement' => 'Evenement',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre projet',
            ])
            ->add('siteWeb', TextType::class, [
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
        ]);
    }
}
