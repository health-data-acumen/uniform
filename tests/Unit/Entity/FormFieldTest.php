<?php

namespace App\Tests\Unit\Entity;

use App\Entity\FormDefinition;
use App\Entity\FormField;
use PHPUnit\Framework\TestCase;

class FormFieldTest extends TestCase
{
    public function testGetIdReturnsNullInitially(): void
    {
        $field = new FormField();

        $this->assertNull($field->getId());
    }

    public function testSetAndGetName(): void
    {
        $field = new FormField();
        $result = $field->setName('email');

        $this->assertSame('email', $field->getName());
        $this->assertSame($field, $result);
    }

    public function testSetAndGetLabel(): void
    {
        $field = new FormField();
        $result = $field->setLabel('Email Address');

        $this->assertSame('Email Address', $field->getLabel());
        $this->assertSame($field, $result);
    }

    public function testSetAndGetType(): void
    {
        $field = new FormField();
        $result = $field->setType('email');

        $this->assertSame('email', $field->getType());
        $this->assertSame($field, $result);
    }

    public function testTypeCanBeVariousValues(): void
    {
        $field = new FormField();

        $field->setType('text');
        $this->assertSame('text', $field->getType());

        $field->setType('textarea');
        $this->assertSame('textarea', $field->getType());

        $field->setType('checkbox');
        $this->assertSame('checkbox', $field->getType());

        $field->setType('select');
        $this->assertSame('select', $field->getType());
    }

    public function testIsRequiredDefaultsToTrue(): void
    {
        $field = new FormField();

        $this->assertTrue($field->isRequired());
    }

    public function testSetAndIsRequired(): void
    {
        $field = new FormField();

        $result = $field->setRequired(false);
        $this->assertFalse($field->isRequired());
        $this->assertSame($field, $result);

        $field->setRequired(true);
        $this->assertTrue($field->isRequired());
    }

    public function testSetAndGetPosition(): void
    {
        $field = new FormField();
        $result = $field->setPosition(5);

        $this->assertSame(5, $field->getPosition());
        $this->assertSame($field, $result);
    }

    public function testPositionCanBeZero(): void
    {
        $field = new FormField();
        $field->setPosition(0);

        $this->assertSame(0, $field->getPosition());
    }

    public function testSetAndGetForm(): void
    {
        $field = new FormField();
        $form = new FormDefinition();

        $result = $field->setForm($form);

        $this->assertSame($form, $field->getForm());
        $this->assertSame($field, $result);
    }

    public function testFormCanBeNull(): void
    {
        $field = new FormField();
        $form = new FormDefinition();

        $field->setForm($form);
        $field->setForm(null);

        $this->assertNull($field->getForm());
    }

    public function testFluentSetters(): void
    {
        $field = new FormField();
        $form = new FormDefinition();

        $result = $field
            ->setName('username')
            ->setLabel('Username')
            ->setType('text')
            ->setRequired(true)
            ->setPosition(1)
            ->setForm($form);

        $this->assertSame($field, $result);
        $this->assertSame('username', $field->getName());
        $this->assertSame('Username', $field->getLabel());
        $this->assertSame('text', $field->getType());
        $this->assertTrue($field->isRequired());
        $this->assertSame(1, $field->getPosition());
        $this->assertSame($form, $field->getForm());
    }

    public function testInitialStateHasNullValues(): void
    {
        $field = new FormField();

        $this->assertNull($field->getId());
        $this->assertNull($field->getName());
        $this->assertNull($field->getLabel());
        $this->assertNull($field->getType());
        $this->assertTrue($field->isRequired());
        $this->assertNull($field->getPosition());
        $this->assertNull($field->getForm());
    }
}
