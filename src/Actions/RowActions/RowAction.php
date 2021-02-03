<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;


use e2221\NetteGrid\Actions\BaseAction;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Exceptions\NetteGridException;
use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

class RowAction extends BaseAction implements IRowAction
{
    protected NetteGrid $netteGrid;

    public string $defaultClass='btn btn-xs';
    public string $class='btn-secondary';

    /** @var mixed */
    protected $row;

    /** @var string|int|mixed */
    protected $primary;

    /** @var bool Set action link only ajax */
    public bool $onlyAjaxRequest=true;

    /** @var null|callable function($row, $primary){}: bool  */
    protected $showIfCallback=null;

    /** @var null|callable function($this, $row, $primary){}: void  */
    protected $styleElementCallback=null;

    /** @var null|callable function(NetteGrid $netteGrid, $row, $primary){}: string  */
    protected $linkCallback=null;

    /** @var null|callable function($row, $primary){}: string|null  */
    protected $confirmationCallback=null;

    /** @var string|null Confirmation style from DocumentTemplate */
    public ?string $confirmationStyle=null;

    /** @var null|callable On click callback function(NetteGrid $grid, $row, $primary){}: void */
    protected $onClickCallback=null;

    /** @var bool is multi action (has action items?) */
    protected bool $isMultiAction=false;

    /** @var bool Could this row has multi action? */
    protected bool $couldHaveMultiAction=true;

    /** @var MultiActionItem[] */
    protected array $actions=[];

    protected Html $dropdownMenu;
    protected Html $dropdown;

    /** @var string Confirm text for Nittro confirmation */
    public string $confirmText='Yes';

    /** @var string Cancel text for Nittro confirmation */
    public string $confirmCancelText='No';

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title=null)
    {
        parent::__construct($name, $title);
        $this->netteGrid = $netteGrid;
        $this->dropdownMenu = Html::el('div class="dropdown-menu"');
        $this->dropdown = Html::el('div class=btn-group');
    }

    /**
     * Set confirm text for Nittro confirmation
     * @param string $confirmText
     * @return RowAction
     */
    public function setConfirmText(string $confirmText): self
    {
        $this->confirmText = $confirmText;
        return $this;
    }

    /**
     * Set confirm cancel text for Nittro confirmation
     * @param string $confirmCancelText
     * @return RowAction
     */
    public function setConfirmCancelText(string $confirmCancelText): self
    {
        $this->confirmCancelText = $confirmCancelText;
        return $this;
    }

    /**
     * Set confirmation style [baseConfirmation, nittroConfirmation]
     * @param string $confirmationStyle
     * @return RowAction
     */
    public function setConfirmationStyle(string $confirmationStyle): self
    {
        $this->confirmationStyle = $confirmationStyle;
        return $this;
    }

    /**
     * Get confirmation style
     * @return string
     */
    public function getConfirmationStyle(): string
    {
        return $this->confirmationStyle ?? $this->netteGrid->getDocumentTemplate()->getDefaultConfirmationStyle();
    }

    /**
     * Add multi action
     * @param string $name
     * @param string $title
     * @return MultiActionItem
     * @throws NetteGridException
     */
    public function addMultiActionItem(string $name, string $title): MultiActionItem
    {
        if($this->couldHaveMultiAction === false)
            throw new NetteGridException(sprintf('%s could not have multi-action.', $name));
        $this->isMultiAction = true;
        $this->defaultClass = 'btn btn-xs dropdown-toggle';
        $this
            ->addHtmlAttribute('role', 'button')
            ->addHtmlAttribute('data-toggle', 'dropdown')
            ->addHtmlAttribute('aria-haspopup', 'true')
            ->addHtmlAttribute('aria-expanded', 'false')
            ->setLink('#');
        return $this->actions[$name] = new MultiActionItem($this->netteGrid, $this, $name, $title);
    }

    /**
     * @throws InvalidLinkException
     * @internal
     */
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
            {
                $confirmationStyle = $this->getConfirmationStyle();
                if($confirmationStyle == DocumentTemplate::CONFIRMATION_BASE)
                {
                    $this->setConfirmation($fn($this->row, $this->primary));
                }else if($confirmationStyle == DocumentTemplate::CONFIRMATION_NITTRO){
                    $this->addDataAttribute('prompt', $confirmation);
                    $this->addDataAttribute('confirm', $this->confirmText);
                    $this->addDataAttribute('cancel', $this->confirmCancelText);
                }
            }
        }

        //link - external handler
        if(is_callable($this->linkCallback))
        {
            $fn = $this->linkCallback;
            $this->setLink($fn($this->netteGrid, $this->row, $this->primary));
        }

        //link - callback
        if(is_callable($this->onClickCallback))
        {
            $this->setLink($this->netteGrid->link('rowAction!', $this->name, $this->primary));
        }

        //show if
        if(is_callable($this->showIfCallback))
        {
            $fn = $this->showIfCallback;
            $this->setHidden(!$fn($this->row, $this->primary));
        }
    }

    /**
     * Render rewrite
     * @param mixed|null $row
     * @param int|string|mixed|null $primary
     * @return Html|null
     * @internal
     */
    public function render($row=null, $primary=null): ?Html
    {
        if(is_null($row) === true || is_null($primary) === true)
            return null;
        $this->row = $row;
        $this->primary = $primary;

        if($this->isMultiAction === true)
            return $this->renderMultiActions($row, $primary);

        return parent::render();
    }

    /**
     * Render multi actions
     * @param mixed $row
     * @param mixed $primary
     * @return Html|null
     * @internal
     */
    public function renderMultiActions($row, $primary): ?Html
    {
        $dropdown = clone $this->dropdown;
        $dropdown->addHtml(parent::render());
        $dropdownMenu = clone $this->dropdownMenu;
        foreach($this->actions as $actionName => $action)
        {
            $dropdownMenu->addHtml($action->render($row, $primary));
        }
        $dropdown->addHtml($dropdownMenu);
        return $dropdown;
    }

    /**
     * Set link only ajax or not
     * @param bool $onlyAjaxRequest
     * @return RowAction
     */
    public function setOnlyAjaxRequest(bool $onlyAjaxRequest=true): self
    {
        $this->onlyAjaxRequest = $onlyAjaxRequest;
        return $this;
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

    /**
     * Get dropdown menu
     * @return Html
     */
    public function getDropdownMenu(): Html
    {
        return $this->dropdownMenu;
    }

    /**
     * Get dropdown
     * @return Html
     */
    public function getDropdown(): Html
    {
        return $this->dropdown;
    }

    /**
     * Set on click callback
     * @param callable|null $onClickCallback function(NetteGrid $grid, $row, $primary){}: void
     * @return RowAction
     */
    public function setOnClickCallback(?callable $onClickCallback): self
    {
        $this->onClickCallback = $onClickCallback;
        return $this;
    }

    /**
     * @return callable|null
     * @internal
     */
    public function getOnClickCallback(): ?callable
    {
        return $this->onClickCallback;
    }

    /**
     * @return mixed
     * @internal
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return int|mixed|string
     * @internal
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * @return NetteGrid
     */
    public function getNetteGrid(): NetteGrid
    {
        return $this->netteGrid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }
}