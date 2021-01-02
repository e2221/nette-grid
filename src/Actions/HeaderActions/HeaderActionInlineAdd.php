<?php


namespace e2221\NetteGrid\Actions\HeaderActions;


use Nette\Application\UI\InvalidLinkException;

class HeaderActionInlineAdd extends HeaderAction
{
    /**
     * @throws InvalidLinkException
     */
    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->setLink($this->netteGrid->link('inlineAdd', ['inlineAdd' => true]));
    }

    /**
     * Set on add callback
     * @param callable $onAddCallback function(ArrayHash $values): void
     * @return HeaderActionInlineAdd
     */
    public function setOnAddCallback(callable $onAddCallback): self
    {
        $this->netteGrid->setOnAddCallback($onAddCallback);
        return $this;
    }
}