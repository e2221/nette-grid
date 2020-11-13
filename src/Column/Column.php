<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Column;


use ArrayAccess;
use e2221\NetteGrid\Document\Templates\Cols\DataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\TitleColTemplate;
use e2221\NetteGrid\Exceptions\ColumnNotFoundException;
use e2221\NetteGrid\Exceptions\ResultRowNotExistsException;
use e2221\NetteGrid\NetteGrid;
use Nette\SmartObject;

class Column
{
    use SmartObject;

    /** @var string Name of column */
    public string $name;

    /** @var string|null Label of column */
    public ?string $label=null;

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

    /** @var null|callable Change cell value with callback function($rowData) */
    protected $cellValueCallback=null;

    /** @var DataColTemplate|null Data col template */
    protected ?DataColTemplate $dataColTemplate=null;

    /** @var null|callable  */
    protected $dataColTemplateCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $label=null)
    {
        $this->netteGrid = $netteGrid;
        $this->name = $name;
        $this->label = $label ?? ucfirst($this->name);
        $this->titleColTemplate = $this->defaultTitleColTemplate();
    }

    /**
     * Get cell value
     * @param mixed $row
     * @return mixed
     */
    public function getCellValue($row)
    {
        $keyName = $this->name;
        if(isset($row->$keyName))
            return $row->$keyName;
        if((is_array($row) || $row instanceof ArrayAccess) && isset($row[$keyName]))
            return $row[$keyName];
        throw new ResultRowNotExistsException("Result row does not have '{$keyName}' column.");
    }

    /**
     * @internal
     *
     * Get Cell value - internal for rendering
     * @param mixed $row
     * @return mixed
     */
    public function getCellValueForRendering($row)
    {
        $cellValue = $this->getCellValue($row);
        if(is_callable($this->cellValueCallback))
        {
            $fn = $this->cellValueCallback;
            return $fn($row, $cellValue);
        }else{
            return $cellValue;
        }
    }

    /**
     * Set data col template
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
     * @internal
     *
     * Get data col template - only for rendering internal
     * @param mixed $row
     * @return DataColTemplate
     */
    public function getDataColTemplateForRendering($row): DataColTemplate
    {
        $template = clone(is_null($this->dataColTemplate) ? new DataColTemplate($this) : $this->dataColTemplate);
        if(is_callable($this->dataColTemplateCallback))
        {
            $fn = $this->dataColTemplateCallback;
            $template = $fn($row, $template);
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
     * @throws ColumnNotFoundException
     */
    public function setAsPrimaryColumn(): self
    {
        $this->netteGrid->setPrimaryColumn($this->name);
        return $this;
    }

    /**
     * @param bool $sortable
     */
    public function setSortable(bool $sortable=true): void
    {
        $this->sortable = $sortable;
    }

    /**
     * @param bool $filterable
     */
    public function setFilterable(bool $filterable=true): void
    {
        $this->filterable = $filterable;
    }

    /**
     * @param bool $multipleFilterable
     */
    public function setMultipleFilterable(bool $multipleFilterable=true): void
    {
        $this->multipleFilterable = $multipleFilterable;
    }

    /**
     * @param bool $editable
     */
    public function setEditable(bool $editable=true): void
    {
        $this->editable = $editable;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required=true): void
    {
        $this->required = $required;
    }

    /**
     * Col will be hidden
     * @param bool $hidden
     */
    public function setHidden(bool $hidden=true): void
    {
        $this->hidden = $hidden;
        $this->getTitleColTemplate()
            ->setHidden($this->hidden);
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




}