<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


use e2221\NetteGrid\NetteGrid;

class TbodyTemplate extends BaseTemplate
{
    protected ?string $elementName='tbody';
    public string $defaultClass = 'snippet-container';
    protected bool $sortable=false;
    protected NetteGrid $netteGrid;

    public function __construct(NetteGrid $netteGrid)
    {
        parent::__construct();
        $this->netteGrid = $netteGrid;
    }

    public function beforeRender(): void
    {
        $this->addDataAttribute('dynamic-mask', 'snippet--data-\\d+');
        if($this->sortable === true)
        {
            $this
                ->addDataAttribute('sortable-rows', 'true')
                ->addDataAttribute('sortable-moved-key', sprintf('%s-%s', $this->netteGrid->getUniqueId(), 'movedKey'))
                ->addDataAttribute('sortable-moved-key', sprintf('%s-%s', $this->netteGrid->getUniqueId(), 'beforeKey'))
                ->addDataAttribute('sortable-moved-key', sprintf('%s-%s', $this->netteGrid->getUniqueId(), 'afterKey'));
        }
    }

    /**
     * Mark tbody as jQuery selector
     * @param bool $selectable
     * @return TbodyTemplate
     */
    public function makeRowsSelectable(bool $selectable=true): self
    {
        if($selectable === true)
            $this->addDataAttribute('tbody-selectable', 'true');
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