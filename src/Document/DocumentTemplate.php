<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document;

use e2221\NetteGrid\Actions\RowAction\RowActionCancel;
use e2221\NetteGrid\Actions\RowAction\RowActionEdit;
use e2221\NetteGrid\Actions\RowAction\RowActionSave;
use e2221\NetteGrid\Document\Templates\Cols\DataActionsColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\EmptyDataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\HeaderActionsColTemplate;
use e2221\NetteGrid\Document\Templates\DataRowTemplate;
use e2221\NetteGrid\Document\Templates\EmptyDataRowTemplate;
use e2221\NetteGrid\Document\Templates\HeadFilterRowTemplate;
use e2221\NetteGrid\Document\Templates\TableTemplate;
use e2221\NetteGrid\Document\Templates\TbodyTemplate;
use e2221\NetteGrid\Document\Templates\TheadTemplate;
use e2221\NetteGrid\Document\Templates\TitlesRowTemplate;
use e2221\NetteGrid\Document\Templates\WholeDocumentTemplate;
use e2221\NetteGrid\NetteGrid;
use e2221\utils\Html\BaseElement;
use Nette\SmartObject;

class DocumentTemplate
{
    use SmartObject;

    /** @var null|callable function(DataRowTemplate $template, $row) */
    protected $dataRowCallback=null;

    private NetteGrid $netteGrid;
    protected TableTemplate $tableTemplate;
    protected TheadTemplate $theadTemplate;
    protected TitlesRowTemplate $theadTitlesRowTemplate;
    protected EmptyDataColTemplate $emptyDataColTemplate;
    protected EmptyDataRowTemplate $emptyDataRowTemplate;
    protected ?DataRowTemplate $dataRowTemplate=null;
    protected TbodyTemplate $tbodyTemplate;
    protected HeadFilterRowTemplate $headFilterRowTemplate;
    protected HeaderActionsColTemplate $headerActionsColTemplate;
    protected WholeDocumentTemplate $wholeDocumentTemplate;
    protected DataActionsColTemplate $dataActionsColTemplate;
    protected ?BaseElement $emptyData=null;
    protected RowActionEdit $rowActionEdit;
    protected RowActionCancel $rowActionCancel;
    protected RowActionSave $rowActionSave;

    public function __construct(NetteGrid $netteGrid)
    {
        $this->netteGrid = $netteGrid;
        $this->wholeDocumentTemplate = new WholeDocumentTemplate();
        $this->tableTemplate = new TableTemplate();
        $this->theadTemplate = new TheadTemplate();
        $this->theadTitlesRowTemplate = new TitlesRowTemplate();
        $this->tbodyTemplate = new TbodyTemplate();
        $this->emptyDataRowTemplate = new EmptyDataRowTemplate();
        $this->emptyDataColTemplate = new EmptyDataColTemplate($netteGrid);
        $this->headFilterRowTemplate = new HeadFilterRowTemplate();
        $this->headerActionsColTemplate = new HeaderActionsColTemplate();
        $this->dataActionsColTemplate = new DataActionsColTemplate();
        $this->rowActionEdit = new RowActionEdit($netteGrid);
        $this->rowActionCancel = new RowActionCancel($netteGrid);
        $this->rowActionSave = new RowActionSave($netteGrid);
    }

    /**
     * Get row action save
     * @return RowActionSave
     */
    public function getRowActionSave(): RowActionSave
    {
        return $this->rowActionSave;
    }

    /**
     * Ger row action cancel
     * @return RowActionCancel
     */
    public function getRowActionCancel(): RowActionCancel
    {
        return $this->rowActionCancel;
    }

    /**
     * Get row action edit
     * @return RowActionEdit
     */
    public function getRowActionEdit(): RowActionEdit
    {
        return $this->rowActionEdit;
    }

    /**
     * Get data actions column template
     * @return DataActionsColTemplate
     */
    public function getDataActionsColTemplate(): DataActionsColTemplate
    {
        return $this->dataActionsColTemplate;
    }

    /**
     * Get empty data element
     * @return BaseElement
     */
    public function getEmptyData(): BaseElement
    {
        return is_null($this->emptyData) ? BaseElement::getStatic('', [], 'Empty data. ') : $this->emptyData;
    }

    /**
     * Get whole document template
     * @return WholeDocumentTemplate
     */
    public function getWholeDocumentTemplate(): WholeDocumentTemplate
    {
        $this->wholeDocumentTemplate
            ->addDataAttribute('grid-name', $this->netteGrid->getUniqueId())
            ->setDefaultClass('nette-grid')
            ->addHtmlAttribute('id', $this->netteGrid->getMainSnippetId());
        return $this->wholeDocumentTemplate;
    }

    /**
     * Get actions col template
     * @return HeaderActionsColTemplate
     */
    public function getHeaderActionsColTemplate(): HeaderActionsColTemplate
    {
        return $this->headerActionsColTemplate;
    }

    /**
     * Get head filter row template
     * @return HeadFilterRowTemplate
     */
    public function getHeadFilterRowTemplate(): HeadFilterRowTemplate
    {
        return $this->headFilterRowTemplate;
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
     * Get data row template (only for style all rows
     * @return DataRowTemplate
     */
    public function getDataRowTemplate(): DataRowTemplate
    {
        return $this->dataRowTemplate = new DataRowTemplate($this);
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
        $template = clone($this->dataRowTemplate instanceof DataRowTemplate ? $this->dataRowTemplate : new DataRowTemplate($this));
        if(is_callable($this->dataRowCallback))
        {
            $fn = $this->dataRowCallback;
            $edited = $fn($template, $row);
            $template = $edited instanceof DataRowTemplate ? $edited : $template;
        }
        return $template;
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
            ->addHtmlAttribute('colspan', (string)$this->netteGrid->getCountOfPrintableColumns())
            ->addElement($this->getEmptyData());
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