<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;

class RowActionEdit extends RowAction
{
    public function __construct(NetteGrid $netteGrid, string $name='edit', ?string $title = 'Edit')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->setLinkCallback([$this, 'getLink']);
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