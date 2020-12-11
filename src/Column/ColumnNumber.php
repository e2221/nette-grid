<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


use Contributte\FormsBootstrap\Inputs\TextInput;
use Nette\Forms\Controls\BaseControl;

class ColumnNumber extends Column
{
    protected string $editInputTag='number';

    /**
     * Get input
     * @return BaseControl
     */
    public function getInput(): BaseControl
    {
        if(is_null($this->input))
        {
            $input = new TextInput($this->name);
            $input->setHtmlType('number');
            $input->setHtmlAttribute('class', 'form-control-sm');
            return $input;
        }
        return $this->input;
    }
}