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

    public function endTemplate(): DocumentTemplate
    {
        return $this->documentTemplate;
    }

    public function beforeRender(): void
    {
        //unset id attribute
        if(isset($this->attributes['id']))
            unset($this->attributes['id']);
    }
}