<?php

namespace App\Tests\Unit\Menu;

use App\Entity\FormDefinition;
use App\Menu\DefaultMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class DefaultMenuBuilderTest extends TestCase
{
    public function testBuildDefaultMenuReturnsItemInterface(): void
    {
        $factory = $this->createMockFactory();

        $builder = new DefaultMenuBuilder($factory);
        $result = $builder->buildDefaultMenu([]);

        $this->assertInstanceOf(ItemInterface::class, $result);
    }

    public function testBuildDefaultMenuContainsFormEndpointsLink(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $rootMenu->expects($this->exactly(2))
            ->method('addChild');

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')
            ->with('root')
            ->willReturn($rootMenu);

        $builder = new DefaultMenuBuilder($factory);
        $builder->buildDefaultMenu([]);
    }

    public function testBuildDefaultMenuContainsSettingsLink(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $addChildCalls = [];
        $rootMenu->method('addChild')
            ->willReturnCallback(function ($name, $options) use (&$addChildCalls, $rootMenu) {
                $addChildCalls[] = ['name' => $name, 'options' => $options];

                return $rootMenu;
            });

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')->willReturn($rootMenu);

        $builder = new DefaultMenuBuilder($factory);
        $builder->buildDefaultMenu([]);

        $this->assertCount(2, $addChildCalls);
    }

    public function testBuildFormEndpointMenuReturnsItemInterface(): void
    {
        $factory = $this->createMockFactory();

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(123);

        $builder = new DefaultMenuBuilder($factory);
        $result = $builder->buildFormEndpointMenu(['endpoint' => $endpoint]);

        $this->assertInstanceOf(ItemInterface::class, $result);
    }

    public function testBuildFormEndpointMenuRequiresEndpointOption(): void
    {
        $factory = $this->createMockFactory();

        $builder = new DefaultMenuBuilder($factory);

        $this->expectException(MissingOptionsException::class);
        $builder->buildFormEndpointMenu([]);
    }

    public function testBuildFormEndpointMenuValidatesEndpointType(): void
    {
        $factory = $this->createMockFactory();

        $builder = new DefaultMenuBuilder($factory);

        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);
        $builder->buildFormEndpointMenu(['endpoint' => 'not_a_form_definition']);
    }

    public function testBuildFormEndpointMenuContainsSetupLink(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $addChildCalls = [];
        $rootMenu->method('addChild')
            ->willReturnCallback(function ($name, $options) use (&$addChildCalls, $rootMenu) {
                $addChildCalls[] = ['name' => $name, 'options' => $options];

                return $rootMenu;
            });

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')->willReturn($rootMenu);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $builder = new DefaultMenuBuilder($factory);
        $builder->buildFormEndpointMenu(['endpoint' => $endpoint]);

        $routes = array_column(array_column($addChildCalls, 'options'), 'route');
        $this->assertContains('app_dashboard_form_endpoint_setup', $routes);
    }

    public function testBuildFormEndpointMenuContainsSubmissionsLink(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $addChildCalls = [];
        $rootMenu->method('addChild')
            ->willReturnCallback(function ($name, $options) use (&$addChildCalls, $rootMenu) {
                $addChildCalls[] = ['name' => $name, 'options' => $options];

                return $rootMenu;
            });

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')->willReturn($rootMenu);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $builder = new DefaultMenuBuilder($factory);
        $builder->buildFormEndpointMenu(['endpoint' => $endpoint]);

        $routes = array_column(array_column($addChildCalls, 'options'), 'route');
        $this->assertContains('app_dashboard_form_endpoint_submission_list', $routes);
    }

    public function testBuildFormEndpointMenuIncludesRouteParameters(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $addChildCalls = [];
        $rootMenu->method('addChild')
            ->willReturnCallback(function ($name, $options) use (&$addChildCalls, $rootMenu) {
                $addChildCalls[] = ['name' => $name, 'options' => $options];

                return $rootMenu;
            });

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')->willReturn($rootMenu);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(456);

        $builder = new DefaultMenuBuilder($factory);
        $builder->buildFormEndpointMenu(['endpoint' => $endpoint]);

        foreach ($addChildCalls as $call) {
            if (isset($call['options']['routeParameters'])) {
                $this->assertArrayHasKey('id', $call['options']['routeParameters']);
                $this->assertSame(456, $call['options']['routeParameters']['id']);
            }
        }
    }

    public function testBuildFormEndpointMenuCreatesSettingsSubmenu(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $childCount = 0;
        $rootMenu->method('addChild')
            ->willReturnCallback(function () use (&$childCount, $rootMenu) {
                ++$childCount;

                return $rootMenu;
            });

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')->willReturn($rootMenu);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $builder = new DefaultMenuBuilder($factory);
        $builder->buildFormEndpointMenu(['endpoint' => $endpoint]);

        $this->assertSame(5, $childCount);
    }

    private function createMockFactory(): FactoryInterface
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->method('addChild')->willReturnSelf();

        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')
            ->with('root')
            ->willReturn($menuItem);

        return $factory;
    }
}
