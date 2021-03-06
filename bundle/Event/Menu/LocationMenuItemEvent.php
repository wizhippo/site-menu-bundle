<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Event\Menu;

use eZ\Publish\API\Repository\Values\Content\Location;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered when a menu item is build using the location menu factory.
 */
class LocationMenuItemEvent extends Event
{
    /**
     * @var \Knp\Menu\ItemInterface
     */
    protected $item;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    public function __construct(ItemInterface $item, Location $location)
    {
        $this->item = $item;
        $this->location = $location;
    }

    /**
     * Returns the item which was built.
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * Returns the eZ Publish location for which the menu item was built.
     */
    public function getLocation(): Location
    {
        return $this->location;
    }
}
