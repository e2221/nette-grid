<?php
declare(strict_types=1);

namespace e2221\NetteGrid\FormControls;



use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\IControl;

class Input implements IFormControl
{
    public string $htmlType='text';

    /**
     * Set html type
     * @param string $htmlType
     * @return IFormControl
     */
    public function setHtmlType(string $htmlType): IFormControl
    {
        $this->setHtmlType($htmlType);
        return $this;
    }

    public function getControl(?string $htmlType = null): IControl
    {
        if($htmlType === 'select')
        {
            $input = $this->getSelectInput();
        }elseif ($htmlType === 'textarea')
        {
            $input = $this->getTextareaInput();
        }else{
            $input = $this->getTextInput();
        }
        $input->setHtmlAttribute('class', 'form-control form-control-sm');
        return $input;
    }

    /**
     * Select
     * @return SelectBox
     */
    protected function getSelectInput(): SelectBox
    {
        return new SelectBox();
    }

    /**
     * Textarea
     * @return TextArea
     */
    protected function getTextareaInput(): TextArea
    {
        return new TextArea();
    }

    /**
     * Text input
     * @return TextInput
     */
    protected function getTextInput(): TextInput
    {
        $input = new TextInput();
        $input->setHtmlType($this->htmlType);
        return $input;
    }
}