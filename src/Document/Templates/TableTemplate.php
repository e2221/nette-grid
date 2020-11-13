<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


class TableTemplate extends BaseTemplate
{
    protected ?string $elName = 'table';
    public string $defaultClass = 'table table-sm';

    /** @var bool true adds class table-hover */
    public bool $hoverRows=true;

    /** @var bool true adds class table-borderless */
    public bool $tableBorderLess=false;

    /** @var bool true adds class table-stripped */
    public bool $tableStripped=true;

    /** @var bool true adds class table-bordered */
    public bool $tableBordered=true;

    /** @var bool true removes all styles (hover, border, strip, border) */
    public bool $removeStyles=false;

    /**
     * Before render
     */
    public function beforeRender(): void
    {
        if($this->hoverRows === true)
            $this->addClass('table-hover');
        if($this->tableBorderLess === true)
        {
            $this->tableBordered = false;
            $this->addClass('table-borderless');
        }
        if($this->tableBordered === true)
            $this->addClass('table-bordered');
        if($this->tableStripped === true)
            $this->addClass('table-striped');
    }

    /**
     * Set remove styles
     * @param bool $removeStyles
     * @return TableTemplate
     */
    public function setRemoveStyles(bool $removeStyles=true): self
    {
        $this->removeStyles = $removeStyles;
        $this->hoverRows = false;
        $this->tableBordered = false;
        $this->tableBorderLess = false;
        $this->tableStripped = false;
        return $this;
    }

    /**
     * Set table borderless
     * @param bool $tableBorderLess
     * @return TableTemplate
     */
    public function setTableBorderLess(bool $tableBorderLess=true): self
    {
        $this->tableBorderLess = $tableBorderLess;
        if($this->tableBorderLess === true)
            $this->tableBordered = false;
        return $this;
    }

    /**
     * Set table stripped
     * @param bool $tableStripped
     * @return TableTemplate
     */
    public function setTableStripped(bool $tableStripped=true): self
    {
        $this->tableStripped = $tableStripped;
        return $this;
    }

    /**
     * Set table bordered
     * @param bool $tableBordered
     * @return TableTemplate
     */
    public function setTableBordered(bool $tableBordered=true): self
    {
        $this->tableBordered = $tableBordered;
        return $this;
    }

    /**
     * Set hover rows
     * @param bool $hoverRows
     * @return TableTemplate
     */
    public function setHoverRows(bool $hoverRows=true): self
    {
        $this->hoverRows = $hoverRows;
        return $this;
    }

}