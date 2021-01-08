<?php
declare(strict_types=1);

namespace e2221\NetteGrid\GlobalActions;


use e2221\NetteGrid\NetteGrid;
use Nette\Application\AbortException;
use Nette\Forms\Container;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class GlobalAction
{
    protected NetteGrid $netteGrid;
    protected string $name;
    protected ?string $label;
    protected ?Container $formContainer;
    protected SubmitButton $actionSubmit;
    protected string $containerName;

    /** @var callable|null on submit callback: function(ArrayHash $selectedRows, ArrayHash $containerValues, NetteGrid $netteGrid): void */
    protected $onSubmit=null;

    use SmartObject;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $label)
    {
        $this->netteGrid = $netteGrid;
        $this->name = $name;
        $this->label = $label ?? ucfirst($name);
        $this->containerName = sprintf('global_%s', $name);
        $this->formContainer = $netteGrid['form']->addContainer($this->containerName);
        $this->actionSubmit = $this->formContainer->addSubmit('submit', 'Execute');
        $this->actionSubmit->setValidationScope([$netteGrid['form'][$this->containerName]]);
    }

    /**
     * Set on submit
     * @param callable $onSubmit on submit callback: function(ArrayHash $selectedRows, ArrayHash $containerValues, NetteGrid $netteGrid): void
     * @return GlobalAction
     */
    public function setOnSubmit(callable $onSubmit): self
    {
        $this->onSubmit = $onSubmit;
        $this->actionSubmit->onClick[] = [$this, 'onSubmitContainer'];
        return $this;
    }

    /**
     * @param Button $button
     * @throws AbortException
     * @internal
     */
    public function onSubmitContainer(Button $button): void
    {
        $form = $button->getForm();
        $checkedRows = ArrayHash::from($form->getHttpData($form::DATA_TEXT, 'globalActions[rowCheck][]'));
        $containerName = $this->containerName;
        $containerValues = $form->getValues()->$containerName;

        $this->netteGrid->reload(NetteGrid::SNIPPET_DOCUMENT_AREA);
        if(is_callable($this->onSubmit))
        {
            $onSubmitFn = $this->onSubmit;
            $onSubmitFn($checkedRows, $containerValues, $this->netteGrid);
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

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get label
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Get submit button
     * @return SubmitButton
     */
    public function getActionSubmit(): SubmitButton
    {
        return $this->actionSubmit;
    }
}