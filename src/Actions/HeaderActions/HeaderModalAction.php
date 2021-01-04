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
        $this->netteGrid->onAnchor[] = function(){
            $this->netteGrid[$this->name]->setModalId(sprintf('_gridModal_%s_%s', $this->name, $this->netteGrid->getUniqueId()));
        };
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this
            ->addDataAttribute('header-modal')
            ->addDataAttribute('modal-id', sprintf('#_gridModal_%s_%s', $this->name, $this->netteGrid->getUniqueId()))
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