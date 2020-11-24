<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions;


use e2221\utils\Html\HrefElement;

class BaseAction extends HrefElement
{
    /** @var string Name of action */
    public string $name;

    /** @var string|null Title of action */
    public ?string $title=null;

    public function __construct(string $name, ?string $title=null)
    {
        $this->name = $name;
        parent::__construct();
        if(is_string($title))
            $this->addTitle($title);
    }

}