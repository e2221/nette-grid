<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates;


class WholeDocumentTemplate extends BaseTemplate
{
    const TABLE_RESPONSIVE_CLASS = 'table-responsive';

    protected ?string $elementName='div';

    public function setResponsiveTable(?string $screenWidth=''): self
    {
        if(is_string($screenWidth))
        {
            $this->addClass(sprintf('%s%s',
                self::TABLE_RESPONSIVE_CLASS,
                empty($screenWidth) ? '' : '-' . $screenWidth
            ));
        }
        return $this;
    }
}