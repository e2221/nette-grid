<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Document\Templates;


class TitleWrapperTemplate extends BaseTemplate
{
    protected ?string $elementName='div';
    public string $defaultClass='col';
    protected string $class='col-sm-6';
}