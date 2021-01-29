<?php
declare(strict_types=1);

namespace e2221\NetteGrid\GlobalActions;


use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\NetteGrid;
use Nette\Forms\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\SmartObject;

class MultipleFilter
{
    use SmartObject;

    protected string $name;
    protected NetteGrid $netteGrid;
    protected Container $container;

    /** @var IColumn[] Affected columns */
    protected array $columns=[];

    public function __construct(NetteGrid $netteGrid, string $name)
    {
        $this->name = $name;
        $this->netteGrid = $netteGrid;
        $this->container = $this->netteGrid->getMultipleFilterContainer()->addContainer($name);
    }

    /**
     * On add column
     * @param IColumn $column
     * @internal
     */
    public function onAddColumn(IColumn $column): void
    {
        $this->columns[$column->getName()] = $column;
    }

    /**
     * Get container
     * @return Container
     */
    public function getContainer(): Container
    {
        foreach($this->container->getControls() as $containerControl)
        {
            $containerControl->setHtmlAttribute($containerControl instanceof SelectBox ? 'data-autosubmit-select' : 'data-autosubmit');
            $containerControl->setHtmlAttribute('data-container', 'multipleFilterSubmit');
            if($containerControl instanceof SelectBox || $containerControl instanceof TextInput || $containerControl instanceof TextArea)
            {
                $class = $containerControl->getControlPrototype()->class;
                $containerControl->setHtmlAttribute('class', is_null($class) ? 'form-control form-control-sm' : $class);
            }
        }
        return $this->container;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return IColumn[]
     * @internal
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

}