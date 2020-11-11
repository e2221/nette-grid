<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Column;


use e2221\NetteGrid\NetteGrid;
use Nette\SmartObject;

class BaseColumn
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

    public function __construct(NetteGrid $netteGrid, string $name, ?string $label=null)
    {
        $this->netteGrid = $netteGrid;
        $this->name = $name;
        $this->label = $label ?? ucfirst($this->name);
    }

    /**
     * @param bool $sortable
     */
    public function setSortable(bool $sortable): void
    {
        $this->sortable = $sortable;
    }

    /**
     * @param bool $filterable
     */
    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }

    /**
     * @param bool $multipleFilterable
     */
    public function setMultipleFilterable(bool $multipleFilterable): void
    {
        $this->multipleFilterable = $multipleFilterable;
    }

    /**
     * @param bool $editable
     */
    public function setEditable(bool $editable): void
    {
        $this->editable = $editable;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
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