<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


use e2221\NetteGrid\NetteGrid;

class TbodyTemplate extends BaseTemplate
{
    protected ?string $elementName='tbody';
    public string $defaultClass = 'snippet-container';
    protected NetteGrid $netteGrid;

    public function __construct(NetteGrid $netteGrid)
    {
        parent::__construct();
        $this->netteGrid = $netteGrid;
    }

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
            $this->addDataAttribute('tbody-selectable', 'true');
        return $this;
    }
}