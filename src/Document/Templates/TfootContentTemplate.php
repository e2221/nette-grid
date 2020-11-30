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
        $this->addHtmlAttribute('colspan', $this->netteGrid->getCountOfPrintableColumns() + (int)$this->netteGrid->hasActionColumn());
    }
}