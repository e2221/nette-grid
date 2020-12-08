<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;
use Nette\Utils\Html;

interface IRowAction
{
    /**
     * Add as multi-action item
     * @param string $name
     * @param string $title
     * @return MultiActionItem
     */
    public function addMultiActionItem(string $name, string $title): MultiActionItem;

    /** @internal  */
    public function beforeRender(): void;

    /**
     * Render
     * @param null $row
     * @param null $primary
     * @return Html|null
     * @internal
     */
    public function render($row=null, $primary=null): ?Html;

    /**
     * Render multi-actions
     * @param $row
     * @param $primary
     * @return Html|null
     * @internal
     */
    public function renderMultiActions($row, $primary): ?Html;

    /**
     * Set show-if callback function($row, $primary){}:bool
     * @param callable|null $showIfCallback
     * @return RowAction
     */
    public function setShowIfCallback(?callable $showIfCallback): self;

    /**
     * Set confirmation callback function($row, $primary){}:string|null (null=no confirmation will be displayed)
     * @param callable|null $confirmationCallback
     * @return RowAction
     */
    public function setConfirmationCallback(?callable $confirmationCallback): self;

    /**
     * Set link callback function(NetteGrid $grid, $row, $primary){}:
     * @param callable|null $linkCallback
     * @return RowAction
     */
    public function setLinkCallback(?callable $linkCallback): self;

    /**
     * Style element with callback
     * @param callable|null $styleElementCallback function(RowAction $this, $row, $primary){}: void
     * @return RowAction
     */
    public function setStyleElementCallback(?callable $styleElementCallback): self;

    /**
     * Get dropdown menu for styling
     * @return Html
     */
    public function getDropdownMenu(): Html;

    /**
     * Get dropdown to styling
     * @return Html
     */
    public function getDropdown(): Html;

    /**
     * Set on-click callback (will be preferred then linkCallback) function(NetteGrid $grid, $row, $primary){}:void
     * @param callable|null $onClickCallback
     * @return RowAction
     */
    public function setOnClickCallback(?callable $onClickCallback): self;

    /**
     * Get on-click callback
     * @return callable|null
     * @internal
     */
    public function getOnClickCallback(): ?callable;

    /**
     * Get row
     * @return mixed
     * @internal
     */
    public function getRow();

    /**
     * Get primary value
     * @return mixed
     * @internal
     */
    public function getPrimary();

    /**
     * Get Nette grid
     * @return NetteGrid
     */
    public function getNetteGrid(): NetteGrid;
}