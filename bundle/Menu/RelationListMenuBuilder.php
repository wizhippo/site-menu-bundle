<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Menu;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Wizhippo\Bundle\SiteMenuBundle\Event\Menu\ConfigureMenuEvent;
use Wizhippo\Bundle\SiteMenuBundle\Event\SiteMenuEvents;

class RelationListMenuBuilder
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \Psr\Log\NullLogger
     */
    private $logger;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FactoryInterface $factory,
        LocationService $locationService,
        ContentService $contentService,
        ConfigResolverInterface $configResolver,
        LoggerInterface $logger = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->factory = $factory;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->configResolver = $configResolver;
        $this->logger = $logger ?: new NullLogger();
    }

    public function createRelationListMenu(string $name, string $relationFieldIdentifier, $locationId = null): ItemInterface
    {
        $locationId = $locationId ?? $this->configResolver->getParameter('content.tree_root.location_id');
        $location = $this->locationService->loadLocation($locationId);
        $content = $location->getContent();

        $menu = $this->factory->createItem($name, ['ezlocation' => $location]);

        if (null !== ($field = $content->getField($relationFieldIdentifier))) {
            foreach ($field->value->destinationContentIds as $contentId) {
                try {
                    $contentInfo = $this->contentService->loadContentInfo($contentId);
                    $location = $this->locationService->loadLocation($contentInfo->mainLocationId);
                } catch (Throwable $t) {
                    $this->logger->error($t->getMessage());

                    continue;
                }

                if ($location->invisible) {
                    continue;
                }

                $menu->addChild(null, ['ezlocation' => $location]);
            }
        }

        $this->eventDispatcher->dispatch(
            SiteMenuEvents::MENU_CONFIGURE,
            new ConfigureMenuEvent($this->factory, $menu)
        );

        return $menu;
    }
}
