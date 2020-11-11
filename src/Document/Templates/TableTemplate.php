<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


class TableTemplate extends BaseTemplate
{
    protected ?string $elName = 'table';
    public string $defaultClass = 'table table-sm';
}