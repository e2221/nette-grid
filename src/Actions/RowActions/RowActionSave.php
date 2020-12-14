<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use Nette\Utils\Html;

class RowActionSave extends RowAction
{
    public string $class = 'btn-primary';
    protected bool $couldHaveMultiAction=false;

    public function __construct(NetteGrid $netteGrid,string $name='save', string $title = 'Save')
    {
        parent::__construct($netteGrid, $name);
        $this->setElement(Html::el('input'));
        $this
            ->addHtmlAttribute('type', 'submit')
            ->addHtmlAttribute('value', $title);
    }

    public function beforeRender(): void
    {
        $this->addDataAttribute('save');
        $this->addHtmlAttribute('name', $this->netteGrid['form']['editSubmit']->getName());
        $this->addDataAttribute('row-id', $this->primary);
    }
}