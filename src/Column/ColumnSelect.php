<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;

use e2221\NetteGrid\Document\Templates\Cols\DataColTemplate;
use e2221\NetteGrid\Exceptions\NetteGridException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Controls\SelectBox;

class ColumnSelect extends Column
{
    /** @var mixed[] Select items */
    protected array $selection;

    protected string $editInputTag='select';
    protected string $htmlType='select';

    /**
     * @param mixed $row
     * @return mixed
     * @throws NetteGridException
     * @internal
     *
     * Get Cell value for rendering - internal
     */
    public function getEditCellValue($row)
    {
        $cell = $this->getCellValue($row);
        if(is_null($this->editCellValueCallback))
        {
            if(is_object($cell))
                throw new NetteGridException(sprintf('Cell value for edit action is object (instance of %s given) in column %s ', get_class($cell), $this->name));
            return isset($this->selection[$cell]) ? $this->selection[$cell] : '';
        }
        $fn = $this->editCellValueCallback;
        return $fn($row, $cell);
    }

    /**
     * @param mixed $row
     * @param mixed $cell
     * @return mixed
     * @throws NetteGridException
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
            $cellValue = $fn($row, $cellValue);
        }else{
            if(is_object($cellValue))
                throw new NetteGridException(sprintf('Cell value for rendering is object (instance of %s given) in column %s ', get_class($cellValue), $this->name));
            $cellValue = isset($this->selection[$cellValue]) ? $this->selection[$cellValue] : '';
        }
        if(is_callable($this->columnLinkCallback))
        {
            $fnLink = $this->columnLinkCallback;
            $columnLink = $this->getColumnLink();
            $columnLink->setTextContent($cellValue);
            $link = $fnLink($columnLink, $row, $cellValue);
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
     * @param mixed $row
     * @param mixed $primary
     * @return DataColTemplate
     * @throws InvalidLinkException
     * @internal
     */
    public function getDataColTemplateForRendering($row, $primary): DataColTemplate
    {
        $template = parent::getDataColTemplateForRendering($row, $primary);
        if($this->editableInColumn === true)
        {
            if(($this->netteGrid->editMode === true && $this->netteGrid->editKey != $primary) || $this->netteGrid->editMode === false)
                $template->addDataAttribute('edit-options', json_encode($this->selection));
        }
        return $template;
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
            $input->setHtmlAttribute('data-autosubmit-select');
            $input->setHtmlAttribute('data-container', 'filterSubmit');
            $container->addComponent($input, $this->name);
        }
    }

    /**
     * Get filter input
     * @return SelectBox
     */
    public function getFilterInput(): SelectBox
    {
        return parent::getFilterInput();
    }

    /**
     * Get edit input
     * @return SelectBox
     */
    public function getEditInput(): SelectBox
    {
        return parent::getEditInput();
    }

    /**
     * Get add input
     * @return SelectBox|null
     */
    public function getAddInput(): ?SelectBox
    {
        return parent::getAddInput();
    }

    /**
     * Get input
     * @return SelectBox
     */
    protected function getInput(): SelectBox
    {
        $input = parent::getInput();
        $input->setItems($this->selection);
        return $input;
    }
}