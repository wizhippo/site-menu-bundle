Wizhippo Site Menu Bundle
=========================

Wizhippo Site Menu bundle is based on the menu from [Netgen Site Bundle](https://github.com/netgen/site-bundle) and is
an eZ Platform bundle that implements common menu features for websites.

Installation & license
----------------------

This package is not intended to be used standalone, but is instead designed to be used as
a base bundle for sites based on eZ Platform.

Licensed under [GPLv2](LICENSE)

Register Shortcut extension
---------------------------

~~~~
app.menu.factory.location.extension.shortcut:
    class: Wizhippo\Bundle\SiteMenuBundle\Menu\Factory\LocationFactory\ShortcutExtension
    arguments:
        - '@router'
        - '@request_stack'
        - '@ezpublish.api.service.content_type'
        - shortcut
        - url
        - target
    tags:
        - { name: wizhippo.menu.factory.location.extension }
~~~~

Register menu
-------------

~~~~
app.menu.main_menu:
    class: Knp\Menu\MenuItem
    factory: ['@Wizhippo\Bundle\SiteMenuBundle\Menu\RelationListMenuBuilder', createRelationListMenu]
    arguments:
        - main_menu
        - main_menu_relation
    tags:
        - { name: knp_menu.menu, alias: main_menu }
~~~~
