<?php

declare(strict_types=1);

namespace KikCMS\Tests\Unit\Domain\DataTable\Config;

use KikCMS\Domain\DataTable\Config\DataTablePathService;
use PHPUnit\Framework\TestCase;

final class DataTablePathServiceTest extends TestCase
{
    private DataTablePathService $service;

    protected function setUp(): void
    {
        $this->service = new DataTablePathService();
    }

    public function testIsPathReturnsTrueWhenSeparatorPresent(): void
    {
        $this->assertTrue(
            $this->service->isPath('translations.nl.title')
        );
    }

    public function testIsPathReturnsFalseWhenNoSeparatorPresent(): void
    {
        $this->assertFalse(
            $this->service->isPath('title')
        );
    }

    public function testGetDataByPathReturnsValue(): void
    {
        $data = ['translations' => ['nl' => ['title' => 'Hallo']]];

        $value = $this->service->getDataByPath($data, 'translations.*.title', 'nl');

        $this->assertSame('Hallo', $value);
    }

    public function testGetDataByPathReturnsNullWhenKeyDoesNotExist(): void
    {
        $data = ['translations' => []];

        $value = $this->service->getDataByPath($data, 'translations.*.title', 'nl');

        $this->assertNull($value);
    }

    public function testGetDataByPathReturnsNullWhenIntermediateValueIsNotArray(): void
    {
        $data = ['translations' => 'not-an-array'];

        $value = $this->service->getDataByPath($data, 'translations.*.title', 'nl');

        $this->assertNull($value);
    }

    public function testConvertPathToArray(): void
    {
        $result = $this->service->convertPathToArray('translations.*.title', 'Hallo', 'nl');

        $this->assertSame(['translations' => ['nl' => ['title' => 'Hallo']]], $result);
    }

    public function testToJson(): void
    {
        $result = $this->service->toJson('translations.*.title', 'en');
        $this->assertSame(['translations', '$.en.title'], $result);
    }
}
