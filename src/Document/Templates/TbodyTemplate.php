<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


class TbodyTemplate extends BaseTemplate
{
    protected ?string $elementName='tbody';
    public string $defaultClass = 'snippet-container';
    protected bool $sortable=false;

    public function beforeRender(): void
    {
        $this->addDataAttribute('dynamic-mask', 'snippet--data-\\d+');
    }

    /**
     * Mark tbody as jQuery selector
     * @param bool $selectable
     * @return TbodyTemplate
     */
    public function makeRowsSelectable(bool $selectable=true): self
    {
        if($selectable === true)
            $this->addDataAttribute('tbody-selectable');
        return $this;
    }

    /**
     * Make row sortable
     * @param bool $sortable
     * @return TbodyTemplate
     * @internal
     */
    public function setSortable(bool $sortable=true): self
    {
        $this->sortable = $sortable;
        if($sortable === true)
            $this->addDataAttribute('sortable-rows');
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