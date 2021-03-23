<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;

use e2221\NetteGrid\Document\Templates\Cols\DataColTemplate;
use e2221\NetteGrid\Exceptions\NetteGridException;
use e2221\NetteGrid\Reflection\ReflectionHelper;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use ReflectionException;

class ColumnSelect extends Column
{
    private const
        SELECTPICKER='selectpicker',
        SELECTPICKER_LIVE_SEARCH = ['data-live-search' => 'true'];


    /** @var mixed[] Select items */
    protected array $selection=[];

    protected string $editInputTag='select';
    protected string $htmlType='select';
    protected ?string $prompt=null;
    protected ?string $filterPrompt=null;

    /**
     * @param mixed $row
     * @return mixed
     * @throws NetteGridException
     * @throws ReflectionException
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
        $type = ReflectionHelper::getCallbackParameterType($fn, 0);
        $data = ReflectionHelper::getRowCallbackClosure($row, $type);
        return $fn($data, $cell);
    }

    /**
     * @param mixed $row
     * @param mixed $cell
     * @return mixed
     * @throws NetteGridException
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
                $template->addDataAttribute('edit-options', (string)json_encode($this->selection));
        }
        return $template;
    }

    /**
     * Set selection
     * @param mixed[] $selection
     * @param string|null $prompt
     * @return ColumnSelect
     */
    public function setSelection(array $selection, ?string $prompt=null): self
    {
        $this->selection = $selection;
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * Set filter prompt
     * @param string|null $prompt
     * @return ColumnSelect
     */
    public function setFilterPrompt(?string $prompt): self
    {
        $this->filterPrompt = $prompt;
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
            if($this->filterPrompt && $input instanceof SelectBox)
                $input->setPrompt($this->filterPrompt);
            $container->addComponent($input, $this->name);
        }
    }

    /**
     * Get filter input
     * @return SelectBox|BaseControl
     */
    public function getFilterInput()
    {
        return parent::getFilterInput();
    }

    /**
     * Get edit input
     * @return SelectBox|BaseControl
     */
    public function getEditInput()
    {
        if($this->editInput){
            return $this->editInput;
        }
        $select = parent::getEditInput();
        if($this->prompt && $select instanceof SelectBox){
            $select->setPrompt($this->prompt);
        }

        return $select;
    }

    /**
     * Get add input
     * @return SelectBox|null|BaseControl
     */
    public function getAddInput()
    {
        return parent::getAddInput();
    }

    /**
     * Get input
     * @return SelectBox|BaseControl
     */
    protected function getInput()
    {
        $input = parent::getInput();
        if($input instanceof SelectBox) {
            $input->setItems($this->selection);
        }
        return $input;
    }

    /**
     * Add selectpicker selector to edit input
     * @return ColumnSelect
     */
    public function addSelectpicker_edit(): ColumnSelect
    {
        $input = $this->getEditInput();
        $input
            ->setHtmlAttribute('class', self::SELECTPICKER)
            ->setHtmlAttribute(array_key_first(self::SELECTPICKER_LIVE_SEARCH), current(self::SELECTPICKER_LIVE_SEARCH));

        return $this;
    }

    /**
     * Add selectpicker selector to filter input
     * @return ColumnSelect
     */
    public function addSelectpicker_filter(): ColumnSelect
    {
        $input = $this->getFilterInput();
        $input
            ->setHtmlAttribute('class', self::SELECTPICKER)
            ->setHtmlAttribute(array_key_first(self::SELECTPICKER_LIVE_SEARCH), current(self::SELECTPICKER_LIVE_SEARCH));
        return $this;
    }

    /**
     * Add selectpicker selector to add input
     * @return ColumnSelect
     */
    public function addSelectpicker_add(): ColumnSelect
    {
        $input = $this->getAddInput();
        $input
            ->setHtmlAttribute('class', self::SELECTPICKER)
            ->setHtmlAttribute(array_key_first(self::SELECTPICKER_LIVE_SEARCH), current(self::SELECTPICKER_LIVE_SEARCH));
        return $this;
    }

    /**
     * Add selectpicker selector to all inputs
     * @return ColumnSelect
     */
    public function addSelectpicker(): ColumnSelect
    {
        $this->addSelectpicker_add();
        $this->addSelectpicker_edit();
        $this->addSelectpicker_filter();
        return $this;
    }
}