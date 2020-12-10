<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;


class ColumnSelect extends Column
{
    /** @var mixed[] Select items */
    protected array $selection;


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
            return isset($this->selection[$cell]) ? $this->selection[$cell] : '';
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
            return isset($this->selection[$cellValue]) ? $this->selection[$cellValue] : '';
        }
    }

    /**
     * Get export value
     * @param mixed $row
     * @return mixed
     * @internal
     */
    public function getExportCellValue($row)
    {
        $cellValue = $this->getCellValue($row);
        if(is_callable($this->exportValueCallback))
        {
            $fn = $this->exportValueCallback;
            return $fn($row, $cellValue);
        }else{
            return isset($this->selection[$cellValue]) ? $this->selection[$cellValue] : '';
        }
    }

    /**
     * Get input
     * @return BaseControl
     */
    public function getInput(): BaseControl
    {
        $input = new SelectBox(null, $this->selection);
        $input->setHtmlAttribute('class', 'form-control form-control-sm');
        return $input;
    }

    /**
     * Set selection
     * @param array $selection
     * @return ColumnSelect
     */
    public function setSelection(array $selection): self
    {
        $this->selection = $selection;
        return $this;
    }
}