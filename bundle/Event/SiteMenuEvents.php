<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Event;

final class SiteMenuEvents
{
    /**
     * The MENU_LOCATION_ITEM event occurs when a menu item is build using location menu factory.
     *
     * The event listener method receives a \Wizhippo\Bundle\SiteMenuBundle\Event\Menu\LocationMenuItemEvent
     */
    public const MENU_LOCATION_ITEM = 'wizhippo.events.menu.location_item';

    /**
     * The MENU_CONFIGURE event occurs when an menu has been built.
     *
     * The event listener method receives a \\Wizhippo\Bundle\SiteMenuBundle\Event\Menu\MenuConfigureEvent
     */
    public const MENU_CONFIGURE = 'wizhippo.events.menu.configure';
}
