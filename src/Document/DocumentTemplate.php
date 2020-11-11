<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document;


use e2221\NetteGrid\Document\Templates\TableTemplate;
use e2221\NetteGrid\NetteGrid;
use Nette\SmartObject;

class DocumentTemplate
{
    use SmartObject;

    private NetteGrid $netteGrid;
    protected TableTemplate $tableTemplate;

    public function __construct(NetteGrid $netteGrid)
    {
        $this->netteGrid = $netteGrid;
        $this->tableTemplate = new TableTemplate();
    }

    /**
     * Get table template
     * @return TableTemplate
     */
    public function getTableTemplate(): TableTemplate
    {
        return $this->tableTemplate;
    }
}