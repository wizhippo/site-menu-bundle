<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Menu\Voter;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocationPathVoter implements VoterInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService, RequestStack $requestStack)
    {
        $this->locationService = $locationService;
        $this->requestStack = $requestStack;
    }

    /**
     * Checks whether an item is current.
     *
     * If the voter is not able to determine a result,
     * it should return null to let other voters do the job.
     *
     * This voter specifically marks the item as current if it is in
     * path of the currently displayed item. This takes care of marking
     * items in menus of arbitrary depths.
     */
    public function matchItem(ItemInterface $item): ?bool
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        if (!$masterRequest instanceof Request) {
            return null;
        }

        if (!$item->getExtra('ezlocation') instanceof Location) {
            return null;
        }

        try {
            $location = $this->locationService->loadLocation($masterRequest->attributes->get('activeItemId', null));
        } catch (NotFoundException $e) {
            return null;
        } catch (UnauthorizedException $e) {
            return null;
        }

        $locationPath = array_map('intval', $location->path);

        if (!in_array($item->getExtra('ezlocation')->id, $locationPath, true)) {
            return null;
        }

        return true;
    }
}
