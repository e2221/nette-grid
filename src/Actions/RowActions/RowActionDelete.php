<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;

class RowActionDelete extends RowAction
{
    public function beforeRender(): void
    {
        $this->addIconElement('fas fa-trash-alt', [], true);
        $this->addDataAttribute('dynamic-remove', sprintf('%s-%s-%s', $this->netteGrid->getUniqueId(), $this->netteGrid::SNIPPET_TBODY, $this->primary));
        parent::beforeRender();
    }
}