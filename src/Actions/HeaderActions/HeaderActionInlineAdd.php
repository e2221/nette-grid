<?php


namespace e2221\NetteGrid\Actions\HeaderActions;


use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;

class HeaderActionInlineAdd extends HeaderAction
{
    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addIconElement('fas fa-plus', [], true);
        $this->addDataAttribute('history', 'false');
        $this->addDataAttribute('transition', 'false');
    }

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