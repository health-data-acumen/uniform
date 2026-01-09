<?php

namespace App\Tests\Unit\Entity\Settings;

use App\Entity\Settings\NotificationSettings;
use PHPUnit\Framework\TestCase;

class NotificationSettingsOptionsTest extends TestCase
{
    private NotificationSettings $settings;

    protected function setUp(): void
    {
        $this->settings = new NotificationSettings();
    }

    public function testSetAndGetOptions(): void
    {
        $options = ['key1' => 'value1', 'key2' => 'value2'];
        $this->settings->setOptions($options);

        $this->assertSame($options, $this->settings->getOptions());
    }

    public function testSetSingleOption(): void
    {
        $this->settings->setOption('key', 'value');

        $this->assertSame('value', $this->settings->getOption('key'));
    }

    public function testGetOptionWithDefault(): void
    {
        $result = $this->settings->getOption('nonexistent', 'default');

        $this->assertSame('default', $result);
    }

    public function testGetOptionReturnsNullByDefault(): void
    {
        $result = $this->settings->getOption('nonexistent');

        $this->assertNull($result);
    }

    public function testRemoveOption(): void
    {
        $this->settings->setOptions(['key1' => 'value1', 'key2' => 'value2']);
        $this->settings->removeOption('key1');

        $this->assertNull($this->settings->getOption('key1'));
        $this->assertSame('value2', $this->settings->getOption('key2'));
    }

    public function testRemoveNonExistentOptionDoesNotThrow(): void
    {
        $this->settings->setOptions(['key1' => 'value1']);
        $this->settings->removeOption('nonexistent');

        $this->assertSame('value1', $this->settings->getOption('key1'));
    }

    public function testSetOptionOverwritesExisting(): void
    {
        $this->settings->setOption('key', 'original');
        $this->settings->setOption('key', 'updated');

        $this->assertSame('updated', $this->settings->getOption('key'));
    }

    public function testSetOptionWithNestedArray(): void
    {
        $nestedValue = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value',
                ],
            ],
        ];
        $this->settings->setOption('nested', $nestedValue);

        $this->assertSame($nestedValue, $this->settings->getOption('nested'));
    }

    public function testSetOptionWithNullValue(): void
    {
        $this->settings->setOption('nullable', null);

        $this->assertArrayHasKey('nullable', $this->settings->getOptions());
        $this->assertNull($this->settings->getOption('nullable'));
    }

    public function testSetOptionWithBooleanValues(): void
    {
        $this->settings->setOption('enabled', true);
        $this->settings->setOption('disabled', false);

        $this->assertTrue($this->settings->getOption('enabled'));
        $this->assertFalse($this->settings->getOption('disabled'));
    }

    public function testSetOptionWithNumericValues(): void
    {
        $this->settings->setOption('integer', 42);
        $this->settings->setOption('float', 3.14);

        $this->assertSame(42, $this->settings->getOption('integer'));
        $this->assertSame(3.14, $this->settings->getOption('float'));
    }

    public function testSetOptionsReplacesAllOptions(): void
    {
        $this->settings->setOptions(['old' => 'value']);
        $this->settings->setOptions(['new' => 'value']);

        $this->assertNull($this->settings->getOption('old'));
        $this->assertSame('value', $this->settings->getOption('new'));
    }

    public function testFluentInterfaceForSetOption(): void
    {
        $result = $this->settings->setOption('key', 'value');

        $this->assertSame($this->settings, $result);
    }

    public function testFluentInterfaceForSetOptions(): void
    {
        $result = $this->settings->setOptions(['key' => 'value']);

        $this->assertSame($this->settings, $result);
    }

    public function testFluentInterfaceForRemoveOption(): void
    {
        $result = $this->settings->removeOption('key');

        $this->assertSame($this->settings, $result);
    }

    public function testMethodChaining(): void
    {
        $this->settings
            ->setOption('first', 'value1')
            ->setOption('second', 'value2')
            ->removeOption('first')
            ->setOption('third', 'value3');

        $this->assertNull($this->settings->getOption('first'));
        $this->assertSame('value2', $this->settings->getOption('second'));
        $this->assertSame('value3', $this->settings->getOption('third'));
    }

    public function testEmptyOptionsArrayByDefault(): void
    {
        $settings = new NotificationSettings();

        $this->assertSame([], $settings->getOptions());
    }

    public function testGetOptionWithCustomDefaultTypes(): void
    {
        $this->assertSame([], $this->settings->getOption('array', []));
        $this->assertSame(0, $this->settings->getOption('number', 0));
        $this->assertSame('', $this->settings->getOption('string', ''));
        $this->assertFalse($this->settings->getOption('bool', false));
    }
}
