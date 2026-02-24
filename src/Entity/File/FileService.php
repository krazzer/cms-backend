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

#[Autoconfigure(public: true)]
readonly class FileService
{
    public function __construct(
        private string $storageDir,
        private string $publicMediaDir,
        private EntityManagerInterface $em,
        private Security $security,
        private Filesystem $filesystem,
        private SluggerInterface $slugger,
        private FileRepository $fileRepository
    ) {}

    public function upload(UploadedFile $file, ?int $folderId = null): File
    {
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
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $fileEntity->setUser($user);
        }

        $this->em->persist($fileEntity);
        $this->em->flush();

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

        $type     = 'default';
        $thumbDir = $this->publicMediaDir . '/thumbs/' . $type;
        $this->filesystem->mkdir($thumbDir);

        $thumbFileName = $file->getHash() . '.jpg';
        $thumbPath     = $thumbDir . '/' . $thumbFileName;

        $thumbnail = $image->thumbnail(new Box(200, 200));
        $thumbnail->save($thumbPath);
    }

    public function getUrlCreateIfMissing(File $file, bool $private = false): string
    {
        $publicDir      = $this->publicMediaDir;
        $fileName       = $private ? $file->getFileName(true) : $this->getPublicFileName($file);
        $publicFilePath = $publicDir . '/files/' . $fileName;

        $this->filesystem->mkdir(dirname($publicFilePath));

        if ( ! file_exists($publicFilePath)) {
            $targetPath = $this->storageDir . '/' . $file->getFileName($private);
            $this->filesystem->symlink($targetPath, $publicFilePath);
        }

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