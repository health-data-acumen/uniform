<?php

namespace App\Menu;

use App\Entity\FormDefinition;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

#[AutoconfigureTag(name: 'knp_menu.menu_builder', attributes: ['method' => 'buildDefaultMenu', 'alias' => 'admin.default'])]
#[AutoconfigureTag(name: 'knp_menu.menu_builder', attributes: ['method' => 'buildFormEndpointMenu', 'alias' => 'admin.form_endpoint'])]
readonly class DefaultMenuBuilder
{
    public function __construct(private FactoryInterface $factory)
    {
    }

    public function buildDefaultMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild(t('menu.admin.default.form_endpoints'), ['route' => 'app_admin_form_endpoint_list']);
        $menu->addChild(t('menu.admin.default.settings'), ['route' => 'app_admin_settings']);

        return $menu;
    }

    public function buildFormEndpointMenu(array $options): ItemInterface
    {
        $endpoint = (new OptionsResolver())
            ->setRequired(['endpoint'])
            ->setAllowedTypes('endpoint', FormDefinition::class)
            ->resolve($options)['endpoint']
        ;

        $routeParameters = ['id' => $endpoint->getId()];

        $menu = $this->factory->createItem('root');
        $menu->addChild(t('menu.admin.form_endpoint.submissions'), [
            'route' => 'app_admin_form_endpoint_submission_list',
            'routeParameters' => $routeParameters,
        ]);
        $settingsMenu = $menu->addChild(t('menu.admin.form_endpoint.settings'), [
            'route' => 'app_admin_form_endpoint_general_settings',
            'routeParameters' => $routeParameters,
        ]);

        $settingsMenu->addChild(t('menu.admin.form_endpoint.settings.general'), [
            'route' => 'app_admin_form_endpoint_general_settings',
            'routeParameters' => $routeParameters,
        ]);
        $settingsMenu->addChild(t('menu.admin.form_endpoint.settings.notifications'), [
            'route' => 'app_admin_form_endpoint_notification_settings',
            'routeParameters' => $routeParameters,
        ]);

        return $menu;
    }
}
