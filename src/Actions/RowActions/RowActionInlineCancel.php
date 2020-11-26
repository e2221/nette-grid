<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\NetteGrid;
use Nette\Utils\Html;

class RowActionInlineCancel extends RowAction
{
    public function __construct(NetteGrid $netteGrid, string $name='__inlineCancel', ?string $title = 'Cancel')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->setClass('btn-warning');
    }

    public function beforeRender(): void
    {
        $this->setLink($this->netteGrid->link('inlineAdd', false));
        parent::beforeRender();
    }

    public function render($row = 0, $primary = 0): ?Html
    {
        return parent::render($row, $primary);
    }
}