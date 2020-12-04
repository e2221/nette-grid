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

    /**
     * Mark tbody as jQuery selector
     * @param bool $selectable
     * @return TbodyTemplate
     */
    public function makeJQuerySelectable(bool $selectable=true): self
    {
        if($selectable === true)
            $this->addDataAttribute('tbody-selectable');
        return $this;
    }
}