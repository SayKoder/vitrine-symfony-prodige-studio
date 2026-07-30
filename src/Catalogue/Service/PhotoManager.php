<?php

namespace App\Catalogue\Service;

use App\Catalogue\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PhotoImageUploader $imageUploader,
    ) {
    }

    public function save(Photo $photo, ?UploadedFile $imageFichier = null): void
    {
        if (null !== $imageFichier) {
            $ancienneImage = $photo->getImage();
            $photo->setImage($this->imageUploader->televerser($imageFichier));

            if (null !== $ancienneImage) {
                $this->imageUploader->supprimer($ancienneImage);
            }
        }

        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }

    public function delete(Photo $photo): void
    {
        $this->entityManager->remove($photo);
        $this->entityManager->flush();

        if (null !== $photo->getImage()) {
            $this->imageUploader->supprimer($photo->getImage());
        }
    }
}
