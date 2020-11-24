<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\Actions\BaseAction;
use e2221\NetteGrid\NetteGrid;
use Nette\Utils\Html;

class RowAction extends BaseAction
{
    protected NetteGrid $netteGrid;

    public string $defaultClass='btn btn-xs';
    public string $class='btn-secondary';

    /** @var mixed */
    protected $row;

    /** @var string|int|mixed */
    protected $primary;

    /** @var null|callable function($row, $primary){}: bool  */
    protected $showIfCallback=null;

    /** @var null|callable function($this, $row, $primary){}: void  */
    protected $styleElementCallback=null;

    /** @var null|callable function(NetteGrid $netteGrid, $row, $primary){}: string  */
    protected $linkCallback=null;

    /** @var null|callable function($row, $primary){}: string|null  */
    protected $confirmationCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title=null, ?string $textContent=null)
    {
        parent::__construct($name, $title, $textContent);
        $this->netteGrid = $netteGrid;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        //style element
        if(is_callable($this->styleElementCallback))
        {
            $fn = $this->styleElementCallback;
            $fn($this, $this->row, $this->primary);
        }

        //confirmation
        if(is_callable($this->confirmationCallback))
        {
            $fn = $this->confirmationCallback;
            $confirmation = $fn($this->row, $this->primary);
            if(is_string($confirmation))
                $this->setConfirmation($fn($this->row, $this->primary));
        }

        //link
        if(is_callable($this->linkCallback))
        {
            $fn = $this->linkCallback;
            $this->setLink($fn($this->netteGrid, $this->row, $this->primary));
        }

        //show if
        if(is_callable($this->showIfCallback))
        {
            $fn = $this->showIfCallback;
            $this->setHidden($fn($this->row, $this->primary));
        }
    }

    /**
     * Render rewrite
     * @param mixed|null $row
     * @param int|string|mixed|null $primary
     * @return Html|null
     */
    public function render($row=null, $primary=null): ?Html
    {
        if(is_null($row) === true || is_null($primary) === true)
            return null;
        $this->row = $row;
        $this->primary = $primary;
        return parent::render();
    }

    /**
     * Set show if callback
     * @param callable|null $showIfCallback
     * @return RowAction
     */
    public function setShowIfCallback(?callable $showIfCallback): self
    {
        $this->showIfCallback = $showIfCallback;
        return $this;
    }

    /**
     * Set confirmation callback
     * @param callable|null $confirmationCallback
     * @return RowAction
     */
    public function setConfirmationCallback(?callable $confirmationCallback): self
    {
        $this->confirmationCallback = $confirmationCallback;
        return $this;
    }

    /**
     * Set link callback
     * @param callable|null $linkCallback
     * @return RowAction
     */
    public function setLinkCallback(?callable $linkCallback): self
    {
        $this->linkCallback = $linkCallback;
        return $this;
    }

    /**
     * Set style element callback
     * @param callable|null $styleElementCallback
     * @return RowAction
     */
    public function setStyleElementCallback(?callable $styleElementCallback): self
    {
        $this->styleElementCallback = $styleElementCallback;
        return $this;
    }


}