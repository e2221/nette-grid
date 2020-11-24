<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;

use e2221\NetteGrid\Document\Templates\BaseColTemplate;

class DataColTemplate extends BaseColTemplate
{
    protected ?string $elementName='td';

    /**
     * @param null $columnName
     * @param null $primary
     */
    public function prepareElement($columnName=null, $primary=null): void
    {
        $this->addDataAttribute('column');
        $this->addDataAttribute('name', $columnName);
        $this->addDataAttribute('id', $primary);
        parent::prepareElement();
    }
}