<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


use e2221\NetteGrid\Column\IColumn;

class BaseColTemplate extends BaseTemplate
{
    protected IColumn $column;

    public function __construct(IColumn $column)
    {
        parent::__construct();
        $this->column = $column;
    }

    /**
     * Go back to column
     * @return IColumn
     */
    public function endTemplate(): IColumn
    {
        return $this->column;
    }

}