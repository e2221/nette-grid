<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Actions\RowAction\RowActionCancel;
use e2221\NetteGrid\Actions\RowAction\RowActionEdit;
use e2221\NetteGrid\Actions\RowAction\RowActionSave;
use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Document\Templates\Cols\DataActionsColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\EmptyDataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\HeaderActionsColTemplate;
use e2221\NetteGrid\Document\Templates\EmptyDataRowTemplate;
use e2221\NetteGrid\Document\Templates\HeadFilterRowTemplate;
use e2221\NetteGrid\Document\Templates\TableTemplate;
use e2221\NetteGrid\Document\Templates\TbodyTemplate;
use e2221\NetteGrid\Document\Templates\TfootContentTemplate;
use e2221\NetteGrid\Document\Templates\TfootTemplate;
use e2221\NetteGrid\Document\Templates\TheadTemplate;
use e2221\NetteGrid\Document\Templates\TitlesRowTemplate;
use e2221\NetteGrid\Document\Templates\WholeDocumentTemplate;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;


/**
 * @method bool isLinkCurrent(string $destination = null, $args = [])
 * @method bool isModuleCurrent(string $module)
 */
class NetteGridTemplate extends Template
{
    /** @var string[]  */
    public array $flashes;

    /** @var string[] */
    public array $templates;

    /** @var IColumn[] */
    public array $columns;

    /** @var mixed[]|null */
    public ?array $data;

    /** @var null|int|string  */
    public $editRowKey;

    /** @var RowAction[] */
    public array $rowActions;

    /** @var HeaderAction[] */
    public array $headerActions;

    /** @var mixed[] */
    public array $filter;

    public NetteGrid $control;
    public Presenter $presenter;
    public string $uniqueID;
    public DocumentTemplate $documentTemplate;
    public TableTemplate $tableTemplate;
    public TheadTemplate $theadTemplate;
    public TitlesRowTemplate $theadTitlesRowTemplate;
    public EmptyDataRowTemplate $emptyDataRowTemplate;
    public EmptyDataColTemplate $emptyDataColTemplate;
    public TbodyTemplate $tbodyTemplate;
    public HeadFilterRowTemplate $headFilterRowTemplate;
    public HeaderActionsColTemplate $headerActionsColumnTemplate;
    public WholeDocumentTemplate $wholeDocumentTemplate;
    public DataActionsColTemplate $dataActionsColumnTemplate;
    public RowActionCancel $rowActionCancel;
    public RowActionSave $rowActionSave;
    public RowActionEdit $rowActionEdit;
    public TfootTemplate $tfootTemplate;
    public TfootContentTemplate $tfootContentTemplate;

    public bool $editMode;
    public bool $showEmptyResult;
    public bool $isFilterable;
    public bool $hasActionsColumn;
    public bool $isEditable;
    public array $rowActionsOrder;
    public string $primaryColumn;
    public ?string $editColumn;
    public bool $hiddenHeader;
    public bool $isAddable;
    public bool $inlineAdd;
    public int $countOfColumns;
}