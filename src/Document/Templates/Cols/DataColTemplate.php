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
        ALIGN_LEFT = 'flot-left',
        TEXT_TRUNCATE = 'text-truncate',
        TEXT_LOWERCASE = 'text-lowercase',
        TEXT_UPPERCASE = 'text-uppercase',
        FONT_BOLD = 'font-weight-bold',
        FONT_LIGHT = 'font-weight-light',
        FONT_ITALIC = 'font-italic';

    protected ?string $elementName='td';

    /** @var string[]|null  */
    private ?array $align=null;

    /** @var string[]|null  */
    private ?array $font=null;

    /** @var string[]|null  */
    private ?array $textStyle=null;

    public function beforeRender(): void
    {
        parent::beforeRender();
        if(is_array($this->align)){
            $this->addClass(implode(' ', $this->align));
        }
        if(is_array($this->font)){
            $this->addClass(implode(' ', $this->font));
        }
        if(is_array($this->textStyle)){
            $this->addClass(implode(' ', $this->textStyle));
        }
    }

    /**
     * Set text font - italic
     * @return DataColTemplate
     */
    public function setTextFont_italic(): self
    {
        $this->font[] = self::FONT_ITALIC;
        return $this;
    }

    /**
     * Set text font - light
     * @return DataColTemplate
     */
    public function setTextFont_light(): self
    {
        $this->font[] = self::FONT_LIGHT;
        return $this;
    }

    /**
     * Set text font - bold
     * @return DataColTemplate
     */
    public function setTextFont_bold(): self
    {
        $this->font[] = self::FONT_BOLD;
        return $this;
    }

    /**
     * Set text style - uppercase
     * @return DataColTemplate
     */
    public function setTextStyle_uppercase(): self
    {
        $this->textStyle[] = self::TEXT_UPPERCASE;
        return $this;
    }

    /**
     * Text style - lowercase
     * @return DataColTemplate
     */
    public function setTextStyle_lowercase(): self
    {
        $this->textStyle[] = self::TEXT_LOWERCASE;
        return $this;
    }

    /**
     * Text style - truncate
     * @return DataColTemplate
     */
    public function setTextStyle_truncate(): self
    {
        $this->textStyle[] = self::TEXT_TRUNCATE;
        return $this;
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