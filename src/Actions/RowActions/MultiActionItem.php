<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;
use Nette\Utils\Html;

class MultiActionItem extends RowAction
{
    public string $defaultClass='dropdown-item';
    public string $class='';

    protected RowAction $rowAction;

    public function __construct(NetteGrid $netteGrid, RowAction $rowAction, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->setTextContent($title);
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