<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;

class RowActionItemDetail extends RowAction
{
    public string $class = 'btn-secondary';
    protected bool $couldHaveMultiAction=false;

    public function __construct(NetteGrid $netteGrid, string $name='__itemDetail', ?string $title='Show detail')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addSpanElement('fa fa-eye', [], true);
        $this->addDataAttribute('item-detail-toggle');
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this
            ->setLink('#')
            ->addDataAttribute('id', $this->primary)
            ->addDataAttribute('link', $this->netteGrid->link('itemDetail!', $this->primary));
    }
}