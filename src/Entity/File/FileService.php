<?php

namespace KikCMS\Entity\File;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use KikCMS\Entity\User\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

// Todo: Autoconfigure is niet nodig vanwege autowiring
#[Autoconfigure(public: true)]
readonly class FileService
{
    public function __construct(
        /**
         * Todo: $storageDir & $publicMediaDir laad je nu in via services.yaml. Zo deed ik het eerder ook, maar kan nu
         * nog mooier met #[Autowire('%variable%')] $variable
         *
         * Dan kan het stukje uit services.yaml weg
         */
        private string $storageDir,
        private string $publicMediaDir,
        private EntityManagerInterface $em,
        private Security $security,
        private Filesystem $filesystem,
        private SluggerInterface $slugger,
        private FileRepository $fileRepository
    ) {}

    // Todo: Door de nieuwe controller kan je straks ?File $folder gebruiken
    public function upload(UploadedFile $file, ?int $folderId = null): File
    {
        // Todo: Mooier om hier Method chaining te gebruiken
        // Todo: Variable kan gewoon $file heten, is expliciet genoeg
        $fileEntity = new File();
        $fileEntity->setName($file->getClientOriginalName());
        $fileEntity->setExtension($file->guessExtension() ?? $file->getClientOriginalExtension());
        $fileEntity->setMimetype($file->getMimeType());
        $fileEntity->setSize($file->getSize());
        $fileEntity->setCreated(new DateTimeImmutable);
        $fileEntity->setUpdated(new DateTimeImmutable);
        $fileEntity->setIsFolder(false);

        if ($folderId) {
            $folder = $this->fileRepository->find($folderId);
            if ($folder && $folder->isFolder()) {
                $fileEntity->setFolder($folder);
            }
        }

        // Todo: Mooier om dit eerst op te halen en dan in de chaining te gebruiken
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $fileEntity->setUser($user);
        }

        $this->em->persist($fileEntity);
        $this->em->flush();

        // Todo: Dit mag wel in een andere method / class
        $targetFilename = $fileEntity->getFileName();
        $targetPath     = $this->storageDir . '/' . $targetFilename;

        $this->filesystem->mkdir(dirname($targetPath));
        $file->move(dirname($targetPath), $targetFilename);

        $fileEntity->setHash(md5_file($targetPath));
        $this->em->flush();

        if ($fileEntity->isImage()) {
            $this->generateThumbnails($targetPath, $fileEntity);
        }

        return $fileEntity;
    }

    private function generateThumbnails(string $originalPath, File $file): void
    {
        $imagine = new Imagine;
        $image   = $imagine->open($originalPath);

        // Todo: magic string, dit zou ergens in een config moeten
        // ter info: https://softwareengineering.stackexchange.com/questions/365339/what-is-wrong-with-magic-strings
        $type = 'default';

        // Todo: thumbs = magic string, dit zou ergens in een config moeten
        $thumbDir = $this->publicMediaDir . '/thumbs/' . $type;
        $this->filesystem->mkdir($thumbDir);

        // Todo: jpeg is hier ook een magic string, webp zou een betere standaard zijn
        $thumbFileName = $file->getHash() . '.jpg';
        $thumbPath     = $thumbDir . '/' . $thumbFileName;

        $thumbnail = $image->thumbnail(new Box(200, 200));
        $thumbnail->save($thumbPath);
    }

    public function getUrlCreateIfMissing(File $file, bool $private = false): string
    {
        $publicDir = $this->publicMediaDir;
        $fileName  = $private ? $file->getFileName(true) : $this->getPublicFileName($file);

        // Todo: files = magic string
        $publicFilePath = $publicDir . '/files/' . $fileName;

        $this->filesystem->mkdir(dirname($publicFilePath));

        if ( ! file_exists($publicFilePath)) {
            $targetPath = $this->storageDir . '/' . $file->getFileName($private);

            // Todo: exclude media/files uit de repo via .gitignore
            $this->filesystem->symlink($targetPath, $publicFilePath);
        }

        // Todo: magic string
        $url = '/media/files/' . $fileName;

        if ($secondsUpdated = $file->secondsUpdated()) {
            $url .= '?u=' . $secondsUpdated;
        }

        return $url;
    }

    private function getPublicFileName(File $file): string
    {
        $slugName = $this->slugger->slug(pathinfo($file->getName(), PATHINFO_FILENAME))->toString();
        return $file->getId() . '-' . $slugName . '.' . $file->getExtension();
    }
}