<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;
use Nette\Utils\Html;

class RowActionInlineSave extends RowAction
{
    public string $class = 'btn-primary';

    public function __construct(NetteGrid $netteGrid,string $name='__inlineSave', string $title = 'Add')
    {
        parent::__construct($netteGrid, $name);
        $this->setElement(Html::el('input'));
        $this
            ->addHtmlAttribute('type', 'submit')
            ->addHtmlAttribute('value', $title)
            ->addHtmlAttribute('name', 'addSubmit');
    }

    public function render($row = 0, $primary = 0): ?Html
    {
        return parent::render($row, $primary);
    }
}