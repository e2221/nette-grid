<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


use e2221\NetteGrid\Document\DocumentTemplate;

class DataRowTemplate extends BaseTemplate
{
    protected ?string $elementName='tr';
    private DocumentTemplate $documentTemplate;
    protected bool $sortable=false;

    public function __construct(DocumentTemplate $documentTemplate)
    {
        parent::__construct();
        $this->documentTemplate = $documentTemplate;
    }

    /**
     * Prepare element
     * @param mixed|null $primary
     * @return void
     */
    public function prepareElement($primary=null): void
    {
        if(is_null($primary) == false)
            $this->addDataAttribute('id', $primary);
        parent::prepareElement();
    }

    public function endTemplate(): DocumentTemplate
    {
        return $this->documentTemplate;
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

    /**
     * Make row sortable
     * @param bool $sortable
     * @return DataRowTemplate
     * @internal
     */
    public function setSortable(bool $sortable=true): self
    {
        $this->sortable = $sortable;
        if($sortable === true)
            $this->addDataAttribute('sortable-row');
        return $this;
    }

    /**
     * Is row sortable
     * @return bool
     * @internal
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }
}