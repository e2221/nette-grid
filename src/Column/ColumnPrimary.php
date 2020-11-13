<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Column;


use e2221\NetteGrid\NetteGrid;

class ColumnPrimary extends Column
{
    /**
     * ColumnPrimary constructor.
     * @param NetteGrid $netteGrid
     * @param string $name
     * @param string|null $label
     */
    public function __construct(NetteGrid $netteGrid, string $name='id', ?string $label = 'ID')
    {
        parent::__construct($netteGrid, $name, $label);
        $this->setHidden();
        $this->setAsPrimaryColumn();
    }

}