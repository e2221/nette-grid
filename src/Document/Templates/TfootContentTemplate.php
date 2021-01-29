<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


use e2221\NetteGrid\NetteGrid;

class TfootContentTemplate extends BaseTemplate
{
    protected ?string $elementName='td';

    private NetteGrid $netteGrid;

    public function __construct(NetteGrid $netteGrid)
    {
        parent::__construct();
        $this->netteGrid = $netteGrid;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        if((is_null($this->netteGrid->getPaginator()) || $this->netteGrid->getPaginator()->pageCount == 0) && $this->netteGrid->showResetFilterButton() === false)
            $this->setHidden();
        $this->addHtmlAttribute('colspan', (string)$this->netteGrid->getTableColspan());
    }
}