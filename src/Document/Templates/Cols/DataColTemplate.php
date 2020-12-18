<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;

use e2221\NetteGrid\Document\Templates\BaseColTemplate;

class DataColTemplate extends BaseColTemplate
{
    const
        ALIGN_BASELINE = 'align-baseline',
        ALIGN_TOP = 'align-top',
        ALIGN_MIDDLE = 'align-middle',
        ALIGN_BOTTOM = 'align-bottom',
        ALIGN_TEXT_TOP = 'align-text-top',
        ALIGN_TEXT_BOTTOM = 'align-text-bottom';

    protected ?string $elementName='td';
    public ?string $align=null;

    public function beforeRender(): void
    {
        parent::beforeRender();
        if(is_string($this->align))
            $this->addClass($this->align);
    }

    public function setAlignBaseline(): self
    {
        $this->align = self::ALIGN_BASELINE;
        return $this;
    }

    public function setAlignTop(): self
    {
        $this->align = self::ALIGN_TOP;
        return $this;
    }

    public function setAlignMiddle(): self
    {
        $this->align = self::ALIGN_MIDDLE;
        return $this;
    }

    public function setAlignBottom(): self
    {
        $this->align = self::ALIGN_BOTTOM;
        return $this;
    }

    public function setAlignTextTop(): self
    {
        $this->align = self::ALIGN_TEXT_TOP;
        return $this;
    }

    public function setAlignTextBottom(): self
    {
        $this->align = self::ALIGN_TEXT_BOTTOM;
        return $this;
    }
}