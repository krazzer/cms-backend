<?php

namespace KikCMS\Tests\Integration\Domain\Controller;

use DateTimeImmutable;
use KikCMS\Entity\File\File;
use KikCMS\Entity\File\FileController;
use KikCMS\Tests\Integration\DbKernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class FileControllerTest extends DbKernelTestCase
{
    private FileController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = self::getContainer()->get(FileController::class);
    }

    public function testCreateFolder(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode(['name' => 'Test Folder', 'folder' => null]));
        $response = $this->controller->createFolder($request);

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

        $request = new Request([], [], [], [], [], [], json_encode(['id' => null]));
        $response = $this->controller->openFolder($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertCount(2, $data['files']);
        $this->assertEquals([], $data['path']);
    }

    public function testOpenSubFolder(): void
    {
        $parent = $this->createTestFolder('Parent', null);
        $child = $this->createTestFolder('Child', $parent);
        $this->createTestFile('file.txt', $child);

        $request = new Request([], [], [], [], [], [], json_encode(['id' => $child->getId()]));
        $response = $this->controller->openFolder($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertCount(1, $data['files']);
        $this->assertEquals('file.txt', $data['files'][0]['name']);
        $this->assertCount(1, $data['path']);
        $this->assertEquals('Parent', $data['path'][$parent->getId()]);
    }

    public function testChangeFilename(): void
    {
        $file = $this->createTestFile('old.txt', null);

        $request = new Request([], [], [], [], [], [], json_encode(['id' => $file->getId(), 'name' => 'new']));
        $response = $this->controller->changeFilename($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $updatedFile = $this->em->find(File::class, $file->getId());
        $this->assertEquals('new.txt', $updatedFile->getName());

        $this->assertCount(1, $data['files']);
        $this->assertEquals('new.txt', $data['files'][0]['name']);
    }

    public function testChangeKey(): void
    {
        $file = $this->createTestFile('test.txt', null);

        $request = new Request([], [], [], [], [], [], json_encode(['id' => $file->getId(), 'name' => 'secret']));
        $response = $this->controller->changeKey($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $updatedFile = $this->em->find(File::class, $file->getId());
        $this->assertEquals('secret', $updatedFile->getKey());
        $this->assertArrayHasKey('files', $data);
    }

    public function testDeleteFile(): void
    {
        $file = $this->createTestFile('delete.txt', null);

        $request = new Request([], [], [], [], [], [], json_encode(['ids' => [$file->getId()], 'folder' => null]));
        $response = $this->controller->deleteFiles($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertNull($this->em->find(File::class, $file->getId()));
        $this->assertCount(0, $data['files']);
    }

    public function testDeleteFolderRecursively(): void
    {
        $parent = $this->createTestFolder('Parent', null);
        $child = $this->createTestFolder('Child', $parent);
        $file = $this->createTestFile('file.txt', $child);

        $request = new Request([], [], [], [], [], [], json_encode(['ids' => [$parent->getId()], 'folder' => null]));
        $response = $this->controller->deleteFiles($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertNull($this->em->find(File::class, $parent->getId()));
        $this->assertNull($this->em->find(File::class, $child->getId()));
        $this->assertNull($this->em->find(File::class, $file->getId()));
    }

    public function testMoveFolder(): void
    {
        $folder1 = $this->createTestFolder('Folder1', null);
        $folder2 = $this->createTestFolder('Folder2', null);
        $file = $this->createTestFile('file.txt', $folder1);

        $request = new Request([], [], [], [], [], [], json_encode(['ids' => [$folder1->getId()], 'folder' => $folder2->getId()]));
        $response = $this->controller->pasteFiles($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $movedFolder = $this->em->find(File::class, $folder1->getId());
        $this->assertEquals($folder2->getId(), $movedFolder->getFolder()->getId());

        $movedFile = $this->em->find(File::class, $file->getId());
        $this->assertEquals($folder1->getId(), $movedFile->getFolder()->getId());

        $this->assertCount(1, $data['files']);
        $this->assertEquals('Folder1', $data['files'][0]['name']);
    }

    public function testMoveFolderIntoSelfFails(): void
    {
        $folder = $this->createTestFolder('Folder', null);

        $request = new Request([], [], [], [], [], [], json_encode(['ids' => [$folder->getId()], 'folder' => $folder->getId()]));
        $response = $this->controller->pasteFiles($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('zichzelf', $data['error']);
    }

    public function testMoveFolderIntoChildFails(): void
    {
        $parent = $this->createTestFolder('Parent', null);
        $child = $this->createTestFolder('Child', $parent);

        $request = new Request([], [], [], [], [], [], json_encode(['ids' => [$parent->getId()], 'folder' => $child->getId()]));
        $response = $this->controller->pasteFiles($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('submap', $data['error']);
    }

    public function testSearch(): void
    {
        $this->createTestFile('document.txt', null);
        $this->createTestFile('image.jpg', null);
        $this->createTestFolder('Documents', null);

        $request = new Request([], [], [], [], [], [], json_encode(['search' => 'doc']));
        $response = $this->controller->search($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertCount(2, $data['files']);
        $names = array_column($data['files'], 'name');
        $this->assertContains('document.txt', $names);
        $this->assertContains('Documents', $names);
        $this->assertNotContains('image.jpg', $names);
        $this->assertEquals([], $data['path']);
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

        return $folder;
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

        return $file;
    }
}