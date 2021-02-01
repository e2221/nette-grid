<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;

class RowActionEdit extends RowAction
{
    public string $class = 'btn-secondary';
    protected bool $couldHaveMultiAction=false;

    public function __construct(NetteGrid $netteGrid, string $name='edit', ?string $title = 'Edit')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->setLinkCallback([$this, 'getLink']);
        $this->addSpanElement('fa fa-pencil fa fa-pencil-al', [], true);
        $this->addDataAttribute('history', 'false');
        $this->addDataAttribute('transition', 'false');
        $netteGrid->onAnchor[] = function(NetteGrid $netteGrid)
        {
            $this->addDataAttribute('edit');
        };

    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        if($this->netteGrid->editKey && $this->netteGrid->editMode === true)
            $this->addClass('disabled');
    }

    /**
     * Get link to edit
     * @param NetteGrid $netteGrid
     * @param mixed $row
     * @param string|int $primary
     * @return string
     * @throws InvalidLinkException
     */
    public function getLink(NetteGrid $netteGrid, $row, $primary): string
    {
        return $netteGrid->link('edit!', $primary);
    }
}