<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;



class ColumnDate extends Column
{
    protected string $editInputTag='date';
    protected string $htmlType='date';
}