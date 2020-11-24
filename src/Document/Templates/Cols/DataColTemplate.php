<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;

use e2221\NetteGrid\Document\Templates\BaseColTemplate;

class DataColTemplate extends BaseColTemplate
{
    protected ?string $elementName='td';

    /**
     * @param null $columnName
     */
    public function prepareElement($columnName=null): void
    {
        $this->addDataAttribute('column', $columnName);
        parent::prepareElement();
    }
}