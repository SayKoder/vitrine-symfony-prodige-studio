<?php

namespace App\Catalogue\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoImageUploader
{
    private const string SOUS_DOSSIER = 'uploads/photos';

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')] private readonly string $dossierPublic,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function televerser(UploadedFile $fichier): string
    {
        $nomFichier = uniqid('photo-', true).'.'.$fichier->guessExtension();

        $fichier->move($this->cheminDossier(), $nomFichier);

        return $nomFichier;
    }

    public function supprimer(string $nomFichier): void
    {
        $chemin = $this->cheminDossier().'/'.$nomFichier;

        if ($this->filesystem->exists($chemin)) {
            $this->filesystem->remove($chemin);
        }
    }

    public function urlPublique(string $nomFichier): string
    {
        return '/'.self::SOUS_DOSSIER.'/'.$nomFichier;
    }

    private function cheminDossier(): string
    {
        return $this->dossierPublic.'/'.self::SOUS_DOSSIER;
    }
}
