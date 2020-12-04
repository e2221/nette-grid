<?php
declare(strict_types=1);

namespace e2221\NetteGrid\GlobalActions;


use e2221\NetteGrid\NetteGrid;
use Nette\Forms\Container;
use Nette\SmartObject;

class MultipleFilter
{
    use SmartObject;

    protected string $name;
    protected NetteGrid $netteGrid;
    protected Container $container;

    public function __construct(NetteGrid $netteGrid, string $name)
    {
        $this->name = $name;
        $this->netteGrid = $netteGrid;
        $this->container = $this->netteGrid->getMultipleFilter()->addContainer($name);
    }

    /**
     * Get container
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}