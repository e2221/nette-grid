<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\HeaderActions;


use e2221\NetteGrid\Actions\BaseAction;
use e2221\NetteGrid\NetteGrid;

class HeaderAction extends BaseAction
{
    protected NetteGrid $netteGrid;

    public string $defaultClass='btn btn-xs';
    public string $class='btn-secondary';

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title = null)
    {
        $this->netteGrid = $netteGrid;
        parent::__construct($name, $title);
    }

    /**
     * Set confirmation with Nittro style
     * @param string|null $prompt
     * @param string|null $confirm
     * @param string|null $cancel
     * @return $this
     */
    public function setNittroConfirmation(?string $prompt=null, ?string $confirm=null, ?string $cancel=null): self
    {
        $this->addDataAttribute('prompt', $prompt);
        if(is_string($confirm))
            $this->addDataAttribute('confirm', $confirm);
        if(is_string($cancel))
            $this->addDataAttribute('cancel', $cancel);
        return $this;
    }
}