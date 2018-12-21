<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory;

use eZ\Publish\API\Repository\Values\Content\Location;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FallbackExtension implements ExtensionInterface
{
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function matches(Location $location): bool
    {
        return true;
    }

    public function buildOptions(array $options): array
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $options['ezlocation'];

        $options['uri'] = $this->urlGenerator->generate($location);
        $options['attributes']['id'] = 'menu-item-location-id-' . $location->id;

        return $options;
    }

    public function buildItem(ItemInterface $item, array $options): void
    {
        $item->setUri($options['uri']);
    }
}
