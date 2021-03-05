<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;

class RowActionItemModalDetail extends RowAction
{
    protected string $modalTitle='';

    /** @var null|callable Header title callback: function($row, $primary, e2221\BootstrapComponents\Modal\Components\HeaderTitleTemplate $headerTemplate): string|null  */
    protected $headerTitleCallback=null;

    /** @var null|callable Modal content callback: function($row, $primary, e2221\BootstrapComponents\Modal\Modal $modal): void  */
    protected $contentCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title='Show detail')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addSpanElement('fa fa-eye', [], true);
        $this->addDataAttribute('item-detail-modal');
        $this->addDataAttribute('action-id', $this->name);
        $this->addDataAttribute('history', 'false');
        $this->addDataAttribute('transition', 'false');
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this
            ->setLink('javascript:void(0);')
            ->addDataAttribute('id', $this->primary)
            ->addDataAttribute('modal-id', $this->netteGrid->getItemDetailModalId())
            ->addDataAttribute('link', $this->netteGrid->link('itemDetailModal!', $this->name, $this->primary));
    }

    /**
     * Set content callback
     * @param callable|null $contentCallback function($row, $primary, e2221\BootstrapComponents\Modal\Modal $modal): void
     * @return RowActionItemModalDetail
     */
    public function setContentCallback(?callable $contentCallback): self
    {
        $this->contentCallback = $contentCallback;
        return $this;
    }

    /**
     * Set header title callback
     * @param callable|null $headerTitleCallback function($row, $primary, e2221\BootstrapComponents\Modal\Components\HeaderTitleTemplate $headerTemplate): string|null
     * @return RowActionItemModalDetail
     */
    public function setHeaderTitleCallback(?callable $headerTitleCallback): self
    {
        $this->headerTitleCallback = $headerTitleCallback;
        return $this;
    }

    /**
     * @return callable|null
     * @internal
     */
    public function getContentCallback(): ?callable
    {
        return $this->contentCallback;
    }

    /**
     * @return callable|null
     * @internal
     */
    public function getHeaderTitleCallback(): ?callable
    {
        return $this->headerTitleCallback;
    }

    /**
     * Call header title callback - internal
     * @param mixed $row
     * @param mixed $primary
     * @internal
     */
    public function callHeaderTitleCallback($row, $primary): void
    {
        $titleCallback = $this->getHeaderTitleCallback();
        if(is_callable($titleCallback))
        {
            $headerTemplate = $this->netteGrid['itemDetailModal']->getHeaderTitleTemplate();
            $return = $titleCallback($row, $primary, $headerTemplate);
            if(is_string($return))
                $headerTemplate->setTextContent($return);
        }
    }

    /**
     * Call content callback - internal
     * @param mixed $row
     * @param mixed $primary
     * @internal
     */
    public function callContentCallback($row, $primary): void
    {
        $contentCallback = $this->getContentCallback();
        if(is_callable($contentCallback))
        {
            $contentCallback($row, $primary, $this->netteGrid['itemDetailModal']);
        }
    }
}