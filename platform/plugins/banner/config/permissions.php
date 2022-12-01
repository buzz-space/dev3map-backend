<?php

return [
    [
        'name' => 'Banners',
        'flag' => 'banner.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'banner.create',
        'parent_flag' => 'banner.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'banner.edit',
        'parent_flag' => 'banner.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'banner.destroy',
        'parent_flag' => 'banner.index',
    ],
];
