<?php

return [
    'items' => [
        'managers' => [
            'caption' => 'admin_managers::manage.managers',
            'section' => 'system/settings',
            'route' => 'admin_managers::managers_list',
            'permission' => 'admin_managers::list'
        ],
    ],

    'routes' => [
        'admin_managers::managers_list' => [
            'route' => '/managers',
            'loader' => 'manage/api/module/admin_managers/managers_list',
        ],
        'admin_managers::managers_add' => [
            'route' => '/managers/add',
            'loader' => 'manage/api/module/admin_managers/manager_edit/add',
        ],
        'admin_managers::managers_edit' => [
            'route' => '/managers/edit/:id',
            'loader' => 'manage/api/module/admin_managers/manager_edit/edit',
        ],
    ],
];