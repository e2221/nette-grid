<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;

class RowActionEdit extends RowAction
{
    protected ?string $spanClass = 'fa fa-pencil fa fa-pencil-alt';
    public string $class = 'btn btn-xs btn-secondary';
    public string $defaultClass = 'datagrid-edit-button';

    public function __construct(NetteGrid $netteGrid, string $name='edit', ?string $title = 'Edit')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->setLinkCallback([$this, 'getLink']);
        $this->addSpanElement('fa fa-pencil fa fa-pencil-al');
        $this->setDefaultClass('datagrid-edit-button');
        $this->setClass('btn btn-xs btn-secondary');
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
        return $netteGrid->link('edit!', ['editKey' => $primary, 'editMode' => true]);
    }
}