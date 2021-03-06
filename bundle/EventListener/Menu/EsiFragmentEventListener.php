<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\EventListener\Menu;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Wizhippo\Bundle\SiteMenuBundle\Event\Menu\LocationMenuItemEvent;
use Wizhippo\Bundle\SiteMenuBundle\Event\SiteMenuEvents;

class EsiFragmentEventListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $fragmentPath;

    public function __construct(RequestStack $requestStack, string $fragmentPath)
    {
        $this->requestStack = $requestStack;
        $this->fragmentPath = $fragmentPath;
    }

    public static function getSubscribedEvents(): array
    {
        return [SiteMenuEvents::MENU_LOCATION_ITEM => 'onMenuItemBuild'];
    }

    public function onMenuItemBuild(LocationMenuItemEvent $event): void
    {
        $request = $this->requestStack->getMasterRequest();
        if ($this->fragmentPath !== rawurldecode($request->getPathInfo())) {
            return;
        }

        if (!$request->attributes->has('activeItemId')) {
            return;
        }

        if ($event->getLocation()->id === (int) $request->attributes->get('activeItemId')) {
            $event->getItem()->setCurrent(true);
        }
    }
}
