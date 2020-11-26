<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\HeaderActions;


use e2221\NetteGrid\Actions\BaseAction;
use e2221\NetteGrid\NetteGrid;

class HeaderAction extends BaseAction
{
    protected NetteGrid $netteGrid;

    public string $defaultClass='btn btn-xs';
    public string $class='btn-secondary';

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        $this->netteGrid = $netteGrid;
        parent::__construct($name, $title);
    }
}