<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


class TbodyTemplate extends BaseTemplate
{
    protected ?string $elName = 'tbody';
    public string $defaultClass = 'snippet-container';
    public array $dataAttributes = ['dynamic-mask' => 'snippet--data-\\d+'];

}