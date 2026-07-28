<?php

namespace App\Catalogue\Form;

use App\Catalogue\Entity\Prestation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PrestationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (EUR)',
                'scale' => 2,
            ])
            ->add('dureeMinutes', IntegerType::class, [
                'label' => 'Duree (minutes)',
                'required' => false,
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Visible dans le catalogue',
                'required' => false,
            ])
            ->add('misEnAvant', CheckboxType::class, [
                'label' => 'Mettre en avant sur la vitrine',
                'required' => false,
            ])
            ->add('imageFichier', FileType::class, [
                'label' => 'Photo (remplace la photo actuelle si renseignee)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Merci de deposer une image JPEG, PNG ou WebP.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prestation::class,
        ]);
    }
}
