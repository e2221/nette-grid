<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;

use e2221\NetteGrid\Document\Templates\BaseColTemplate;

class DataColTemplate extends BaseColTemplate
{
    const
        ALIGN_TOP = 'align-top',
        ALIGN_MIDDLE = 'align-middle',
        ALIGN_BOTTOM = 'align-bottom',
        ALIGN_CENTER = 'text-center',
        ALIGN_RIGHT = 'float-right',
        ALIGN_LEFT = 'flot-left';

    protected ?string $elementName='td';

    /** @var string[]|null  */
    public ?array $align=null;

    public function beforeRender(): void
    {
        parent::beforeRender();
        if(is_array($this->align))
            $this->addClass(implode(' ', $this->align));
    }

    /**
     * Set align class
     * @param string[]|null $align
     * @return DataColTemplate
     */
    public function setAlign(?array $align): self
    {
        $this->align = $align;
        return $this;
    }

    public function setAlignTop(): self
    {
        $this->align[] = self::ALIGN_TOP;
        return $this;
    }

    public function setAlignMiddle(): self
    {
        $this->align[] = self::ALIGN_MIDDLE;
        return $this;
    }

    public function setAlignBottom(): self
    {
        $this->align[] = self::ALIGN_BOTTOM;
        return $this;
    }

    public function setAlignCenter(): self
    {
        $this->align[] = self::ALIGN_CENTER;
        return $this;
    }

    public function setAlignRight(): self
    {
        $this->align[] = self::ALIGN_RIGHT;
        return $this;
    }

    public function setAlignLeft(): self
    {
        $this->align[] = self::ALIGN_LEFT;
        return $this;
    }
}