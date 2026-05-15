<?php

return [

    'cluster' => [
        'menu_settings' => [
            'title' => 'Menu Settings',
            'navigation_label' => 'Menu Settings',
            'group' => 'Settings',
        ],
    ],

    'fields' => [
        'visible_for_roles' => 'Visible for Roles',
        'target' => 'Resource / Page / Cluster',
        'url' => 'URL',
        'menu_group' => 'Menu Group',
        'parent_item' => 'Parent Item',
        'required_permissions' => 'Required Permissions',
    ],

    'table' => [
        'items' => 'Items',
        'group' => 'Group',
    ],

    'help' => [
        'roles_group' => 'Group-level role filter. Items inside are individually checked against permissions. Leave empty to show for all roles.',
        'roles_item' => 'Additional role filter. Permission is auto-checked for Resource/Page/Cluster types. Leave empty to show for all roles.',
        'permissions' => 'For Resource/Page/Cluster types, permissions are auto-resolved. Set this only for additional restrictions or Custom URL items.',
    ],

    'menu_sidebar' => [
        'title' => 'Menu Sidebar',
        'collapsible_navigation_groups' => 'Collapsible Navigation Groups',
        'collapsible_navigation_groups_help' => 'Allow navigation groups in the sidebar to be collapsed.',
        'sidebar_collapsible_on_desktop' => 'Sidebar Collapsible on Desktop',
        'sidebar_collapsible_on_desktop_help' => 'Allow the sidebar to be collapsed on desktop screens.',
        'sidebar_fully_collapsible_on_desktop' => 'Sidebar Fully Collapsible on Desktop',
        'sidebar_fully_collapsible_on_desktop_help' => 'When collapsed, the sidebar is fully hidden instead of showing only icons.',
    ],

    'actions' => [
        'save' => 'Save',
    ],

    'messages' => [
        'saved' => 'Settings saved successfully.',
    ],

];
