<?php
declare(strict_types=1);

namespace ADmad\Tree\TestCase\View\Helper;

use ADmad\Tree\View\Helper\TreeHelper;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Cake\View\View;

class TreeHelperTest extends TestCase
{
    /**
     * @var array
     */
    protected array $fixtures = [
        'plugin.ADmad\Tree.AfterTrees',
    ];

    protected Table $Table;

    protected TreeHelper $Tree;

    /**
     * Initial Tree
     *
     * - One
     * -- One-SubA
     * - Two
     * -- Two-SubA
     * --- Two-SubA-1
     * ---- Two-SubA-1-1
     * - Three
     * - Four
     * -- Four-SubA
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Tree = new TreeHelper(new View(null));
        $this->Table = $this->fetchTable('AfterTrees');
        $this->Table->addBehavior('Tree');

        //$this->Table->truncate();
        $connection = ConnectionManager::get('test');
        $sql = $this->Table->getSchema()->truncateSql($connection);
        foreach ($sql as $snippet) {
            $connection->execute($snippet);
        }
        //$this->Table->deleteAll(array());

        $data = [
            ['name' => 'One'],
            ['name' => 'Two'],
            ['name' => 'Three'],
            ['name' => 'Four'],

            ['name' => 'One-SubA', 'parent_id' => 1],
            ['name' => 'Two-SubA', 'parent_id' => 2],
            ['name' => 'Four-SubA', 'parent_id' => 4],

            ['name' => 'Two-SubA-1', 'parent_id' => 6],

            ['name' => 'Two-SubA-1-1', 'parent_id' => 8],
        ];
        foreach ($data as $row) {
            $row = new Entity($row);
            $this->Table->save($row);
        }
    }

    public function tearDown(): void
    {
        unset($this->Table);
        $this->getTableLocator()->clear();

        parent::tearDown();
    }

    public function testObject()
    {
        $this->assertInstanceOf(TreeHelper::class, $this->Tree);
    }

    /**
     * TreeHelperTest::testGenerate()
     *
     * @return void
     */
    public function testGenerate()
    {
        $tree = $this->Table->find('threaded')->toArray();

        $output = $this->Tree->generate($tree);

        $expected = <<<TEXT

<ul>
    <li>One
    <ul>
        <li>One-SubA</li>
    </ul>
    </li>
    <li>Two
    <ul>
        <li>Two-SubA
        <ul>
            <li>Two-SubA-1
            <ul>
                <li>Two-SubA-1-1</li>
            </ul>
            </li>
        </ul>
        </li>
    </ul>
    </li>
    <li>Three</li>
    <li>Four
    <ul>
        <li>Four-SubA</li>
    </ul>
    </li>
</ul>

TEXT;
        $this->assertTextEquals($expected, $output);
        $this->assertTrue(substr_count($output, '<ul>') === substr_count($output, '</ul>'));
        $this->assertTrue(substr_count($output, '<li>') === substr_count($output, '</li>'));
    }

    /**
     * TreeHelperTest::testGenerateWithFindAll()
     *
     * @return void
     */
    public function testGenerateWithFindAll()
    {
        $tree = $this->Table->find()
            ->orderByAsc('lft')
            ->toArray();

        $output = $this->Tree->generate($tree);
        $expected = <<<TEXT

<ul>
    <li>One
    <ul>
        <li>One-SubA</li>
    </ul>
    </li>
    <li>Two
    <ul>
        <li>Two-SubA
        <ul>
            <li>Two-SubA-1
            <ul>
                <li>Two-SubA-1-1</li>
            </ul>
            </li>
        </ul>
        </li>
    </ul>
    </li>
    <li>Three</li>
    <li>Four
    <ul>
        <li>Four-SubA</li>
    </ul>
    </li>
</ul>

TEXT;
        $output = str_replace(['    ', "\r", "\n"], '', $output);
        $expected = str_replace(['    ', "\r", "\n"], '', $expected);
        $this->assertTextEquals($expected, $output);
    }

    /**
     * TreeHelperTest::testGenerateWithDepth()
     *
     * @return void
     */
    public function testGenerateWithDepth()
    {
        $tree = $this->Table->find('threaded')->toArray();

        $output = $this->Tree->generate($tree, ['depth' => 1]);
        $expected = <<<TEXT

    <ul>
        <li>One
        <ul>
            <li>One-SubA</li>
        </ul>
        </li>
        <li>Two
        <ul>
            <li>Two-SubA
            <ul>
                <li>Two-SubA-1
                <ul>
                    <li>Two-SubA-1-1</li>
                </ul>
                </li>
            </ul>
            </li>
        </ul>
        </li>
        <li>Three</li>
        <li>Four
        <ul>
            <li>Four-SubA</li>
        </ul>
        </li>
    </ul>

TEXT;
        $this->assertTextEquals($expected, $output);
    }

    /**
     * TreeHelperTest::testGenerateWithSettings()
     *
     * @return void
     */
    public function testGenerateWithSettings()
    {
        $tree = $this->Table->find('threaded')->toArray();

        $output = $this->Tree->generate($tree, ['class' => 'navi', 'id' => 'main', 'type' => 'ol']);
        $expected = <<<TEXT

<ol class="navi" id="main">
    <li>One
    <ol>
        <li>One-SubA</li>
    </ol>
    </li>
    <li>Two
    <ol>
        <li>Two-SubA
        <ol>
            <li>Two-SubA-1
            <ol>
                <li>Two-SubA-1-1</li>
            </ol>
            </li>
        </ol>
        </li>
    </ol>
    </li>
    <li>Three</li>
    <li>Four
    <ol>
        <li>Four-SubA</li>
    </ol>
    </li>
</ol>

TEXT;
        $this->assertTextEquals($expected, $output);
    }

    /**
     * TreeHelperTest::testGenerateWithMaxDepth()
     *
     * @return void
     */
    public function testGenerateWithMaxDepth()
    {
        $tree = $this->Table->find('threaded')->toArray();

        $output = $this->Tree->generate($tree, ['maxDepth' => 2]);
        $expected = <<<TEXT

<ul>
    <li>One
    <ul>
        <li>One-SubA</li>
    </ul>
    </li>
    <li>Two
    <ul>
        <li>Two-SubA
        <ul>
            <li>Two-SubA-1</li>
        </ul>
        </li>
    </ul>
    </li>
    <li>Three</li>
    <li>Four
    <ul>
        <li>Four-SubA</li>
    </ul>
    </li>
</ul>

TEXT;
        $this->assertTextEquals($expected, $output);
    }

    /**
     * TreeHelperTest::testGenerateWithAutoPath()
     *
     * @return void
     */
    public function testGenerateWithAutoPath()
    {
        $tree = $this->Table->find('threaded')->toArray();

        $output = $this->Tree->generate($tree, ['autoPath' => [7, 10]]); // Two-SubA-1
        $expected = <<<TEXT

<ul>
    <li>One
    <ul>
        <li>One-SubA</li>
    </ul>
    </li>
    <li class="active">Two
    <ul>
        <li class="active">Two-SubA
        <ul>
            <li class="active">Two-SubA-1
            <ul>
                <li>Two-SubA-1-1</li>
            </ul>
            </li>
        </ul>
        </li>
    </ul>
    </li>
    <li>Three</li>
    <li>Four
    <ul>
        <li>Four-SubA</li>
    </ul>
    </li>
</ul>

TEXT;
        $this->assertTextEquals($expected, $output);

        $output = $this->Tree->generate($tree, ['autoPath' => [8, 9]]); // Two-SubA-1-1
        $expected = <<<TEXT

<ul>
    <li>One
    <ul>
        <li>One-SubA</li>
    </ul>
    </li>
    <li class="active">Two
    <ul>
        <li class="active">Two-SubA
        <ul>
            <li class="active">Two-SubA-1
            <ul>
                <li class="active">Two-SubA-1-1</li>
            </ul>
            </li>
        </ul>
        </li>
    </ul>
    </li>
    <li>Three</li>
    <li>Four
    <ul>
        <li>Four-SubA</li>
    </ul>
    </li>
</ul>

TEXT;
        $this->assertTextEquals($expected, $output);
    }

    /**
     * - One
     * -- One-SubA
     * - Two
     * -- Two-SubA
     * --- Two-SubA-1
     * ---- Two-SubA-1-1
     * -- Two-SubB
     * -- Two-SubC
     * - Three
     * - Four
     * -- Four-SubA
     *
     * @return void
     */
    public function testGenerateWithAutoPathAndHideUnrelated()
    {
        $data = [
            ['name' => 'Two-SubB', 'parent_id' => 2],
            ['name' => 'Two-SubC', 'parent_id' => 2],
        ];
        foreach ($data as $row) {
            $row = new Entity($row);
            $this->Table->save($row);
        }

        $tree = $this->Table->find('threaded')->toArray();
        $id = 6;
        $nodes = $this->Table->find('path', for: $id)->all();
        $path = $nodes->extract('id')->toArray();

        $output = $this->Tree->generate($tree, ['autoPath' => [6, 11], 'hideUnrelated' => true, 'treePath' => $path, 'callback' => [$this, 'myCallback']]); // Two-SubA

        $expected = <<<TEXT

<ul>
    <li>One</li>
    <li class="active">Two (active)
    <ul>
        <li class="active">Two-SubA (active)
        <ul>
            <li>Two-SubA-1</li>
        </ul>
        </li>
        <li>Two-SubB</li>
        <li>Two-SubC</li>
    </ul>
    </li>
    <li>Three</li>
    <li>Four</li>
</ul>

TEXT;
        $output = str_replace(['    '], '', $output);
        $expected = str_replace(['    '], '', $expected);
        $this->assertTextEquals($expected, $output);
    }

    /**
     * - One
     * -- One-SubA
     * - Two
     * -- Two-SubA
     * --- Two-SubA-1
     * ---- Two-SubA-1-1
     * -- Two-SubB
     * -- Two-SubC
     * - Three
     * - Four
     * -- Four-SubA
     *
     * @return void
     */
    public function testGenerateWithAutoPathAndHideUnrelatedAndSiblings()
    {
        $data = [
            ['name' => 'Two-SubB', 'parent_id' => 2],
            ['name' => 'Two-SubC', 'parent_id' => 2],
        ];
        foreach ($data as $row) {
            $row = new Entity($row);
            $this->Table->save($row);
        }

        $tree = $this->Table->find('threaded')->toArray();
        $id = 6;
        $nodes = $this->Table->find('path', for: $id)->all();
        $path = $nodes->extract('id')->toArray();

        $output = $this->Tree->generate($tree, [
            'autoPath' => [6, 11], 'hideUnrelated' => true, 'treePath' => $path,
            'callback' => [$this, 'myCallbackSiblings']]); // Two-SubA

        $expected = <<<TEXT

<ul>
    <li>One (sibling)</li>
    <li class="active">Two (active)
    <ul>
        <li class="active">Two-SubA (active)
        <ul>
            <li>Two-SubA-1</li>
        </ul>
        </li>
        <li>Two-SubB</li>
        <li>Two-SubC</li>
    </ul>
    </li>
    <li>Three (sibling)</li>
    <li>Four (sibling)</li>
</ul>

TEXT;
        $output = str_replace(['    ', "\r", "\n"], '', $output);
        $expected = str_replace(['    ', "\r", "\n"], '', $expected);
        $this->assertTextEquals($expected, $output);
    }

    public function myCallback($data)
    {
        if (!empty($data['data']['hide'])) {
            return;
        }

        return $data['data']['name'] . ($data['activePathElement'] ? ' (active)' : '');
    }

    public function myCallbackSiblings($data)
    {
        if (!empty($data['data']['hide'])) {
            return;
        }
        if ($data['depth'] == 0 && $data['isSibling']) {
            return $data['data']['name'] . ' (sibling)';
        }

        return $data['data']['name'] . ($data['activePathElement'] ? ' (active)' : '');
    }

    /**
     * TreeHelperTest::testGenerateProductive()
     *
     * @return void
     */
    public function testGenerateProductive()
    {
        $tree = $this->Table->find('threaded')->toArray();

        $output = $this->Tree->generate($tree, ['indent' => false]);
        $expected = '<ul><li>One<ul><li>One-SubA</li></ul></li><li>Two<ul><li>Two-SubA<ul><li>Two-SubA-1<ul><li>Two-SubA-1-1</li></ul></li></ul></li></ul></li><li>Three</li><li>Four<ul><li>Four-SubA</li></ul></li></ul>';

        $this->assertTextEquals($expected, $output);
    }
}
