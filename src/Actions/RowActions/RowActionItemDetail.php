<?php
declare(strict_types=1);


namespace e2221\NetteGrid\Actions\RowAction;

use e2221\NetteGrid\NetteGrid;
use Nette\Application\UI\InvalidLinkException;

class RowActionItemDetail extends RowAction
{
    public string $class = 'btn-secondary';
    protected bool $couldHaveMultiAction=false;

    /** @var null|callable Detail callback function($row, $primary): string|Nette\Utils\Html|e2221\utils\BaseElement */
    protected $detailCallback=null;

    public function __construct(NetteGrid $netteGrid, string $name, ?string $title='Show detail')
    {
        parent::__construct($netteGrid, $name, $title);
        $this->addSpanElement('fa fa-eye', [], true);
        $this->addDataAttribute('item-detail-toggle');
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
     * Set detail callback
     * @param callable|null $detailCallback
     * @return RowActionItemDetail
     */
    public function setDetailCallback(?callable $detailCallback): self
    {
        $this->detailCallback = $detailCallback;
        return $this;
    }
}