<?php

namespace App\Tests\Unit\Entity\Settings;

use App\Entity\FormDefinition;
use App\Entity\Settings\NotificationSettings;
use PHPUnit\Framework\TestCase;

class NotificationSettingsTest extends TestCase
{
    public function testGetIdReturnsNullInitially(): void
    {
        $settings = new NotificationSettings();

        $this->assertNull($settings->getId());
    }

    public function testSetAndIsEnabled(): void
    {
        $settings = new NotificationSettings();

        $result = $settings->setEnabled(true);
        $this->assertTrue($settings->isEnabled());
        $this->assertSame($settings, $result);

        $settings->setEnabled(false);
        $this->assertFalse($settings->isEnabled());
    }

    public function testEnabledIsNullInitially(): void
    {
        $settings = new NotificationSettings();

        $this->assertNull($settings->isEnabled());
    }

    public function testSetAndGetType(): void
    {
        $settings = new NotificationSettings();
        $result = $settings->setType('email');

        $this->assertSame('email', $settings->getType());
        $this->assertSame($settings, $result);
    }

    public function testTypeCanBeVariousValues(): void
    {
        $settings = new NotificationSettings();

        $settings->setType('email');
        $this->assertSame('email', $settings->getType());

        $settings->setType('webhook');
        $this->assertSame('webhook', $settings->getType());

        $settings->setType('slack');
        $this->assertSame('slack', $settings->getType());
    }

    public function testSetAndGetTarget(): void
    {
        $settings = new NotificationSettings();
        $result = $settings->setTarget('admin@example.com');

        $this->assertSame('admin@example.com', $settings->getTarget());
        $this->assertSame($settings, $result);
    }

    public function testTargetCanBeNull(): void
    {
        $settings = new NotificationSettings();
        $settings->setTarget(null);

        $this->assertNull($settings->getTarget());
    }

    public function testGetOptionsReturnsEmptyArrayInitially(): void
    {
        $settings = new NotificationSettings();

        $this->assertSame([], $settings->getOptions());
    }

    public function testSetOptions(): void
    {
        $settings = new NotificationSettings();
        $options = ['key1' => 'value1', 'key2' => 'value2'];

        $result = $settings->setOptions($options);

        $this->assertSame($options, $settings->getOptions());
        $this->assertSame($settings, $result);
    }

    public function testGetOptionReturnsValue(): void
    {
        $settings = new NotificationSettings();
        $settings->setOptions(['myKey' => 'myValue']);

        $this->assertSame('myValue', $settings->getOption('myKey'));
    }

    public function testGetOptionReturnsDefaultWhenMissing(): void
    {
        $settings = new NotificationSettings();

        $this->assertNull($settings->getOption('nonexistent'));
        $this->assertSame('default', $settings->getOption('nonexistent', 'default'));
        $this->assertSame(42, $settings->getOption('missing', 42));
    }

    public function testGetOptionWithNullDefault(): void
    {
        $settings = new NotificationSettings();
        $settings->setOptions(['existing' => 'value']);

        $this->assertNull($settings->getOption('missing', null));
        $this->assertSame('value', $settings->getOption('existing', null));
    }

    public function testSetOptionAddsNewOption(): void
    {
        $settings = new NotificationSettings();

        $result = $settings->setOption('newKey', 'newValue');

        $this->assertSame('newValue', $settings->getOption('newKey'));
        $this->assertSame($settings, $result);
    }

    public function testSetOptionUpdatesExistingOption(): void
    {
        $settings = new NotificationSettings();
        $settings->setOption('key', 'originalValue');
        $settings->setOption('key', 'updatedValue');

        $this->assertSame('updatedValue', $settings->getOption('key'));
    }

    public function testSetOptionWithVariousTypes(): void
    {
        $settings = new NotificationSettings();

        $settings->setOption('string', 'value');
        $settings->setOption('int', 42);
        $settings->setOption('bool', true);
        $settings->setOption('array', ['nested' => 'value']);

        $this->assertSame('value', $settings->getOption('string'));
        $this->assertSame(42, $settings->getOption('int'));
        $this->assertTrue($settings->getOption('bool'));
        $this->assertSame(['nested' => 'value'], $settings->getOption('array'));
    }

    public function testRemoveOptionDeletesOption(): void
    {
        $settings = new NotificationSettings();
        $settings->setOptions(['toRemove' => 'value', 'toKeep' => 'another']);

        $result = $settings->removeOption('toRemove');

        $this->assertNull($settings->getOption('toRemove'));
        $this->assertSame('another', $settings->getOption('toKeep'));
        $this->assertSame($settings, $result);
    }

    public function testRemoveOptionDoesNothingIfKeyMissing(): void
    {
        $settings = new NotificationSettings();
        $settings->setOptions(['existing' => 'value']);

        $settings->removeOption('nonexistent');

        $this->assertSame(['existing' => 'value'], $settings->getOptions());
    }

    public function testSetAndGetForm(): void
    {
        $settings = new NotificationSettings();
        $form = new FormDefinition();

        $result = $settings->setForm($form);

        $this->assertSame($form, $settings->getForm());
        $this->assertSame($settings, $result);
    }

    public function testFormCanBeNull(): void
    {
        $settings = new NotificationSettings();
        $form = new FormDefinition();

        $settings->setForm($form);
        $settings->setForm(null);

        $this->assertNull($settings->getForm());
    }

    public function testFluentSetters(): void
    {
        $settings = new NotificationSettings();
        $form = new FormDefinition();

        $result = $settings
            ->setEnabled(true)
            ->setType('email')
            ->setTarget('user@example.com')
            ->setOptions(['key' => 'value'])
            ->setForm($form);

        $this->assertSame($settings, $result);
        $this->assertTrue($settings->isEnabled());
        $this->assertSame('email', $settings->getType());
        $this->assertSame('user@example.com', $settings->getTarget());
        $this->assertSame(['key' => 'value'], $settings->getOptions());
        $this->assertSame($form, $settings->getForm());
    }

    public function testOptionChaining(): void
    {
        $settings = new NotificationSettings();

        $result = $settings
            ->setOption('opt1', 'val1')
            ->setOption('opt2', 'val2')
            ->removeOption('opt1')
            ->setOption('opt3', 'val3');

        $this->assertSame($settings, $result);
        $this->assertNull($settings->getOption('opt1'));
        $this->assertSame('val2', $settings->getOption('opt2'));
        $this->assertSame('val3', $settings->getOption('opt3'));
    }
}
