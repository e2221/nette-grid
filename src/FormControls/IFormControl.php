<?php
declare(strict_types=1);

namespace e2221\NetteGrid\FormControls;

use Nette\Forms\Controls\BaseControl;


interface IFormControl
{
    /**
     * Set html type of input
     * @param string $htmlType
     * @return IFormControl
     */
    public function setHtmlType(string $htmlType): IFormControl;

    /**
     * Get new instance of control
     * @param string|null $htmlType
     * @return BaseControl
     */
    public function getControl(?string $htmlType=null): BaseControl;


}