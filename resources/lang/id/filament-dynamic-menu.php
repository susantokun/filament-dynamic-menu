<?php

return [

    'cluster' => [
        'menu_settings' => [
            'title' => 'Pengaturan Menu',
            'navigation_label' => 'Pengaturan Menu',
            'group' => 'Pengaturan',
        ],
    ],

    'fields' => [
        'visible_for_roles' => 'Terlihat untuk Role',
        'target' => 'Resource / Page / Cluster',
        'url' => 'URL',
        'menu_group' => 'Grup Menu',
        'parent_item' => 'Item Induk',
        'required_permissions' => 'Izin yang Diperlukan',
    ],

    'table' => [
        'items' => 'Item',
        'group' => 'Grup',
    ],

    'help' => [
        'roles_group' => 'Filter role level grup. Item di dalamnya dicek secara individual terhadap izin. Kosongkan untuk menampilkan ke semua role.',
        'roles_item' => 'Filter role tambahan. Izin otomatis dicek untuk tipe Resource/Page/Cluster. Kosongkan untuk menampilkan ke semua role.',
        'permissions' => 'Untuk tipe Resource/Page/Cluster, izin sudah di-resolve otomatis. Atur ini hanya untuk pembatasan tambahan atau item Custom URL.',
    ],

    'menu_sidebar' => [
        'title' => 'Menu Sidebar',
        'collapsible_navigation_groups' => 'Grup Navigasi Dapat Ditutup',
        'collapsible_navigation_groups_help' => 'Izinkan grup navigasi di sidebar untuk ditutup (collapse).',
        'sidebar_collapsible_on_desktop' => 'Sidebar Dapat Ditutup di Desktop',
        'sidebar_collapsible_on_desktop_help' => 'Izinkan sidebar untuk ditutup di layar desktop.',
        'sidebar_fully_collapsible_on_desktop' => 'Sidebar Tersembunyi Penuh di Desktop',
        'sidebar_fully_collapsible_on_desktop_help' => 'Ketika ditutup, sidebar sepenuhnya tersembunyi, bukan hanya menampilkan ikon.',
    ],

    'actions' => [
        'save' => 'Simpan',
    ],

    'messages' => [
        'saved' => 'Pengaturan berhasil disimpan.',
    ],

];
