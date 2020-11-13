<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;


use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\Document\Templates\BaseTemplate;

class DataColTemplate extends BaseTemplate
{
    protected ?string $elName='td';

    private IColumn $column;

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