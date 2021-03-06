<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Column;


use e2221\NetteGrid\Document\Templates\Cols\DataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\HeadFilterColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\TitleColTemplate;
use e2221\NetteGrid\FormControls\InputControl;
use e2221\NetteGrid\GlobalActions\MultipleFilter;
use e2221\NetteGrid\NetteGrid;
use e2221\NetteGrid\Reflection\ReflectionHelper;
use e2221\utils\Html\HrefElement;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use ReflectionException;

abstract class Column implements IColumn
{
     use SmartObject;

     const
        SORT_ASC = 'ASC',
        SORT_DESC = 'DESC';

    /** @var string Name of column */
    public string $name;

    /** @var string|null Label of column */
    public ?string $label=null;

    /** @var string Input html type */
    protected string $htmlType='text';

    /** @var string Edit input used with directly edit (not inline), for text inputs it´s recommended to use textarea */
    protected string $editInputTag='textarea';

    /** @var bool is column sortable */
    protected bool $sortable=false;

    /** @var bool is column filterable */
    protected bool $filterable=false;

    /** @var bool is column editable in line */
    protected bool $editable=false;

    /** @var bool is editable in single column */
    protected bool $editableInColumn=false;

    /** @var bool is column required (for editing) */
    protected bool $required=false;

    /** @var bool is column hidden */
    protected bool $hidden=false;

    /** @var NetteGrid Nette grid */
    protected NetteGrid $netteGrid;

    /** @var TitleColTemplate Title col to style */
    protected TitleColTemplate $titleColTemplate;

    /** @var null|callable Change cell value with callback function($row, $cell){}: string */
    protected $cellValueCallback=null;

    /** @var null|callable Change edit cell value function($row, $cell){}: string  */
    protected $editCellValueCallback=null;

    /** @var DataColTemplate|null Data col template */
    protected ?DataColTemplate $dataColTemplate=null;

    /** @var null|callable function(DataColTemplate $template, $row, $cell){} */
    protected $dataColTemplateCallback=null;

    /** @var HeadFilterColTemplate|null Filter col template */
    protected ?HeadFilterColTemplate $headFilterColTemplate=null;

    /** @var BaseControl|null Filter input */
    protected ?BaseControl $filterInput=null;

    /** @var BaseControl|null Edit input */
    protected ?BaseControl $editInput=null;

    /** @var bool Enable/disable addable behaviour */
    protected bool $addAble=false;

    /** @var BaseControl|null Add input */
    protected ?BaseControl $addInput=null;

    /** @var string Sort direction could be ['', 'ASC', 'DESC'] */
    protected string $sortDirection='';

    /** @var MultipleFilter[] */
    protected array $multipleFilters=[];

    /** @var null|callable Export value callback: function($row, $cell): ?string  */
    protected $exportValueCallback=null;

    /** @var string  */
    protected string $inputClass = InputControl::class;

    /** @var null|callable Column link callback: function(e2221\utils\Html\HrefElement $href, $row, $primary, $cell): void|string  */
    protected $columnLinkCallback=null;

    /** @var HrefElement|null Column link element */
    protected ?HrefElement $columnLink=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $label=null)
    {
        $this->netteGrid = $netteGrid;
        $this->name = $name;
        $this->label = $label ?? ucfirst($this->name);
        $this->titleColTemplate = $this->defaultTitleColTemplate();
        $this->setStickyHeader();
    }

    /**
     * Set html type directly
     * @param string $type
     * @return Column
     */
    public function setHtmlType(string $type): self
    {
        $this->htmlType = $type;
        return $this;
    }

    /**
     * Set this column as default grid sort
     * @param string $direction
     * @return Column
     */
    public function setAsDefaultGridSortBy(string $direction='ASC'): self
    {
        $this->netteGrid->sortDirection = $direction;
        $this->netteGrid->sortByColumn = $this->name;
        $this->setSortDirection($direction);
        return $this;
    }


    /**
     * Add multiple filter
     * @param MultipleFilter $multipleFilter
     * @return Column
     */
    public function addMultipleFilter(MultipleFilter $multipleFilter): self
    {
        if(isset($this->netteGrid->multipleFilters[$multipleFilter->getName()]) == false)
            $this->netteGrid->multipleFilters[$multipleFilter->getName()] = $multipleFilter;
        $this->multipleFilters[$multipleFilter->getName()] = $multipleFilter;
        $multipleFilter->onAddColumn($this);
        return $this;
    }


    /**
     * RENDERING
     * *****************************************************************************
     *
     */

    /**
     * Get cell value
     * @param mixed $row
     * @return mixed
     * @internal
     */
    public function getCellValue($row)
    {
        $keyName = $this->name;
        if(is_array($row))
        {
            $row = ArrayHash::from($row);
        }

        return $row->$keyName ?? null;
    }

    /**
     * @param mixed $row
     * @return mixed
     * @throws ReflectionException
     * @internal
     *
     * Get Cell value for rendering - internal
     */
    public function getEditCellValue($row)
    {
        $cell = $this->getCellValue($row);
        if(is_null($this->editCellValueCallback))
            return $cell;
        $fn = $this->editCellValueCallback;
        $type = ReflectionHelper::getCallbackParameterType($fn, 0);
        $data = ReflectionHelper::getRowCallbackClosure($row, $type);
        return $fn($data, $cell);
    }

    /**
     * @param mixed $row
     * @param mixed $cell
     * @return mixed
     * @throws ReflectionException
     * @internal
     *
     * Get Cell value - internal for rendering
     */
    public function getCellValueForRendering($row, $cell=null)
    {
        $cellValue = $cell ?? $this->getCellValue($row);
        if(is_callable($this->cellValueCallback))
        {
            $fn = $this->cellValueCallback;
            $type = ReflectionHelper::getCallbackParameterType($fn, 0);
            $data = ReflectionHelper::getRowCallbackClosure($row, $type);
            $cellValue = $fn($data, $cellValue);
        }
        if(is_callable($this->columnLinkCallback))
        {
            $fnLink = $this->columnLinkCallback;
            $columnLink = $this->getColumnLink();
            $columnLink->setTextContent($cellValue);
            $type = ReflectionHelper::getCallbackParameterType($fnLink, 1);
            $data = ReflectionHelper::getRowCallbackClosure($row, $type);
            $link = $fnLink($columnLink, $data, $cellValue);
            if(is_string($link))
                $columnLink->setLink($link);
            return $columnLink->render();
        }
        return $cellValue;
    }

    /**
     * Get export value
     * @param mixed $row
     * @return mixed
     * @throws ReflectionException
     * @internal
     */
    public function getExportCellValue($row)
    {
        $cellValue = $this->getCellValue($row);
        if(is_callable($this->exportValueCallback))
        {
            $fn = $this->exportValueCallback;
            $type = ReflectionHelper::getCallbackParameterType($fn, 0);
            $data = ReflectionHelper::getRowCallbackClosure($row, $type);
            return $fn($data, $cellValue);
        }else{
            return $cellValue;
        }
    }


    /**
     * @param mixed $row
     * @param mixed $primary
     * @return DataColTemplate
     * @throws InvalidLinkException
     * @throws ReflectionException
     * @internal
     *
     * Get data col template - only for rendering internal
     */
    public function getDataColTemplateForRendering($row, $primary): DataColTemplate
    {
        $template = $this->getDataColTemplate();
        if(is_callable($this->dataColTemplateCallback))
        {
            $attrs = $template->render()->attrs;
            $templateNew = new DataColTemplate($this);
            if(isset($attrs['class']))
                $templateNew->addClass($attrs['class']);
            $fn = $this->dataColTemplateCallback;
            $type = ReflectionHelper::getCallbackParameterType($fn, 1);
            $data = ReflectionHelper::getRowCallbackClosure($row, $type);
            $edited = $fn($templateNew, $data, $this->getCellValue($row));
            $template = $edited instanceof DataColTemplate ? $edited : $templateNew;
        }
        if($this->editableInColumn === true)
        {
            if(($this->netteGrid->editMode === true && $this->netteGrid->editKey != $primary) || $this->netteGrid->editMode === false)
            {
                $template
                    ->addDataAttribute('column-editable', $this->name)
                    ->addDataAttribute('editable-link', $this->netteGrid->link('editColumn', $primary, $this->name))
                    ->addDataAttribute('edit-value', $this->getEditCellValue($row))
                    ->addDataAttribute('edit-input', $this->editInputTag)
                    ->addDataAttribute('print-value', $this->getCellValueForRendering($row));
            }
        }
        return $template;
    }

    /**
     * VALUES CALLBACKS
     * *****************************************************************************
     *
     */

    /**
     * Set column link callback
     * @param callable|null $columnLinkCallback function(e2221\utils\Html\HrefElement $href, $row, $primary, $cell): void|string
     * @return Column
     */
    public function setColumnLinkCallback(?callable $columnLinkCallback): self
    {
        $this->columnLinkCallback = $columnLinkCallback;
        return $this;
    }


    /**
     * Set export value callback
     * @param callable|null $exportValueCallback function($row, $cell): ?string
     * @return Column
     */
    public function setExportCellValueCallback(?callable $exportValueCallback): self
    {
        $this->exportValueCallback = $exportValueCallback;
        return $this;
    }

    /**
     * Set edit cell value callback
     * @param callable|null $editCellValueCallback function($row, $cell){}: string
     * @return Column
     */
    public function setEditCellValueCallback(?callable $editCellValueCallback): self
    {
        $this->editCellValueCallback = $editCellValueCallback;
        return $this;
    }

    /**
     * Set data col template callback
     * @param callable|null $callback function(DataColTemplate $template, $row, $cell){}
     * @return Column
     */
    public function setDataColTemplateCallback(?callable $callback): self
    {
        $this->dataColTemplateCallback = $callback;
        return $this;
    }

    /**
     * Set cell value callback
     * @param callable|null $cellValueCallback function($row, $cell){}: string
     * @return Column
     */
    public function setCellValueCallback(?callable $cellValueCallback): self
    {
        $this->cellValueCallback = $cellValueCallback;
        return $this;
    }

    /**
     * Get column link
     * @return HrefElement
     */
    public function getColumnLink(): HrefElement
    {
        $this->columnLink = $this->columnLink ?? HrefElement::getStatic();
        return $this->columnLink;
    }

    /**
     * COLUMN TEMPLATES
     * *****************************************************************************
     *
     */

    /**
     * Get head filter col template
     * @return HeadFilterColTemplate
     */
    public function getHeadFilterColTemplate(): HeadFilterColTemplate
    {
        $this->headFilterColTemplate = $this->headFilterColTemplate ?? new HeadFilterColTemplate($this);
        return $this->headFilterColTemplate;
    }

    /**
     * Get data col template
     * @return DataColTemplate
     */
    public function getDataColTemplate(): DataColTemplate
    {
        $this->dataColTemplate = $this->dataColTemplate ?? new DataColTemplate($this);
        return $this->dataColTemplate;
    }

    /**
     * Default title col template
     * @return TitleColTemplate
     */
    private function defaultTitleColTemplate(): TitleColTemplate
    {
        $titleCol = new TitleColTemplate();
        $titleCol->setTextContent($this->label);
        return $titleCol;
    }

    /**
     * Get title col template for styling
     * @return TitleColTemplate
     */
    public function getTitleColTemplate(): TitleColTemplate
    {
        return $this->titleColTemplate;
    }

    /**
     * Set this column as primary
     * @return Column
     */
    public function setAsPrimaryColumn(): self
    {
        $this->netteGrid->setPrimaryColumn($this->name);
        return $this;
    }

    /**
     * PROPERTY SETTERS & GETTERS
     * *****************************************************************************
     *
     */

    /**
     * Set sort direction
     * @param string $direction
     * @return Column
     */
    public function setSortDirection(string $direction): self
    {
        $this->sortDirection = $direction;
        return $this;
    }

    /**
     * Get sort direction
     * @return string
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * Set sticky header
     * @param bool $sticky
     * @param int $offset top offset in px
     * @return Column
     */
    public function setStickyHeader(bool $sticky=true, int $offset=0): self
    {
        $this->getTitleColTemplate()
            ->setStickyHeader($sticky, $offset);
        return $this;
    }

    /**
     * Set sortable
     * @param bool $sortable
     * @return Column
     */
    public function setSortable(bool $sortable=true): self
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * Set filterable
     * @param bool $filterable
     * @return Column
     */
    public function setFilterable(bool $filterable=true): self
    {
        $this->filterable = $filterable;
        $this->netteGrid->setFilterable($filterable);
        return $this;
    }

    /**
     * Set editable (in line)
     * @param bool $editable
     * @return Column
     */
    public function setEditable(bool $editable=true): self
    {
        $this->editable = $editable;
        if($editable === true)
            $this->netteGrid->setEditable(true);
        return $this;
    }

    /**
     * Set editable in column
     * @param bool $editableInColumn
     * @return Column
     */
    public function setEditableInColumn(bool $editableInColumn=true): self
    {
        $this->editableInColumn = $editableInColumn;
        if($editableInColumn === true)
            $this->netteGrid->setEditableInColumn(true);
        return $this;
    }

    /**
     * Set required
     * @param bool $required
     * @return Column
     */
    public function setRequired(bool $required=true): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Col will be hidden
     * @param bool $hidden
     * @return Column
     */
    public function setHidden(bool $hidden=true): self
    {
        $this->hidden = $hidden;
        $this->getTitleColTemplate()
            ->setHidden($this->hidden);
        $this->getDataColTemplate()
            ->setHidden($this->hidden);
        return $this;
    }

    /**
     * Set column addable
     * @param bool $addAble
     * @return Column
     */
    public function setAddAble(bool $addAble=true): self
    {
        $this->addAble = $addAble;
        if($addAble === true)
            $this->netteGrid->setAddable(true);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAddable(): bool
    {
        return $this->addAble;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }


    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * INPUTS & FORMS
     * *****************************************************************************
     *
     */

    /**
     * Set form value
     * @param mixed $cellValue
     * @internal
     */
    public function setFormValue($cellValue): void
    {
        $this->netteGrid->getComponent('form')
            ->getComponent('edit')
                ->getComponent($this->name)
                    ->setDefaultValue($cellValue);
    }

    /**
     * Add filter form input
     * @internal
     */
    public function addFilterFormInput(): void
    {
        if($this->isFilterable())
        {
            $container = $this->netteGrid->getFilterContainer();
            $input = $this->getFilterInput();
            $input->setHtmlAttribute('data-autosubmit');
            $input->setHtmlAttribute('data-container', 'filterSubmit');
            $container->addComponent($input, $this->name);
        }
    }

    /**
     * Add filter form input
     * @internal
     */
    public function addEditFormInput(): void
    {
        if($this->isEditable())
        {
            $container = $this->netteGrid->getEditContainer();
            $input = $this->getEditInput();
            $container->addComponent($input, $this->name);
        }
    }

    /**
     * Add add form input
     * @internal
     */
    public function addAddFormInput(): void
    {
        if($this->isAddAble())
        {
            $container = $this->netteGrid->getAddContainer();
            $input = $this->getAddInput();
            $container->addComponent($input, $this->name);
        }
    }

    /**
     * Get filter input
     * @return BaseControl|SelectBox
     */
    public function getFilterInput()
    {
        if(is_null($this->filterInput)){
            $this->filterInput = $this->getInput();
            if($this->netteGrid->isAutocomplete() === false || $this->netteGrid->isFilterAutocomplete() === false)
                $this->filterInput->setHtmlAttribute('autocomplete', 'off');
            return $this->filterInput;
        }else{
            return $this->filterInput;
        }
    }

    /**
     * Get edit input
     * @return BaseControl|SelectBox
     */
    public function getEditInput()
    {
        if(is_null($this->editInput)){
            $this->editInput = $this->getInput();
            if($this->isRequired())
                $this->editInput->setRequired();
            $this->editInput->setHtmlAttribute('placeholder', $this->label);
            if($this->netteGrid->isAutocomplete() === false || $this->netteGrid->isEditAutocomplete() === false)
                $this->editInput->setHtmlAttribute('autocomplete', 'off');
            return $this->editInput;
        }else{
            return $this->editInput;
        }
    }

    /**
     * Get add input
     * @return BaseControl|null|SelectBox
     */
    public function getAddInput()
    {
        if(is_null($this->addInput)){
            $this->addInput = $this->getInput();
            if($this->isRequired())
                $this->addInput->setRequired();
            $this->addInput->setHtmlAttribute('placeholder', $this->label);
            if($this->netteGrid->isAutocomplete() === false || $this->netteGrid->isAddAutocomplete() === false)
                $this->addInput->setHtmlAttribute('autocomplete', 'off');
            return $this->addInput;
        }else{
            return $this->addInput;
        }
    }

    /**
     * Get parent input for another inputs
     * @return BaseControl|SelectBox
     */
    protected function getInput()
    {
        $inputClass = $this->inputClass;
        $input = new $inputClass();
        $input->setHtmlType($this->htmlType);
        return $input->getControl();
    }

    /**
     * Set input class
     * @param string $inputClass
     * @return Column
     */
    public function setInputClass(string $inputClass): self
    {
        $this->inputClass = $inputClass;
        return $this;
    }
}