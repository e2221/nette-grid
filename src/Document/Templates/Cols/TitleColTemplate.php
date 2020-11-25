<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Document\Templates\Cols;


use e2221\NetteGrid\Document\Templates\BaseTemplate;

class TitleColTemplate extends BaseTemplate
{
    public bool $sticky=false;

    public int $stickyOffset=0;

    protected ?string $elementName='th';

    /**
     * Set sticky header
     * @param bool $sticky
     * @param int $offset
     * @return $this
     */
    public function setStickyHeader(bool $sticky=true, int $offset=0): self
    {
        $this->sticky = $sticky;
        $this->stickyOffset = $offset;
        return $this;
    }

    public function beforeRender(): void
    {
        if($this->sticky === true)
        {
            $this->addClass('column-sticky');
            if($this->stickyOffset > 0)
                $this->addHtmlAttribute('style', sprintf('top:%spx', $this->stickyOffset));
        }
    }
}