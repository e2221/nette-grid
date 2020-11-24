<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use Nette\Utils\Html;

class RowActionSave extends RowAction
{
    public string $class = 'btn-primary';

    public function __construct(NetteGrid $netteGrid,string $name='save', string $title = 'Save')
    {
        parent::__construct($netteGrid, $name);
        $this->setElement(Html::el('input'));
        $this
            ->addHtmlAttribute('type', 'submit')
            ->addHtmlAttribute('value', $title);
        $this->netteGrid->onAnchor[] = function(NetteGrid $netteGrid)
        {
            $this->addDataAttribute('gridid', $netteGrid->getUniqueId());
            $this->addDataAttribute('cancel');
            $this->addHtmlAttribute('name', $netteGrid['form']['editSubmit']->getName());
        };
    }
}