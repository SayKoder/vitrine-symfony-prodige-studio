<?php

namespace App\Catalogue\Service;

use App\Catalogue\Entity\Prestation;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PrestationManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PrestationImageUploader $imageUploader,
    ) {
    }

    public function save(Prestation $prestation, ?UploadedFile $imageFichier = null): void
    {
        if (null !== $imageFichier) {
            $ancienneImage = $prestation->getImage();
            $prestation->setImage($this->imageUploader->televerser($imageFichier));

            if (null !== $ancienneImage) {
                $this->imageUploader->supprimer($ancienneImage);
            }
        }

        $this->entityManager->persist($prestation);
        $this->entityManager->flush();
    }

    public function delete(Prestation $prestation): void
    {
        try {
            $this->entityManager->remove($prestation);
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $exception) {
            throw new PrestationEnUsageException($prestation, $exception);
        }

        if (null !== $prestation->getImage()) {
            $this->imageUploader->supprimer($prestation->getImage());
        }
    }
}
