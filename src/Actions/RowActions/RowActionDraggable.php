<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;
use e2221\NetteGrid\Reflection\ReflectionHelper;

class RowActionDraggable extends RowAction
{
    /** @var null|callable Text that will be used as helper during drag. function($row, $primary):?string  */
    protected $helperCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addDataAttribute('drag-handler');
        $this->setLink('javascript:void(0);');
        $this->addIconElement('fas fa-expand-arrows-alt', [],true);
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $helperFn = $this->helperCallback;
        if(is_callable($helperFn))
        {
            $type = ReflectionHelper::getCallbackParameterType($helperFn, 0);
            $data = ReflectionHelper::getRowCallbackClosure($this->row, $type);
            $this->addDataAttribute('helper-text', $helperFn($data, $this->primary));
        }
    }

    /**
     * Set helper callback
     * @param callable|null $helperCallback
     * @return RowActionDraggable
     */
    public function setHelperCallback(?callable $helperCallback): self
    {
        $this->helperCallback = $helperCallback;
        $this->netteGrid->getDocumentTemplate()->getDataRowTemplate()->setDraggableHelperCallback($helperCallback);
        return $this;
    }

    /**
     * Get helper callback
     * @return callable|null
     */
    public function getHelperCallback(): ?callable
    {
        return $this->helperCallback;
    }
}