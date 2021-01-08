<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Document\Templates;


class PreGlobalActionSelectionTemplate extends BaseTemplate
{
    protected ?string $elementName='label';
    protected string $class='my-1 mr-2';

    public function __construct()
    {
        parent::__construct();
        $this->setTextContent('Selected rows:');
    }
}