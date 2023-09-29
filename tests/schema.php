<?php
declare(strict_types=1);

return [
    [
        'table' => 'after_trees',
        'columns' => [
            'id' => ['type' => 'integer'],
            'parent_id' => ['type' => 'integer'],
            'lft' => ['type' => 'integer'],
            'rght' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
];
