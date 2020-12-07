<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


class EmptyDataRowTemplate extends BaseTemplate
{
    protected ?string $elementName='tr';
    public function beforeRender(): void
    {
        $this->addDataAttribute('no-select');
        parent::beforeRender();
    }
}