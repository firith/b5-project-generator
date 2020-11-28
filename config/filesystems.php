<?php

return [
    'default' => 'local',
    'disks' => [
        'cwd' => [
            'driver' => 'local',
            'root' => getcwd(),
        ],
    ],
];
