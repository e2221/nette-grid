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
        $this->addHtmlAttribute('colspan', (string)$this->netteGrid->getTableColspan());
        $this->addElement('emptyData', $this->netteGrid->getDocumentTemplate()->getEmptyData());
        if($this->netteGrid->showResetFilterButton() === true)
            $this->addElement('link',
                HrefElement::getStatic('a', [], 'Reset filter')
                    ->setLink($this->netteGrid->link('resetFilter!'))
            );
    }
}