<?php
declare(strict_types=1);

namespace ADmad\Tree;

use Cake\Core\BasePlugin;

class TreePlugin extends BasePlugin
{
    /**
     * Do bootstrapping or not
     *
     * @var bool
     */
    protected bool $bootstrapEnabled = false;

    /**
     * Console middleware
     *
     * @var bool
     */
    protected bool $consoleEnabled = false;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected bool $routesEnabled = false;
}
