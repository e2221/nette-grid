<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;

class RowActionCancel extends RowAction
{
    public string $class='btn-warning';

    public function __construct(NetteGrid $netteGrid, string $name='cancel', ?string $title = 'Cancel')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->setLinkCallback([$this, 'getLink']);
    }

    /**
     * Get link to edit
     * @param NetteGrid $netteGrid
     * @return string
     * @throws InvalidLinkException
     */
    public function getLink(NetteGrid $netteGrid): string
    {
        return $netteGrid->link('cancelEdit!');
    }
}