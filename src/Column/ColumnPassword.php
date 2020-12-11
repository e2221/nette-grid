<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


use Contributte\FormsBootstrap\Inputs\TextInput;
use Nette\Forms\Controls\BaseControl;

class ColumnPassword extends Column
{
    protected string $editInputTag='password';

    /**
     * Get input
     * @return BaseControl
     */
    public function getInput(): BaseControl
    {
        if(is_null($this->input))
        {
            $input = new TextInput($this->name);
            $input->setHtmlType('password');
            $input->setHtmlAttribute('class', 'form-control-sm');
        }
        return $this->input;
    }

    /**
     * Set form value
     * @param $cellValue
     * @internal
     */
    public function setFormValue($cellValue): void
    {
        $this->netteGrid['form']['edit'][$this->name]->getControlPrototype()->value = $cellValue;
    }
}