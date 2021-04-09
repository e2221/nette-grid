<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Column;


use e2221\NetteGrid\Document\Templates\Cols\DataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\HeadFilterColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\TitleColTemplate;
use e2221\NetteGrid\GlobalActions\MultipleFilter;
use e2221\utils\Html\HrefElement;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

interface IColumn
{

    /**
     * Set directly html type of input
     * @param string $type
     * @return IColumn
     */
    public function setHtmlType(string $type): self;

    /**
     * Set export value callback
     * @param callable|null $exportValueCallback function($row, $cellValue)
     * @return IColumn
     */
    public function setExportCellValueCallback(?callable $exportValueCallback): self;

    /**
     * Set edit cell value callback
     * @param callable|null $editCellValueCallback function($row, $cell)
     * @return IColumn
     */
    public function setEditCellValueCallback(?callable $editCellValueCallback): self;

    /**
     * Get export value
     * @param mixed $row
     * @return mixed
     * @internal
     */
    public function getExportCellValue($row);

    /**
     * Add multiple filter
     * @param MultipleFilter $multipleFilter
     * @return $this
     */
    public function addMultipleFilter(MultipleFilter $multipleFilter): self;

    /**
     * Get data col template - styling data col <td>
     * @return DataColTemplate
     */
    public function getDataColTemplate(): DataColTemplate;

    /**
     * Set data col template callback (for edit/style data <td> element)
     * @param callable|null $callback function(DataColTemplate $template, $row, $cellValue): void - edit $template | DataColTemplate
     * @return $this
     */
    public function setDataColTemplateCallback(?callable $callback): self;


    /**
     * Set cell value callback (for edit cell value) - could return \Nette\Utils\Html
     * @param callable|null $cellValueCallback function($row, $cell)
     * @return $this
     */
    public function setCellValueCallback(?callable $cellValueCallback): self;


    /**
     * Get head filter col template - styling head filter <th>
     * @return HeadFilterColTemplate
     */
    public function getHeadFilterColTemplate(): HeadFilterColTemplate;


    /**
     * Get title col template (for edit/style title <td> elemtn)
     * @return TitleColTemplate
     */
    public function getTitleColTemplate(): TitleColTemplate;


    /**
     * Set this column as primary
     * @return IColumn
     */
    public function setAsPrimaryColumn(): self;


    /**
     * Set this column sortable
     * @param bool $sortable
     * @return IColumn
     */
    public function setSortable(bool $sortable=true): self;


    /**
     * Set this column filterable
     * @param bool $filterable
     * @return IColumn
     */
    public function setFilterable(bool $filterable=true): self;


    /**
     * Set this column editable (in line)
     * @param bool $editable
     * @return IColumn
     */
    public function setEditable(bool $editable=true): self;

    /**
     * Set editable in column
     * @param bool $editableInColumn
     * @return IColumn
     */
    public function setEditableInColumn(bool $editableInColumn=true): self;

    /**
     * Set sort direction (ASC | DESC | '')
     * @param string $direction
     * @return IColumn
     */
    public function setSortDirection(string $direction): self;

    /**
     * Set sticky header
     * @param bool $sticky
     * @param int $offset top offset in px
     * @return IColumn
     */
    public function setStickyHeader(bool $sticky=true, int $offset=0): self;

    /**
     * Get sort direction
     * @return string
     */
    public function getSortDirection(): string;

    /**
     * Set this column required
     * @param bool $required
     * @return IColumn
     */
    public function setRequired(bool $required=true): self;

    /**
     * Set column hidden
     * @param bool $hidden
     * @return IColumn
     */
    public function setHidden(bool $hidden=true): self;

    /**
     * Get column name
     * @return string
     */
    public function getName(): string;

    /**
     * Get column label
     * @return string|null
     */
    public function getLabel(): ?string;

    /**
     * Set column add-able
     * @param bool $addAble
     * @return IColumn
     */
    public function setAddAble(bool $addAble=true): self;


    public function isSortable(): bool;
    public function isFilterable(): bool;
    public function isEditable(): bool;
    public function isRequired(): bool;
    public function isHidden(): bool;
    public function isAddable(): bool;

    /**
     * INTERNAL
     * *****************************************************
     *
     */

    /**
     * Get original cell value
     * @param mixed $row
     * @return mixed
     * @internal
     */
    public function getCellValue($row);

    /**
     * Get real cell value
     * @param mixed $row
     * @param null $cell
     * @return mixed
     * @internal
     */
    public function getCellValueForRendering($row, $cell=null);

    /**
     * Get data col template for rendering - apply callbacks to edit <td> attribute
     * @param mixed $row
     * @param mixed $primary
     * @return DataColTemplate
     * @internal
     */
    public function getDataColTemplateForRendering($row, $primary): DataColTemplate;


    /**
     * Set input form value
     * @param mixed $cellValue
     * @internal
     */
    public function setFormValue($cellValue): void;

    /**
     * Get edit cell value
     * @param mixed $row
     * @return mixed
     * @internal
     */
    public function getEditCellValue($row);

    /**
     * Set input class getter - this class have to implement e2221\NetteGrid\FormControls\IFormControl - you can style the parent for another inputs
     * @param string $inputClass
     * @return IColumn
     */
    public function setInputClass(string $inputClass): self;

    /**
     * Set column link callback
     * @param callable|null $columnLinkCallback function(e2221\utils\Html\HrefElement $href, $row, $primary, $cell): void-edit $href | string - url
     * @return IColumn
     */
    public function setColumnLinkCallback(?callable $columnLinkCallback): self;

    /**
     * Call NetteGrid after load state
     * @internal
     */
    public function addFilterFormInput(): void;
    public function addEditFormInput(): void;
    public function addAddFormInput(): void;

    /**
     * Get filter input to styling
     * @return BaseControl|SelectBox|null
     */
    public function getFilterInput();

    /**
     * Get edit input to styling
     * @return BaseControl|SelectBox|null
     */
    public function getEditInput();

    /**
     * Get add input to styling
     * @return BaseControl|SelectBox|null
     */
    public function getAddInput();

    /**
     * Get column link for styling - make sense only if you set this column as link
     * @return HrefElement
     */
    public function getColumnLink(): HrefElement;

    /**
     * Set this column as default grid sort
     * @param string $direction
     * @return Column
     */
    public function setAsDefaultGridSortBy(string $direction='ASC'): self;
}