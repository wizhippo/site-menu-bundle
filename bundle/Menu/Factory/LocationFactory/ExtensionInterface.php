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
     * Builds the full option array used to configure the item.
     *
     * @param array $options The options processed by the previous extensions
     *
     * @return array
     */
    public function buildOptions(array $options);

    /**
     * Configures the item with the passed options
     *
     * @param ItemInterface $item
     * @param array         $options
     */
    public function buildItem(ItemInterface $item, array $options);
}
