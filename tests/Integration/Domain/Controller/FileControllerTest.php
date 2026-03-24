<?php

namespace KikCMS\Tests\Integration\Domain\Controller;

use DateTimeImmutable;
use KikCMS\Entity\File\Dto\ChangeKeyDto;
use KikCMS\Entity\File\Dto\ChangeNameDto;
use KikCMS\Entity\File\Dto\CreateDto;
use KikCMS\Entity\File\Dto\DeleteDto;
use KikCMS\Entity\File\Dto\OpenDto;
use KikCMS\Entity\File\Dto\PasteDto;
use KikCMS\Entity\File\Dto\SearchDto;
use KikCMS\Entity\File\File;
use KikCMS\Entity\File\FileController;
use KikCMS\Entity\File\FilePublicService;
use KikCMS\Tests\Integration\DbKernelTestCase;

class FileControllerTest extends DbKernelTestCase
{
    private FileController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();

        $mockPublicService = $this->createMock(FilePublicService::class);
        $mockPublicService->method('getUrlCreateIfMissing')
            ->willReturnCallback(fn($file) => '/media/files/' . $file->getId() . '-' . $file->getName());
        $container->set(FilePublicService::class, $mockPublicService);

        $this->controller = $container->get(FileController::class);
    }

    public function testCreateFolder(): void
    {
        $createDto           = new CreateDto();
        $createDto->name     = 'Test Folder';
        $createDto->folderId = null;

        $response = $this->controller->createFolder($createDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('files', $data);
        $this->assertArrayHasKey('path', $data);
        $this->assertCount(1, $data['files']);
        $this->assertEquals('Test Folder', $data['files'][0]['name']);
        $this->assertTrue($data['files'][0]['isDir']);

        $folder = $this->em->getRepository(File::class)->findOneBy(['name' => 'Test Folder']);
        $this->assertNotNull($folder);
        $this->assertTrue($folder->isFolder());
        $this->assertNull($folder->getFolder());
    }

    public function testOpenRootFolder(): void
    {
        $this->createTestFolder('Folder A', null);
        $this->createTestFile('file.txt', null);

        $openDto     = new OpenDto();
        $openDto->id = null;

        $response = $this->controller->openFolder($openDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['files']);
        $this->assertEquals([], $data['path']);
    }

    public function testOpenSubFolder(): void
    {
        [$parent, $child] = $this->createFolderHierarchy();

        $openDto     = new OpenDto();
        $openDto->id = $child->getId();

        $response = $this->controller->openFolder($openDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data['files']);
        $this->assertEquals('file.txt', $data['files'][0]['name']);
        $this->assertCount(2, $data['path']);
        $this->assertEquals('Parent', $data['path'][$parent->getId()]);
        $this->assertEquals('Child', $data['path'][$child->getId()]);
    }

    public function testChangeFilename(): void
    {
        $file   = $this->createTestFile('old.txt', null);
        $fileId = $file->getId();

        $changeNameDto       = new ChangeNameDto();
        $changeNameDto->id   = $fileId;
        $changeNameDto->name = 'new';

        $response = $this->controller->changeFilename($changeNameDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data['files']);
        $this->assertEquals('new.txt', $data['files'][0]['name']);

        $updatedFile = $this->em->find(File::class, $fileId);
        $this->assertEquals('new.txt', $updatedFile->getName());
    }

    public function testChangeKey(): void
    {
        $file   = $this->createTestFile('test.txt', null);
        $fileId = $file->getId();

        $changeKeyDto       = new ChangeKeyDto();
        $changeKeyDto->id   = $fileId;
        $changeKeyDto->name = 'secret';

        $response = $this->controller->changeKey($changeKeyDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('files', $data);

        $updatedFile = $this->em->find(File::class, $fileId);
        $this->assertEquals('secret', $updatedFile->getKey());
    }

    public function testDeleteFile(): void
    {
        $file   = $this->createTestFile('delete.txt', null);
        $fileId = $file->getId();

        $deleteDto           = new DeleteDto();
        $deleteDto->ids      = [$fileId];
        $deleteDto->folderId = null;

        $response = $this->controller->deleteFiles($deleteDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(0, $data['files']);

        $this->assertNull($this->em->find(File::class, $fileId));
    }

    public function testDeleteFolderRecursively(): void
    {
        [$parent, $child, $file] = $this->createFolderHierarchy();

        $parentId = $parent->getId();
        $childId  = $child->getId();
        $fileId   = $file->getId();

        $deleteDto           = new DeleteDto();
        $deleteDto->ids      = [$parentId];
        $deleteDto->folderId = null;

        $response = $this->controller->deleteFiles($deleteDto);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertNull($this->em->find(File::class, $parentId));
        $this->assertNull($this->em->find(File::class, $childId));
        $this->assertNull($this->em->find(File::class, $fileId));
    }

    public function testMoveFolder(): void
    {
        $folder1 = $this->createTestFolder('Folder1', null);
        $folder2 = $this->createTestFolder('Folder2', null);
        $file    = $this->createTestFile('file.txt', null);

        $file->setFolder($folder1);
        $this->em->flush();
        $this->em->clear();

        $pasteDto         = new PasteDto();
        $pasteDto->ids    = [$folder1->getId()];
        $pasteDto->folder = $folder2->getId();

        $response = $this->controller->pasteFiles($pasteDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data['files']);
        $this->assertEquals('Folder1', $data['files'][0]['name']);

        $movedFolder = $this->em->find(File::class, $folder1->getId());
        $this->assertEquals($folder2->getId(), $movedFolder->getFolder()->getId());

        $movedFile = $this->em->find(File::class, $file->getId());
        $this->assertEquals($folder1->getId(), $movedFile->getFolder()->getId());
    }

    public function testMoveFolderIntoSelfFails(): void
    {
        $folder   = $this->createTestFolder('Folder', null);
        $folderId = $folder->getId();

        $pasteDto         = new PasteDto();
        $pasteDto->ids    = [$folderId];
        $pasteDto->folder = $folderId;

        $response = $this->controller->pasteFiles($pasteDto);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('zichzelf', $data['error']);
    }

    public function testMoveFolderIntoChildFails(): void
    {
        $parent = $this->createTestFolder('Parent', null);
        $child  = $this->createTestFolder('Child', null);

        $child->setFolder($parent);
        $this->em->flush();
        $this->em->clear();

        $pasteDto         = new PasteDto();
        $pasteDto->ids    = [$parent->getId()];
        $pasteDto->folder = $child->getId();

        $response = $this->controller->pasteFiles($pasteDto);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('submap', $data['error']);
    }

    public function testSearch(): void
    {
        $this->createTestFile('document.txt', null);
        $this->createTestFile('image.jpg', null);
        $this->createTestFolder('Documents', null);

        $searchDto         = new SearchDto();
        $searchDto->search = 'doc';

        $response = $this->controller->search($searchDto);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['files']);

        $names = array_column($data['files'], 'name');
        $this->assertContains('document.txt', $names);
        $this->assertContains('Documents', $names);
        $this->assertNotContains('image.jpg', $names);
        $this->assertEquals([], $data['path']);
    }

    private function createFolderHierarchy(): array
    {
        $parent = $this->createTestFolder('Parent', null);
        $child  = $this->createTestFolder('Child', null);
        $file   = $this->createTestFile('file.txt', null);

        $child->setFolder($parent);
        $file->setFolder($child);
        $this->em->flush();
        $this->em->clear();

        return [
            $this->em->find(File::class, $parent->getId()),
            $this->em->find(File::class, $child->getId()),
            $this->em->find(File::class, $file->getId()),
        ];
    }

    private function createTestFolder(string $name, ?File $parent): File
    {
        $folder = new File();
        $folder->setName($name);
        $folder->setIsFolder(true);
        $folder->setFolder($parent);
        $folder->setCreated(new DateTimeImmutable());
        $folder->setUpdated(new DateTimeImmutable());
        $folder->setSize(0);

        $this->em->persist($folder);
        $this->em->flush();

        return $this->em->getRepository(File::class)->findOneBy(['name' => $name]);
    }

    private function createTestFile(string $name, ?File $parent): File
    {
        $file = new File();
        $file->setName($name);
        $file->setIsFolder(false);
        $file->setFolder($parent);
        $file->setExtension(pathinfo($name, PATHINFO_EXTENSION));
        $file->setMimetype('text/plain');
        $file->setCreated(new DateTimeImmutable());
        $file->setUpdated(new DateTimeImmutable());
        $file->setSize(123);
        $file->setHash(md5(uniqid()));

        $this->em->persist($file);
        $this->em->flush();

        $file = $this->em->getRepository(File::class)->findOneBy(['name' => $name]);

        $storageDir = self::getContainer()->getParameter('cms.storage.dir');
        $filePath   = $storageDir . '/' . $file->getId() . '.' . $file->getExtension();
        if ( ! is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
        }
        file_put_contents($filePath, 'dummy content');

        return $file;
    }
}