<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document;

use e2221\NetteGrid\Actions\RowAction\RowActionCancel;
use e2221\NetteGrid\Actions\RowAction\RowActionEdit;
use e2221\NetteGrid\Actions\RowAction\RowActionInlineCancel;
use e2221\NetteGrid\Actions\RowAction\RowActionInlineSave;
use e2221\NetteGrid\Actions\RowAction\RowActionSave;
use e2221\NetteGrid\Document\Templates\Cols\DataActionsColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\EmptyDataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\HeaderActionsColTemplate;
use e2221\NetteGrid\Document\Templates\DataRowTemplate;
use e2221\NetteGrid\Document\Templates\EmptyDataRowTemplate;
use e2221\NetteGrid\Document\Templates\HeadFilterRowTemplate;
use e2221\NetteGrid\Document\Templates\PreGlobalActionSelectionTemplate;
use e2221\NetteGrid\Document\Templates\TableTemplate;
use e2221\NetteGrid\Document\Templates\TbodyTemplate;
use e2221\NetteGrid\Document\Templates\TfootContentTemplate;
use e2221\NetteGrid\Document\Templates\TfootTemplate;
use e2221\NetteGrid\Document\Templates\TheadTemplate;
use e2221\NetteGrid\Document\Templates\TitlesRowTemplate;
use e2221\NetteGrid\Document\Templates\TitleTemplate;
use e2221\NetteGrid\Document\Templates\TitleWrapperTemplate;
use e2221\NetteGrid\Document\Templates\TopActionsWrapperTemplate;
use e2221\NetteGrid\Document\Templates\TopRowTemplate;
use e2221\NetteGrid\Document\Templates\WholeDocumentTemplate;
use e2221\NetteGrid\NetteGrid;
use e2221\NetteGrid\Reflection\ReflectionHelper;
use e2221\utils\Html\BaseElement;
use Nette\SmartObject;

class DocumentTemplate
{
    use SmartObject;

    const
        SM = 'sm',
        MD = 'md',
        LG = 'lg',
        XL = 'xl',
        CONFIRMATION_BASE = 'baseConfirmation',
        CONFIRMATION_NITTRO = 'nittroConfirmation';

    /** @var null|callable function(DataRowTemplate $template, $row): DataRowTemplate */
    protected $dataRowCallback=null;

    /** @var bool Hidden all in tag <thead> */
    public bool $hiddenHeader=false;

    /** @var string Confirmation type */
    public string $defaultConfirmationStyle = self::CONFIRMATION_BASE;

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
    protected RowActionInlineCancel $rowActionInlineAddCancel;
    protected RowActionInlineSave $rowActionInlineAddSave;
    protected TfootTemplate $tfootTemplate;
    protected TfootContentTemplate $tfootContentTemplate;
    protected TopRowTemplate $topRowTemplate;
    protected TopActionsWrapperTemplate $topActionsWrapperTemplate;
    protected TitleWrapperTemplate $titleWrapperTemplate;
    protected TitleTemplate $titleTemplate;
    protected PreGlobalActionSelectionTemplate $preGlobalActionSelectionTemplate;

    public function __construct(NetteGrid $netteGrid)
    {
        $this->netteGrid = $netteGrid;
        $this->wholeDocumentTemplate = new WholeDocumentTemplate();
        $this->tableTemplate = new TableTemplate();
        $this->theadTemplate = new TheadTemplate();
        $this->theadTitlesRowTemplate = new TitlesRowTemplate();
        $this->tbodyTemplate = new TbodyTemplate($netteGrid);
        $this->emptyDataRowTemplate = new EmptyDataRowTemplate();
        $this->emptyDataColTemplate = new EmptyDataColTemplate($netteGrid);
        $this->headFilterRowTemplate = new HeadFilterRowTemplate();
        $this->headerActionsColTemplate = new HeaderActionsColTemplate();
        $this->dataActionsColTemplate = new DataActionsColTemplate();
        $this->rowActionEdit = new RowActionEdit($netteGrid);
        $this->rowActionCancel = new RowActionCancel($netteGrid);
        $this->rowActionSave = new RowActionSave($netteGrid);
        $this->rowActionInlineAddCancel = new RowActionInlineCancel($netteGrid);
        $this->rowActionInlineAddSave = new RowActionInlineSave($netteGrid);
        $this->tfootTemplate = new TfootTemplate();
        $this->tfootContentTemplate = new TfootContentTemplate($netteGrid);
        $this->topRowTemplate = new TopRowTemplate();
        $this->topActionsWrapperTemplate = new TopActionsWrapperTemplate();
        $this->titleWrapperTemplate = new TitleWrapperTemplate();
        $this->titleTemplate = new TitleTemplate();
        $this->preGlobalActionSelectionTemplate = new PreGlobalActionSelectionTemplate();
    }

    /**
     * Set confirmation style [baseConfirmation, nittroConfirmation]
     * @param string $defaultConfirmationStyle
     * @return DocumentTemplate
     */
    public function setDefaultConfirmationStyle(string $defaultConfirmationStyle): self
    {
        $this->defaultConfirmationStyle = $defaultConfirmationStyle;
        return $this;
    }

    /**
     * Get confirmation style
     * @return string
     */
    public function getDefaultConfirmationStyle(): string
    {
        return $this->defaultConfirmationStyle;
    }

    /**
     * Set table responsive .table-responsive{-sm|-md|-lg|-xl} from Bootstrap library
     * @param string|null $screenWidth [sm|md|lg|xl]
     * @return DocumentTemplate
     */
    public function setResponsiveTable(?string $screenWidth=''): self
    {
        $this->getWholeDocumentTemplate()
            ->setResponsiveTable($screenWidth);
        return $this;
    }

    /**
     * Set sticky header
     * @param int $offset
     * @return DocumentTemplate
     */
    public function setStickyHeader(int $offset): self
    {
        foreach($this->netteGrid->getColumns(true) as $columnName => $column)
            $column->setStickyHeader(true, $offset);
        $this->headerActionsColTemplate->setStickyHeader(true, $offset);
        return $this;
    }

    /**
     * Set <tbody> hidden
     * @param bool $hiddenHeader
     * @return DocumentTemplate
     */
    public function setHiddenHeader(bool $hiddenHeader=true): self
    {
        $this->hiddenHeader = $hiddenHeader;
        return $this;
    }

    /**
     * Get title template
     * @return TitleTemplate
     */
    public function getTitleTemplate(): TitleTemplate
    {
        return $this->titleTemplate;
    }

    /**
     * Get title wrapper
     * @return TitleWrapperTemplate
     */
    public function getTitleWrapperTemplate(): TitleWrapperTemplate
    {
        return $this->titleWrapperTemplate;
    }

    /**
     * Top row template
     * @return TopRowTemplate
     */
    public function getTopRowTemplate(): TopRowTemplate
    {
        return $this->topRowTemplate;
    }

    /**
     * Get top actions wrapper <div>
     * @return TopActionsWrapperTemplate
     */
    public function getTopActionsWrapperTemplate(): TopActionsWrapperTemplate
    {
        return $this->topActionsWrapperTemplate;
    }

    /**
     * Get tfoot template <tfoot>
     * @return TfootTemplate
     */
    public function getTfootTemplate(): TfootTemplate
    {
        return $this->tfootTemplate;
    }

    /**
     * Tfoot content <td>
     * @return TfootContentTemplate
     */
    public function getTfootContentTemplate(): TfootContentTemplate
    {
        return $this->tfootContentTemplate;
    }

    /**
     * Save button for inline add
     * @return RowActionInlineSave
     */
    public function getRowActionInlineAddSave(): RowActionInlineSave
    {
        return $this->rowActionInlineAddSave;
    }

    /**
     * Cancel button for inline add
     * @return RowActionInlineCancel
     */
    public function getRowActionInlineAddCancel(): RowActionInlineCancel
    {
        return $this->rowActionInlineAddCancel;
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
        if(is_null($this->emptyData))
            $this->emptyData = BaseElement::getStatic('', [], 'Empty data. ');
        return $this->emptyData;
    }

    /**
     * Get whole document template
     * @return WholeDocumentTemplate
     */
    public function getWholeDocumentTemplate(): WholeDocumentTemplate
    {
        $this->netteGrid->onAnchor[] = function() {
            $this->wholeDocumentTemplate
                ->addDataAttribute('grid-name', $this->netteGrid->getUniqueId())
                ->setDefaultClass('nette-grid');
        };
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
     * @param callable|null $dataRowCallback function(DataRowTemplate $template, $row): DataRowTemplate
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
        return $this->dataRowTemplate ?? $this->dataRowTemplate = new DataRowTemplate($this);
    }

    /**
     * @param mixed $row
     * @return DataRowTemplate
     * @throws \ReflectionException
     * @internal
     *
     * Get data row template for rendering - internal
     */
    public function getDataRowTemplateForRendering($row): DataRowTemplate
    {
        $templateDefault = $defaultTemplate = $this->getDataRowTemplate();
        if(is_callable($this->dataRowCallback)) {
            $template = new DataRowTemplate($this);
            $attributes = $template->render()->attrs;
            $template->addHtmlAttributes($attributes);
            $fn = $this->dataRowCallback;
            $type = ReflectionHelper::getCallbackParameterType($fn, 1);
            $data = ReflectionHelper::getRowCallbackClosure($row, $type);
            $edited = $fn($template, $data);
            return $edited instanceof DataRowTemplate ? $edited : $template;
        }
        return $templateDefault;
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
        return $this->emptyDataColTemplate;
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

    /**
     * Get global action pre-selection label
     * @return PreGlobalActionSelectionTemplate
     */
    public function getPreGlobalActionSelectionTemplate(): PreGlobalActionSelectionTemplate
    {
        return $this->preGlobalActionSelectionTemplate;
    }
}