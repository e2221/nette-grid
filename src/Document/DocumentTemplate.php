<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document;


use e2221\NetteGrid\Document\Templates\Cols\EmptyDataColTemplate;
use e2221\NetteGrid\Document\Templates\DataRowTemplate;
use e2221\NetteGrid\Document\Templates\EmptyDataRowTemplate;
use e2221\NetteGrid\Document\Templates\TableTemplate;
use e2221\NetteGrid\Document\Templates\TbodyTemplate;
use e2221\NetteGrid\Document\Templates\TheadTemplate;
use e2221\NetteGrid\Document\Templates\TitlesRowTemplate;
use e2221\NetteGrid\NetteGrid;
use Nette\SmartObject;

class DocumentTemplate
{
    use SmartObject;

    /** @var null|callable function($row, DataRowTemplate $dataRowTemplate) */
    protected $dataRowCallback=null;

    private NetteGrid $netteGrid;
    protected TableTemplate $tableTemplate;
    protected TheadTemplate $theadTemplate;
    protected TitlesRowTemplate $theadTitlesRowTemplate;
    protected EmptyDataColTemplate $emptyDataColTemplate;
    protected EmptyDataRowTemplate $emptyDataRowTemplate;
    protected ?DataRowTemplate $dataRowTemplate=null;
    protected TbodyTemplate $tbodyTemplate;

    public function __construct(NetteGrid $netteGrid)
    {
        $this->netteGrid = $netteGrid;
        $this->tableTemplate = new TableTemplate();
        $this->theadTemplate = new TheadTemplate();
        $this->theadTitlesRowTemplate = new TitlesRowTemplate();
        $this->tbodyTemplate = new TbodyTemplate();
        $this->emptyDataRowTemplate = new EmptyDataRowTemplate();
        $this->emptyDataColTemplate = new EmptyDataColTemplate();
    }

    /**
     * Set data row callback
     * @param callable|null $dataRowCallback
     * @return DocumentTemplate
     */
    public function setDataRowCallback(?callable $dataRowCallback): self
    {
        $this->dataRowCallback = $dataRowCallback;
        return $this;
    }

    /**
     * Get data row template (only for style all rows - don´t combine with setDataRowCallback()!!!)
     * @return DataRowTemplate
     */
    public function getDataRowTemplate(): DataRowTemplate
    {
        return $this->dataRowTemplate = new DataRowTemplate();
    }

    /**
     * @internal
     *
     * Get data row template for rendering - internal
     * @param mixed $row
     * @return DataRowTemplate
     */
    public function getDataRowTemplateForRendering($row): DataRowTemplate
    {
        $template = $this->dataRowTemplate instanceof DataRowTemplate ? $this->dataRowTemplate : new DataRowTemplate();
        if(is_callable($this->dataRowCallback))
        {
            $fn = $this->dataRowCallback;
            $template = $fn($row, $template);
        }
        return $this->dataRowTemplate = $template;
    }

    /**
     * Get tbody template <tbody>
     * @return TbodyTemplate
     */
    public function getTbodyTemplate(): TbodyTemplate
    {
        return $this->tbodyTemplate;
    }

    /**
     * Get empty data col template <td>
     * @return EmptyDataColTemplate
     */
    public function getEmptyDataColTemplate(): EmptyDataColTemplate
    {
        return $this->emptyDataColTemplate
            ->setAttribute('colspan', (string)$this->netteGrid->getCountOfPrintableColumns());
    }

    /**
     * Get empty data row template <tr>
     * @return EmptyDataRowTemplate
     */
    public function getEmptyDataRowTemplate(): EmptyDataRowTemplate
    {
        return $this->emptyDataRowTemplate;
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