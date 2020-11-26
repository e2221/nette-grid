<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Column;


use ArrayAccess;
use Contributte\FormsBootstrap\Inputs\TextInput;
use e2221\NetteGrid\Document\Templates\Cols\DataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\HeadFilterColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\TitleColTemplate;
use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

abstract class Column implements IColumn
{
     use SmartObject;

    /** @var string Name of column */
    public string $name;

    /** @var string|null Label of column */
    public ?string $label=null;

    /** @var string Edit input used with directly edit (not inline), for text inputs it´s recommended to use textarea */
    protected string $editInputTag='textarea';

    /** @var bool is column sortable */
    protected bool $sortable=false;

    /** @var bool is column filterable */
    protected bool $filterable=false;

    /** @var bool is column multiple-filterable */
    protected bool $multipleFilterable=false;

    /** @var bool is column editable */
    protected bool $editable=false;

    /** @var bool is column required (for editing) */
    protected bool $required=false;

    /** @var bool is column hidden */
    protected bool $hidden=false;

    /** @var NetteGrid Nette grid */
    protected NetteGrid $netteGrid;

    /** @var TitleColTemplate Title col to style */
    protected TitleColTemplate $titleColTemplate;

    /** @var null|callable Change cell value with callback function($row, $cell){} */
    protected $cellValueCallback=null;

    /** @var null|callable Change edit cell value function($row, $cell){}  */
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

    public function __construct(NetteGrid $netteGrid, string $name, ?string $label=null)
    {
        $this->netteGrid = $netteGrid;
        $this->name = $name;
        $this->label = $label ?? ucfirst($this->name);
        $this->titleColTemplate = $this->defaultTitleColTemplate();
        $this->setStickyHeader();
    }

    /**
     * Set sticky header
     * @param bool $sticky
     * @param int $offset
     * @return Column
     */
    public function setStickyHeader(bool $sticky=true, int $offset=0): self
    {
        $this->getTitleColTemplate()
            ->setStickyHeader($sticky, $offset);
        return $this;
    }

    /**
     * Get head filter col template
     * @return HeadFilterColTemplate
     */
    public function getHeadFilterColTemplate(): HeadFilterColTemplate
    {
        if(is_null($this->headFilterColTemplate))
            $this->headFilterColTemplate = new HeadFilterColTemplate($this);
        return $this->headFilterColTemplate;
    }

    /**
     * Get cell value
     * @param mixed $row
     * @return mixed
     * @internal
     */
    public function getCellValue($row)
    {
        $keyName = $this->name;

        //array source
        if((is_array($row) || $row instanceof ArrayAccess) && isset($row[$keyName]))
            $row = ArrayHash::from($row);

        //other object source
        return $row->$keyName;
    }

    /**
     * Set edit cell value callback
     * @param callable|null $editCellValueCallback
     * @return Column
     */
    public function setEditCellValueCallback(?callable $editCellValueCallback): self
    {
        $this->editCellValueCallback = $editCellValueCallback;
        return $this;
    }

    /**
     * @param mixed $row
     * @return mixed
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
        return $fn($row, $cell);
    }

    /**
     * @param mixed $row
     * @param mixed $cell
     * @return mixed
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
            return $fn($row, $cellValue);
        }else{
            return $cellValue;
        }
    }

    /**
     * Get data col template
     * @return DataColTemplate
     */
    public function getDataColTemplate(): DataColTemplate
    {
        return $this->dataColTemplate = new DataColTemplate($this);
    }


    /**
     * Set data col template callback
     * @param callable|null $callback
     * @return Column
     */
    public function setDataColTemplateCallback(?callable $callback): self
    {
        $this->dataColTemplateCallback = $callback;
        return $this;
    }

    /**
     * @param mixed $row
     * @param mixed $primary
     * @return DataColTemplate
     * @throws InvalidLinkException
     * @internal
     *
     * Get data col template - only for rendering internal
     */
    public function getDataColTemplateForRendering($row, $primary): DataColTemplate
    {
        $template = clone(is_null($this->dataColTemplate) ? new DataColTemplate($this) : $this->dataColTemplate);
        if(is_callable($this->dataColTemplateCallback))
        {
            $fn = $this->dataColTemplateCallback;
            $edited = $fn($template, $row, $this->getCellValue($row));
            $template = $edited instanceof DataColTemplate ? $edited : $template;
        }
        if($this->isEditable() === true)
        {
            if(($this->netteGrid->editMode === true && $this->netteGrid->editKey != $primary) || $this->netteGrid->editMode === false)
            {
                $template
                    ->addDataAttribute('column-editable', $this->name)
                    ->addDataAttribute('editable-link', $this->netteGrid->link('editColumn', $primary, $this->name))
                    ->addDataAttribute('edit-value', $this->getEditCellValue($row))
                    ->addDataAttribute('edit-input', $this->editInputTag);
            }
        }
        return $template;
    }

    /**
     * Set cell value callback
     * @param callable|null $cellValueCallback
     * @return Column
     */
    public function setCellValueCallback(?callable $cellValueCallback): self
    {
        $this->cellValueCallback = $cellValueCallback;
        return $this;
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
     * Set multiple filterable
     * @param bool $multipleFilterable
     * @return Column
     */
    public function setMultipleFilterable(bool $multipleFilterable=true): self
    {
        $this->multipleFilterable = $multipleFilterable;
        return $this;
    }

    /**
     * Set editable
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
    public function isMultipleFilterable(): bool
    {
        return $this->multipleFilterable;
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
     * @return BaseControl
     */
    public function getFilterInput(): BaseControl
    {
        if(is_null($this->filterInput))
            $this->filterInput = $this->getInput();
        return $this->filterInput;
    }

    /**
     * Get edit input
     * @return BaseControl
     */
    public function getEditInput(): BaseControl
    {
        if(is_null($this->editInput))
            $this->editInput = $this->getInput();
        return $this->editInput;
    }

    /**
     * Get add input
     * @return BaseControl|null
     */
    public function getAddInput(): ?BaseControl
    {
        if(is_null($this->addInput))
            $this->addInput = $this->getInput();
        return $this->addInput;
    }

    /**
     * Get input
     * @return BaseControl
     */
    public function getInput(): BaseControl
    {
        $input = new TextInput($this->name);
        $input->setHtmlAttribute('class', 'form-control-sm');
        return $input;
    }

}