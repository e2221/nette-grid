<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;

class RowActionCopyToAdd extends RowAction
{
    public function __construct(NetteGrid $netteGrid, string $name='copyToAdd', ?string $title = 'Copy to add form')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addIconElement('fas fa-copy', [], true);

        $this->setOnClickCallback(function(NetteGrid $netteGrid, $row){
            $netteGrid->inlineAdd = true;
            foreach($netteGrid->getColumns(true) as $column)
            {
                if($column->isAddable() === false)
                    continue;
                $column->getAddInput()->setDefaultValue($column->getEditCellValue($row));
            }
            $netteGrid->reloadItems();
        });
    }
}