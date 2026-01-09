<?php

namespace App\Tests\Unit\Dto;

use App\Dto\FormEndpointDto;
use PHPUnit\Framework\TestCase;

class FormEndpointDtoTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $dto = new FormEndpointDto(
            id: 'form-123',
            name: 'Contact Form',
            isActive: true,
            submissionsCount: 42
        );

        $this->assertSame('form-123', $dto->id);
        $this->assertSame('Contact Form', $dto->name);
        $this->assertTrue($dto->isActive);
        $this->assertSame(42, $dto->submissionsCount);
    }

    public function testIdPropertyIsAccessible(): void
    {
        $dto = new FormEndpointDto('uuid-456', 'Test', false, 0);

        $this->assertSame('uuid-456', $dto->id);
    }

    public function testNamePropertyIsAccessible(): void
    {
        $dto = new FormEndpointDto('id', 'My Form Name', true, 10);

        $this->assertSame('My Form Name', $dto->name);
    }

    public function testIsActivePropertyIsAccessible(): void
    {
        $dtoActive = new FormEndpointDto('id', 'Name', true, 0);
        $dtoInactive = new FormEndpointDto('id', 'Name', false, 0);

        $this->assertTrue($dtoActive->isActive);
        $this->assertFalse($dtoInactive->isActive);
    }

    public function testSubmissionsCountPropertyIsAccessible(): void
    {
        $dto = new FormEndpointDto('id', 'Name', true, 999);

        $this->assertSame(999, $dto->submissionsCount);
    }

    public function testSubmissionsCountCanBeZero(): void
    {
        $dto = new FormEndpointDto('id', 'Name', true, 0);

        $this->assertSame(0, $dto->submissionsCount);
    }

    public function testEmptyStringValuesAreAllowed(): void
    {
        $dto = new FormEndpointDto('', '', false, 0);

        $this->assertSame('', $dto->id);
        $this->assertSame('', $dto->name);
    }
}
