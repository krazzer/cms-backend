<?php

namespace KikCMS\Tests\Unit\Domain\DataTable\SourceService;

use KikCMS\Domain\DataTable\Rearrange\ArrayRearrangeService;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation;
use PHPUnit\Framework\TestCase;

class ArrayRearrangeServiceTest extends TestCase
{
    public function testRearrange()
    {
        $input = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3']
        ];

        $expectedOutput1 = [
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
            ['id' => 1, 'name' => 'Item 1'],
        ];

        $expectedOutput2 = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 3, 'name' => 'Item 3'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        $service = new ArrayRearrangeService();

        $output1 = $service->rearrange(1, 3, RearrangeLocation::BEFORE, $input);
        $output2 = $service->rearrange(3, 1, RearrangeLocation::AFTER, $input);

        $this->assertEquals($expectedOutput1, $output1);
        $this->assertEquals($expectedOutput2, $output2);
    }
}