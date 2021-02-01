<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use e2221\utils\Html\BaseElement;
use Nette\Utils\Html;

class RowActionItemDetail extends RowAction
{
    public string $class = 'btn-secondary';
    protected bool $couldHaveMultiAction=false;

    /** @var null|callable Detail callback function($row, $primary): string|Nette\Utils\Html|e2221\utils\BaseElement */
    protected $detailCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title='Show detail')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addSpanElement('fa fa-eye', [], true);
        $this->addDataAttribute('item-detail-toggle');
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
            ->addDataAttribute('link', $this->netteGrid->link('itemDetail!', $this->name, $this->primary));
    }

    /**
     * Set detail callback
     * @param callable|null $detailCallback
     * @return RowActionItemDetail
     */
    public function setDetailCallback(?callable $detailCallback): self
    {
        $this->detailCallback = $detailCallback;
        return $this;
    }

    /**
     * Get detail
     * @return null|string|BaseElement|Html
     * @internal
     */
    public function renderItemDetail()
    {
        if(is_callable($this->detailCallback))
        {
            $detailFn = $this->detailCallback;
            return $detailFn($this->row, $this->primary);
        }
        return null;
    }
}