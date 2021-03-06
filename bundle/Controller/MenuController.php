<?php

declare(strict_types=1);

namespace Wizhippo\Bundle\SiteMenuBundle\Controller;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use FOS\HttpCacheBundle\Handler\TagHandler;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\Renderer\RendererProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    /**
     * @var \Knp\Menu\Provider\MenuProviderInterface
     */
    protected $menuProvider;

    /**
     * @var \Knp\Menu\Renderer\RendererProviderInterface
     */
    protected $menuRenderer;

    /**
     * @var \FOS\HttpCacheBundle\Handler\TagHandler
     */
    protected $tagHandler;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        MenuProviderInterface $menuProvider,
        RendererProviderInterface $menuRenderer,
        TagHandler $tagHandler
    ) {
        $this->configResolver = $configResolver;
        $this->menuProvider = $menuProvider;
        $this->menuRenderer = $menuRenderer;
        $this->tagHandler = $tagHandler;
    }

    /**
     * Renders the menu with provided name.
     */
    public function renderMenuAction(Request $request, string $menuName): Response
    {
        $menu = $this->menuProvider->get($menuName);
        $menu->setChildrenAttribute('class', $request->attributes->get('ulClass') ?: 'nav navbar-nav');

        $menuOptions = [
            'firstClass' => $request->attributes->get('firstClass') ?: 'first',
            'currentClass' => $request->attributes->get('currentClass') ?: 'active',
            'lastClass' => $request->attributes->get('lastClass') ?: 'last',
            'template' => $this->configResolver->getParameter('template.menu', 'wizhippo'),
        ];

        if ($request->attributes->has('template')) {
            $menuOptions['template'] = $request->attributes->get('template');
        }

        $response = new Response();

        $menuLocationId = $menu->getAttribute('location-id');
        if (!empty($menuLocationId)) {
            $this->tagHandler->addTags(['location-' . $menuLocationId]);
        }

        $response->setContent($this->menuRenderer->get()->render($menu, $menuOptions));

        return $response;
    }
}
