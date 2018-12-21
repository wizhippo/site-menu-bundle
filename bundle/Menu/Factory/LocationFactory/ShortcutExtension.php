<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ShortcutExtension implements ExtensionInterface
{
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var string
     */
    protected $contentTypeIdentifier;

    /**
     * @var string
     */
    protected $targetFieldIdentifier;

    /**
     * @var string
     */
    protected $urlFieldIdentifier;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        ContentTypeService $contentTypeService,
        string $contentTypeIdentifier,
        string $urlFieldIdentifier,
        ?string $targetFieldIdentifier = null
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->contentTypeService = $contentTypeService;
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->urlFieldIdentifier = $urlFieldIdentifier;
        $this->targetFieldIdentifier = $targetFieldIdentifier;
    }

    public function matches(Location $location): bool
    {
        $contentType = $this->contentTypeService->loadContentType($location->contentInfo->contentTypeId);

        return $contentType->identifier === $this->contentTypeIdentifier;
    }

    public function buildOptions(array $options): array
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $options['ezlocation'];
        $content = $location->getContent();

        if (empty($content->getField($this->urlFieldIdentifier)->value->link)) {
            return $options;
        }

        $urlValue = $content->getField($this->urlFieldIdentifier)->value;

        $uri = $urlValue->link;

        if (stripos($urlValue->link, 'http') !== 0) {
            $currentSiteAccess = $this->requestStack->getMasterRequest()->attributes->get('siteaccess');
            if ($currentSiteAccess->matcher instanceof URILexer) {
                $uri = $currentSiteAccess->matcher->analyseLink($uri);
            }
        }

        $options['uri'] = $this->urlGenerator->generate($location);

        if (!empty($urlValue->text)) {
            $options['linkAttributes']['title'] = $urlValue->text;
            $options['label'] = $urlValue->text;
        }

        return $options;
    }

    public function buildItem(ItemInterface $item, array $options): void
    {
        $item->setUri($options['uri']);
    }
}
