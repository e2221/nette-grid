<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;


use e2221\NetteGrid\Document\Templates\BaseTemplate;

class EmptyDataColTemplate extends BaseTemplate
{
    protected ?string $elName='td';
    public ?string $textContent='Empty result';
}