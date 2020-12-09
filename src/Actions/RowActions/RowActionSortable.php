<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;

class RowActionSortable extends RowAction
{
    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addDataAttribute('sort-handler');
        $this->setLink('javascript:void(0);');
    }
}