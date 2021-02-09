<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use e2221\utils\Html\BaseElement;
use Nette\Application\UI\Component;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;

class RowActionItemDetail extends RowAction
{
    public string $class = 'btn-secondary';
    protected bool $couldHaveMultiAction=false;

    /** @var null|callable Detail callback function($row, $primary): string|Nette\Utils\Html|e2221\utils\BaseElement|IComponent*/
    protected $detailCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title='Show detail')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addSpanElement('fa fa-eye', [], true);
        $this->addDataAttribute('item-detail-toggle');
        $this->addDataAttribute('action-id', $this->name);
        $this->addDataAttribute('history', 'false');
        $this->addDataAttribute('transition', 'false');
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this
            ->setLink('javascript:void(0);')
            ->addDataAttribute('id', $this->primary)
            ->addDataAttribute('link', $this->netteGrid->link('itemDetail!', $this->name, $this->primary));
    }

    /**
     * Set detail callback: function($row, $primary): string|Nette\Utils\Html|e2221\utils\BaseElement|IComponent
     * @param callable|null $detailCallback
     * @return RowActionItemDetail
     */
    public function setDetailCallback(?callable $detailCallback): self
    {
        $this->detailCallback = $detailCallback;

        //attach component if is not attached
        if($this->detailCallback instanceof Component && is_null($this->detailCallback->getPresenterIfExists()))
            $this->netteGrid->addComponent($this->detailCallback, 'itemDetail_' . $this->name);

        return $this;
    }

    /**
     * Get detail
     * @return null|string|BaseElement|Html|IComponent
     * @internal
     */
    public function renderItemDetail()
    {
        if(is_callable($this->detailCallback))
        {
            $detailFn = $this->detailCallback;
            $detail = $detailFn($this->row, $this->primary);
            if($detail instanceof Component)
            {
                if(method_exists($detail, 'render'))
                {
                    return $detail->render();
                }
                return null;
            }else{
                return $detail;
            }
        }
        return null;
    }
}