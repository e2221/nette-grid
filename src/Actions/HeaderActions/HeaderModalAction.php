<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\HeaderActions;


use e2221\BootstrapComponents\Modal\Modal;
use e2221\NetteGrid\NetteGrid;
use Nette\ComponentModel\IComponent;

class HeaderModalAction extends HeaderAction
{
    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->netteGrid->addComponent(new Modal(), $name);
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this
            ->addDataAttribute('toggle', 'modal')
            ->addDataAttribute('target', sprintf('#%s', $this->getModal()->getModalId()))
            ->setLink('javascript:void(0);');
    }

    /**
     * Get modal control
     * @return Modal
     */
    public function getModal(): IComponent
    {
        return $this->netteGrid[$this->name];
    }
}