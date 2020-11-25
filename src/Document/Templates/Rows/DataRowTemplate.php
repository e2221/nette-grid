<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


use e2221\NetteGrid\Document\DocumentTemplate;

class DataRowTemplate extends BaseTemplate
{
    protected ?string $elementName='tr';

    private DocumentTemplate $documentTemplate;

    public function __construct(DocumentTemplate $documentTemplate)
    {
        parent::__construct();
        $this->documentTemplate = $documentTemplate;
    }

    /**
     * Prepare element
     * @param mixed|null $primary
     * @return void
     */
    public function prepareElement($primary=null): void
    {
        if(is_null($primary) == false)
            $this->addDataAttribute('id', $primary);
        parent::prepareElement();
    }

    public function endTemplate(): DocumentTemplate
    {
        return $this->documentTemplate;
    }

}