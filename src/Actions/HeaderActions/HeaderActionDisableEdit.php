<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\HeaderActions;


use e2221\NetteGrid\NetteGrid;

class HeaderActionDisableEdit extends HeaderAction
{
    /** @var bool [true = enabled, false = disabled] */
    protected bool $defaultMode=false;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addIconElement('fas fa-pen', [], true);
        $this->addDataAttribute('history', 'false');
        $this->addDataAttribute('transition', 'false');

        $this->netteGrid->onAnchor[] = function(){
            if($this->netteGrid->editEnabled === true)
            {
                $this->addTitle('Disable edit');
                $this->setLink($this->netteGrid->link('redrawGrid', ['editEnabled' => false]));
            }else{
                $this->addTitle('Enable edit');
                $this->setLink($this->netteGrid->link('redrawGrid', ['editEnabled' => true]));
            }
        };
        $this->prepareElement();
    }

    /**
     * Set default mode
     * @param bool $mode [true = enabled, false = disabled]
     * @return HeaderActionDisableEdit
     */
    public function setDefaultMode(bool $mode=false): self
    {
        $this->defaultMode = $mode;
        $this->netteGrid->editEnabled = $mode;
        $this->prepareElement();
        return $this;
    }

    public function beforeRender(): void
    {
        if($this->netteGrid->editEnabled === null)
        {
            if($this->defaultMode === true)
            {
                $this->netteGrid->editEnabled = true;
                $this->setClass('btn-outline-primary');
            }else if($this->defaultMode === false)
            {
                $this->netteGrid->editEnabled = false;
                $this->setClass('btn-primary');
            }
        }else if ($this->netteGrid->editEnabled === true)
        {
            $this->setClass('btn-outline-primary');
        }else{
            $this->setClass('btn-primary');
        }
    }
}