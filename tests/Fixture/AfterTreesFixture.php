<?php
declare(strict_types=1);

namespace ADmad\Tree\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AfterTreeFixture class
 */
class AfterTreesFixture extends TestFixture
{
    /**
     * records property
     *
     * @var array
     */
    public array $records = [
        ['parent_id' => null, 'lft' => 1, 'rght' => 2, 'name' => 'One'],
        ['parent_id' => null, 'lft' => 3, 'rght' => 4, 'name' => 'Two'],
        ['parent_id' => null, 'lft' => 5, 'rght' => 6, 'name' => 'Three'],
        ['parent_id' => null, 'lft' => 7, 'rght' => 12, 'name' => 'Four'],
        ['parent_id' => null, 'lft' => 8, 'rght' => 9, 'name' => 'Five'],
        ['parent_id' => null, 'lft' => 10, 'rght' => 11, 'name' => 'Six'],
        ['parent_id' => null, 'lft' => 13, 'rght' => 14, 'name' => 'Seven'],
    ];
}
