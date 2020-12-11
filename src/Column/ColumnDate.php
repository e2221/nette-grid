<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


use Contributte\FormsBootstrap\Inputs\DateInput;
use Nette\Forms\Controls\BaseControl;

class ColumnDate extends Column
{
    protected string $editInputTag='date';

    /**
     * Get input
     * @return BaseControl
     */
    public function getInput(): BaseControl
    {
        $input = new DateInput($this->name);
        $input->setHtmlAttribute('class', 'form-control-sm');
        return $input;
    }
}