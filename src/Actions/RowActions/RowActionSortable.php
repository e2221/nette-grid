<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;

class RowActionSortable extends RowAction
{
    /** @var null|callable Sort callback function(NetteGrid $grid, $movedKey, $beforeKey, $afterKey, ?string $senderId):void  */
    protected $onSortCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addDataAttribute('sort-handler');
        $this->addDataAttribute('action-id', $this->name);
        $this->setLink('javascript:void(0);');
        $this->addIconElement('fas fa-arrows-alt-v', [],true);
    }

    /**
     * Set on sort callback
     * @param callable|null $onSortCallback
     * @return RowActionSortable
     */
    public function setOnSortCallback(?callable $onSortCallback): self
    {
        $this->onSortCallback = $onSortCallback;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getOnSortCallback(): ?callable
    {
        return $this->onSortCallback;
    }
}