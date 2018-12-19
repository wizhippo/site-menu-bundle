<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory;

use eZ\Publish\API\Repository\Values\Content\Location;
use Knp\Menu\ItemInterface;

interface ExtensionInterface
{
    /**
     * Returns if the extension can be used to configure the item based on provided location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return bool
     */
    public function matches(Location $location): bool;

    /**
     * Configures the item with the passed options.
     *
     * @param \Knp\Menu\ItemInterface $item
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function buildItem(ItemInterface $item, Location $location): void;
}
