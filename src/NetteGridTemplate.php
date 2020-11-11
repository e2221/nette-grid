<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;


/**
 * @method bool isLinkCurrent(string $destination = null, $args = [])
 * @method bool isModuleCurrent(string $module)
 */
class NetteGridTemplate extends Template
{
    public array $flashes;
    public NetteGrid $control;
    public Presenter $presenter;
    public string $uniqueID;
    public Document\Templates\TableTemplate $tableTemplate;
}