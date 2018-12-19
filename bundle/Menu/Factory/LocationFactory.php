<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Menu\Factory;

use eZ\Publish\API\Repository\Values\Content\Location;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wizhippo\Bundle\SiteMenuBundle\Event\Menu\LocationMenuItemEvent;
use Wizhippo\Bundle\SiteMenuBundle\Event\SiteMenuEvents;
use Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory\ExtensionInterface;

class LocationFactory implements FactoryInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory\ExtensionInterface
     */
    protected $fallbackExtension;

    /**
     * @var \Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory\ExtensionInterface[]
     */
    protected $extensions = [];

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ExtensionInterface $fallbackExtension,
        Iterable $extensions = []
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->fallbackExtension = $fallbackExtension;
        $this->extensions = $extensions;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createItem($name, array $options = []): ItemInterface
    {
        $menuItem = (new MenuItem($name, $this))->setExtra('translation_domain', false);

        if (!isset($options['ezlocation']) || !$options['ezlocation'] instanceof Location) {
            return $menuItem;
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $options['ezlocation'];

        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $location->getContent();

        $menuItem
            ->setLabel($content->getName())
            ->setExtra('ezlocation', $location);

        $extension = $this->getExtension($location);
        $extension->buildItem($menuItem, $location);

        $menuItem->setName(md5($menuItem->getUri() ?? ''));

        $event = new LocationMenuItemEvent($menuItem, $menuItem->getExtra('ezlocation'));
        $this->eventDispatcher->dispatch(SiteMenuEvents::MENU_LOCATION_ITEM, $event);

        return $menuItem;
    }

    /**
     * Returns the first extension that matches the provided location.
     *
     * If none match, fallback extension is returned.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory\ExtensionInterface
     */
    protected function getExtension(Location $location): ExtensionInterface
    {
        foreach ($this->extensions as $extension) {
            if ($extension->matches($location)) {
                return $extension;
            }
        }

        return $this->fallbackExtension;
    }
}
