<?php

namespace App\Catalogue\Form;

use App\Catalogue\Entity\CategoriePhoto;
use App\Catalogue\Entity\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * @extends AbstractType<Photo>
 */
class PhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', EnumType::class, [
                'label' => 'Categorie',
                'class' => CategoriePhoto::class,
                'choice_label' => static fn (CategoriePhoto $categorie): string => $categorie->libelle(),
            ])
            ->add('legende', TextType::class, [
                'label' => 'Legende (optionnelle)',
                'required' => false,
            ])
            ->add('misEnAvant', CheckboxType::class, [
                'label' => 'Mettre en avant sur la page d\'accueil',
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
            'data_class' => Photo::class,
        ]);
    }
}
