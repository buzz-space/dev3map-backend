<?php

return [
    [
        'name' => 'Statistics',
        'flag' => 'statistic.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'statistic.create',
        'parent_flag' => 'statistic.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'statistic.edit',
        'parent_flag' => 'statistic.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'statistic.destroy',
        'parent_flag' => 'statistic.index',
    ],
];
