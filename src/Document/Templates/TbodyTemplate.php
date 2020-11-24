<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


class TbodyTemplate extends BaseTemplate
{
    protected ?string $elementName='tbody';
    public string $defaultClass = 'snippet-container';
    public function beforeRender(): void
    {
        $this->addDataAttribute('dynamic-mask', 'snippet--data-\\d+');
    }
}