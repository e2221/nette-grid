<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;

class MultiActionItem extends RowAction
{
    public string $defaultClass='dropdown-item dropdown-smaller-item';
    public string $class='';
    protected bool $couldHaveMultiAction = false;

    protected RowAction $rowAction;

    public function __construct(NetteGrid $netteGrid, RowAction $rowAction, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->setTextContent((string)$title);
        $this->rowAction = $rowAction;
        $this->setLink('#');
    }

    /**
     * Back to MultiActon parent
     * @return RowAction
     */
    public function endItem(): RowAction
    {
        return $this->rowAction;
    }


}