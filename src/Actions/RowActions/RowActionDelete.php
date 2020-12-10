<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;

class RowActionDelete extends RowAction
{
    public string $class='btn-danger';

    public function beforeRender(): void
    {
        $this->addIconElement('far fa-trash-alt', [], true);
        $this->addDataAttribute('data-history', "false");
        $this->addDataAttribute('dynamic-remove', sprintf('#%s-%s', $this->netteGrid->getSnippetId($this->netteGrid::SNIPPET_TBODY), $this->primary));
        parent::beforeRender();
    }
}