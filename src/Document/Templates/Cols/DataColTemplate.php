<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;


use e2221\NetteGrid\Column\Column;
use e2221\NetteGrid\Document\Templates\BaseTemplate;

class DataColTemplate extends BaseTemplate
{
    protected ?string $elName='td';

    private Column $column;

    public function __construct(Column $column)
    {
        parent::__construct();
        $this->column = $column;
    }

    /**
     * Go back to column
     * @return Column
     */
    public function endTemplate(): Column
    {
        return $this->column;
    }
}