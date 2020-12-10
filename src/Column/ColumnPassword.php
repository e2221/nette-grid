<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


use Contributte\FormsBootstrap\Inputs\TextInput;
use Nette\Forms\Controls\BaseControl;

class ColumnPassword extends Column
{
    /**
     * Get input
     * @return BaseControl
     */
    public function getInput(): BaseControl
    {
        $input = new TextInput($this->name);
        $input->setHtmlType('password');
        $input->setHtmlAttribute('class', 'form-control-sm');
        return $input;
    }
}