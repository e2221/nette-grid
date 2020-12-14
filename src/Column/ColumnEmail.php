<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Column;


class ColumnEmail extends Column
{
    protected string $editInputTag='email';
    protected string $htmlType='email';
}