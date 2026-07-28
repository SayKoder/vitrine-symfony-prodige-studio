<?php

namespace App\Tests\Catalogue;

use App\Catalogue\Service\PrestationImageUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PrestationImageUploaderTest extends TestCase
{
    private const string PNG_UN_PIXEL_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    private string $dossierPublic;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->dossierPublic = sys_get_temp_dir().'/vitrineps-uploads-test-'.uniqid();
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->dossierPublic.'/uploads/prestations');
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->dossierPublic);
    }

    private function creerUploader(): PrestationImageUploader
    {
        return new PrestationImageUploader($this->dossierPublic, $this->filesystem);
    }

    private function creerFichierTest(): UploadedFile
    {
        $chemin = sys_get_temp_dir().'/'.uniqid('image-test-', true).'.png';
        file_put_contents($chemin, base64_decode(self::PNG_UN_PIXEL_BASE64));

        return new UploadedFile($chemin, 'image-test.png', 'image/png', null, true);
    }

    public function testTeleverserDeplaceLeFichierEtRetourneUnNomUnique(): void
    {
        $uploader = $this->creerUploader();

        $nomFichier = $uploader->televerser($this->creerFichierTest());

        self::assertFileExists($this->dossierPublic.'/uploads/prestations/'.$nomFichier);
        self::assertStringEndsWith('.png', $nomFichier);
    }

    public function testDeuxTeleversementsProduisentDesNomsDifferents(): void
    {
        $uploader = $this->creerUploader();

        $premierNom = $uploader->televerser($this->creerFichierTest());
        $secondNom = $uploader->televerser($this->creerFichierTest());

        self::assertNotSame($premierNom, $secondNom);
    }

    public function testSupprimerRetireLeFichierDuDisque(): void
    {
        $uploader = $this->creerUploader();
        $nomFichier = $uploader->televerser($this->creerFichierTest());

        $uploader->supprimer($nomFichier);

        self::assertFileDoesNotExist($this->dossierPublic.'/uploads/prestations/'.$nomFichier);
    }

    public function testSupprimerUnFichierInexistantNeLevePasDException(): void
    {
        $uploader = $this->creerUploader();

        $uploader->supprimer('fichier-qui-n-existe-pas.png');

        $this->expectNotToPerformAssertions();
    }

    public function testUrlPubliquePointeVersLeSousDossierUploads(): void
    {
        $uploader = $this->creerUploader();

        self::assertSame('/uploads/prestations/photo.png', $uploader->urlPublique('photo.png'));
    }
}
