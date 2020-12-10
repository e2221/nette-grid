<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

class ColumnTextarea extends Column
{
    /**
     * Get input
     * @return BaseControl
     */
    public function getInput(): BaseControl
    {
        $input = new TextArea($this->name);
        $input->setHtmlAttribute('class', 'form-control-sm');
        return $input;
    }
}