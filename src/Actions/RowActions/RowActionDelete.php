<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;

class RowActionDelete extends RowAction
{
    public string $class='btn-danger';

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addIconElement('far fa-trash-alt', [], true);
        $this->addDataAttribute('history', 'false');
        $this->addDataAttribute('transition', 'false');
    }

    public function beforeRender(): void
    {
        $this->addDataAttribute('dynamic-remove', sprintf('#%s-%s', $this->netteGrid->getSnippetId($this->netteGrid::SNIPPET_TBODY), $this->primary));
        parent::beforeRender();
    }
}