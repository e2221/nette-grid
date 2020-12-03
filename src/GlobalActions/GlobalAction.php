<?php
declare(strict_types=1);

namespace e2221\NetteGrid\GlobalActions;


use e2221\NetteGrid\NetteGrid;
use Nette\Forms\Container;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;

class GlobalAction
{
    protected NetteGrid $netteGrid;
    protected string $name;
    protected ?string $label;
    protected ?Container $formContainer;
    protected SubmitButton $actionSubmit;
    protected string $containerName;

    /** @var callable|null on submit callback function(ArrayHash $selectedRows) */
    protected $onSubmit=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $label)
    {
        $this->netteGrid = $netteGrid;
        $this->name = $name;
        $this->label = $label;
        $this->containerName = sprintf('global_%s', $name);
        $this->formContainer = $netteGrid['form']->addContainer($this->containerName);
        $this->actionSubmit = $this->formContainer->addSubmit('submit', 'Execute');
        $this->actionSubmit->setValidationScope([$netteGrid['form'][$this->containerName]]);
    }

    /**
     * Set on submit
     * @param callable $onSubmit
     * @return GlobalAction
     */
    public function setOnSubmit(callable $onSubmit): self
    {
        $this->onSubmit = $onSubmit;
        $this->actionSubmit->onClick[] = [$this, 'onSubmitContainer'];
        return $this;
    }

    /**
     * @internal
     * @param Button $button
     */
    public function onSubmitContainer(Button $button): void
    {
        $form = $button->getForm();
        $values = $form->getValues();
        $values = $values->globalActions;

        if(is_callable($this->onSubmit))
        {
            $onSubmitFn = $this->onSubmit;
            $onSubmitFn($values->rowCheck ?? ArrayHash::from([]));
        }
    }

    /**
     * Get form container
     * @return Container
     */
    public function getFormContainer():Container
    {
        return $this->formContainer;
    }

}