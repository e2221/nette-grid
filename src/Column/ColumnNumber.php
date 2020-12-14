<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


use Contributte\FormsBootstrap\Inputs\TextInput;
use Nette\Forms\Controls\BaseControl;

class ColumnNumber extends Column
{
    protected string $editInputTag='number';
    protected string $htmlType='number';
}