<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


class ColumnPassword extends Column
{
    protected string $editInputTag='password';
    protected string $htmlType='password';

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