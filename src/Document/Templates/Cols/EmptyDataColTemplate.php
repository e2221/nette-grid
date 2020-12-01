<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;

use e2221\NetteGrid\Document\Templates\BaseTemplate;
use e2221\NetteGrid\NetteGrid;
use e2221\utils\Html\HrefElement;

class EmptyDataColTemplate extends BaseTemplate
{
    private NetteGrid $netteGrid;
    protected ?string $elementName='td';

    public function __construct(NetteGrid $netteGrid)
    {
        parent::__construct();

        $this->netteGrid = $netteGrid;
    }

    public function beforeRender(): void
    {
        if(count($this->netteGrid->filter) > 0)
            $this->addElement(
                HrefElement::getStatic('a', [], 'Reset filter')
                    ->setLink($this->netteGrid->link('resetFilter!'))
            );
    }
}