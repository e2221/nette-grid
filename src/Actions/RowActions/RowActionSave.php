<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\Actions\BaseAction;

class RowActionSave extends BaseAction
{
    protected ?string $elName = 'a';
    public string $defaultClass = 'btn btn-xs';
    public string $class = 'btn-primary';

    public array $dataAttributes = [
        'submit'        => '',
        'container'     => 'edit'
    ];

    public function __construct(string $name='save', ?string $title = 'Save')
    {
        parent::__construct($name, $title);
    }
}