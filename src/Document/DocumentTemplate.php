<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document;


use e2221\NetteGrid\Document\Templates\TableTemplate;
use e2221\NetteGrid\Document\Templates\TheadTemplate;
use e2221\NetteGrid\Document\Templates\TitlesRowTemplate;
use e2221\NetteGrid\NetteGrid;
use Nette\SmartObject;

class DocumentTemplate
{
    use SmartObject;

    private NetteGrid $netteGrid;
    protected TableTemplate $tableTemplate;
    protected TheadTemplate $theadTemplate;
    protected TitlesRowTemplate $theadTitlesRowTemplate;

    public function __construct(NetteGrid $netteGrid)
    {
        $this->netteGrid = $netteGrid;
        $this->tableTemplate = new TableTemplate();
        $this->theadTemplate = new TheadTemplate();
        $this->theadTitlesRowTemplate = new TitlesRowTemplate();
    }

    /**
     * Get thead titles row template <tr>
     * @return TitlesRowTemplate
     */
    public function getTheadTitlesRowTemplate(): TitlesRowTemplate
    {
        return $this->theadTitlesRowTemplate;
    }

    /**
     * Get thead template <thead>
     * @return TheadTemplate
     */
    public function getTheadTemplate(): TheadTemplate
    {
        return $this->theadTemplate;
    }

    /**
     * Get table template <table>
     * @return TableTemplate
     */
    public function getTableTemplate(): TableTemplate
    {
        return $this->tableTemplate;
    }
}