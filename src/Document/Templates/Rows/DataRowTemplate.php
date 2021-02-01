<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


use e2221\NetteGrid\Document\DocumentTemplate;

class DataRowTemplate extends BaseTemplate
{
    protected ?string $elementName='tr';
    private DocumentTemplate $documentTemplate;

    /** @var mixed */
    protected $row=null;

    /** @var mixed */
    protected $primary=null;

    /** @var null|callable Draggable helper text callback: function($row, $primary):?string */
    protected $draggableHelperCallback=null;

    public function __construct(DocumentTemplate $documentTemplate)
    {
        parent::__construct();
        $this->documentTemplate = $documentTemplate;
    }

    /**
     * Prepare element
     * @param mixed $row
     * @param mixed $primary
     * @return void
     */
    public function prepareElement($row=null, $primary=null): void
    {
        if(is_null($primary) == false)
            $this->addDataAttribute('id', $primary);

        $draggableHelperFn = $this->draggableHelperCallback;
        if(is_callable($draggableHelperFn))
            $this->addDataAttribute('helper-text', $draggableHelperFn($row, $primary));

        parent::prepareElement();
    }

    public function endTemplate(): DocumentTemplate
    {
        return $this->documentTemplate;
    }

    /**
     * Set draggable helper callback: function($row, $primary):?string
     * @param callable|null $draggableHelperCallback
     * @return DataRowTemplate
     */
    public function setDraggableHelperCallback(?callable $draggableHelperCallback): self
    {
        $this->draggableHelperCallback = $draggableHelperCallback;
        return $this;
    }

    /**
     * Make row jQuery selectable
     * @param bool $selectable
     * @return DataRowTemplate
     * @internal
     */
    public function rowsSelectable(bool $selectable=true): self
    {
        if($selectable === true)
            $this->addDataAttribute('selectable-row');
        return $this;
    }
}