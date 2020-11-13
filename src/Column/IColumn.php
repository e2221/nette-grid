<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Column;


use e2221\NetteGrid\Document\Templates\Cols\DataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\TitleColTemplate;

interface IColumn
{

    /**
     * PUBLIC
     * *****************************************************
     *
     */

    /**
     * Get data col template
     * @return DataColTemplate
     */
    public function getDataColTemplate(): DataColTemplate;

    /**
     * Set data col template callback (for edit/style data <td> element)
     * @param callable|null $callback
     * @return $this
     */
    public function setDataColTemplateCallback(?callable $callback): self;


    /**
     * Set cell value callback (for edit cell value) - could return \Nette\Utils\Html
     * @param callable|null $cellValueCallback
     * @return $this
     */
    public function setCellValueCallback(?callable $cellValueCallback): self;


    /**
     * Get title col template (for edit/style title <td> elemtn)
     * @return TitleColTemplate
     */
    public function getTitleColTemplate(): TitleColTemplate;


    /**
     * Set this column as primary
     * @return $this
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
     * Set this column multiple filterable
     * @param bool $multipleFilterable
     * @return IColumn
     */
    public function setMultipleFilterable(bool $multipleFilterable=true): self;


    /**
     * Set this column editable
     * @param bool $editable
     * @return IColumn
     */
    public function setEditable(bool $editable=true): self;


    /**
     * Set this column required
     * @param bool $required
     * @return IColumn
     */
    public function setRequired(bool $required=true): self;


    /**
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



    public function isSortable(): bool;
    public function isFilterable(): bool;
    public function isMultipleFilterable(): bool;
    public function isEditable(): bool;
    public function isRequired(): bool;
    public function isHidden(): bool;

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
     * @return mixed
     * @internal
     */
    public function getCellValueForRendering($row);

    /**
     * Get data col template for rendering - apply callbacks to edit <td> attribute
     * @param mixed $row
     * @return DataColTemplate
     */
    public function getDataColTemplateForRendering($row): DataColTemplate;




}