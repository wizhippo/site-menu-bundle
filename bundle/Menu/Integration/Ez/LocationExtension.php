<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Menu\Integration\Ez;

use eZ\Publish\API\Repository\Values\Content\Location;
use Knp\Menu\Factory\ExtensionInterface as KnpExtensionInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wizhippo\Bundle\SiteMenuBundle\Event\SiteMenuEvents;
use Wizhippo\Bundle\SiteMenuBundle\Event\Menu\LocationMenuItemEvent;
use Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory\ExtensionInterface;

/**
 * Factory able to use the Ez Location to build the url
 */
class LocationExtension implements KnpExtensionInterface
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

    public function buildOptions(array $options): array
    {
        if (!isset($options['ezlocation']) || !$options['ezlocation'] instanceof Location) {
            return $options;
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $options['ezlocation'];
        $options['extras']['ezlocation'] = $location;
        $options['label'] = $location->getContent()->getName();

        return $this->getExtension($location)
            ->buildOptions($options);
    }

    public function buildItem(ItemInterface $item, array $options): ItemInterface
    {
        if (!isset($options['ezlocation']) || !$options['ezlocation'] instanceof Location) {
            return $item;
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $options['ezlocation'];

        $extension = $this->getExtension($location)
            ->buildItem($item, $options);

        if (!strlen($item->getName())) {
            $item
                ->setName(md5($options['uri'] ?? ''));
        }

        $event = new LocationMenuItemEvent($item, $location);
        $this->eventDispatcher->dispatch(SiteMenuEvents::MENU_LOCATION_ITEM, $event);

        return $item;
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
