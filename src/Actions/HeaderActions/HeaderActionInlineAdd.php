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
        $this->setLink($this->netteGrid->link('inlineAdd'));
    }
}