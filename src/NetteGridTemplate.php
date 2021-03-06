<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\HeaderActions\HeaderModalAction;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Actions\RowAction\RowActionCancel;
use e2221\NetteGrid\Actions\RowAction\RowActionEdit;
use e2221\NetteGrid\Actions\RowAction\RowActionItemDetail;
use e2221\NetteGrid\Actions\RowAction\RowActionItemModalDetail;
use e2221\NetteGrid\Actions\RowAction\RowActionSave;
use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Document\Templates\Cols\DataActionsColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\EmptyDataColTemplate;
use e2221\NetteGrid\Document\Templates\Cols\HeaderActionsColTemplate;
use e2221\NetteGrid\Document\Templates\EmptyDataRowTemplate;
use e2221\NetteGrid\Document\Templates\HeadFilterRowTemplate;
use e2221\NetteGrid\Document\Templates\PreGlobalActionSelectionTemplate;
use e2221\NetteGrid\Document\Templates\TableTemplate;
use e2221\NetteGrid\Document\Templates\TbodyTemplate;
use e2221\NetteGrid\Document\Templates\TfootContentTemplate;
use e2221\NetteGrid\Document\Templates\TfootTemplate;
use e2221\NetteGrid\Document\Templates\TheadTemplate;
use e2221\NetteGrid\Document\Templates\TitlesRowTemplate;
use e2221\NetteGrid\Document\Templates\TitleTemplate;
use e2221\NetteGrid\Document\Templates\TitleWrapperTemplate;
use e2221\NetteGrid\Document\Templates\TopActionsWrapperTemplate;
use e2221\NetteGrid\Document\Templates\TopRowTemplate;
use e2221\NetteGrid\Document\Templates\WholeDocumentTemplate;
use e2221\NetteGrid\GlobalActions\GlobalAction;
use e2221\NetteGrid\GlobalActions\MultipleFilter;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Forms\Container;
use Nette\Utils\Paginator;


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

    /** @var mixed|null */
    public $data;

    /** @var null|int|string  */
    public $editRowKey;

    /** @var RowAction[] */
    public array $rowActions;

    /** @var HeaderAction[] */
    public array $headerActions;

    /** @var mixed[] */
    public array $filter;

    /** @var GlobalAction[] */
    public array $globalActions;

    /** @var MultipleFilter[] */
    public array $multipleFilters;

    /** @var mixed */
    public $itemDetailKey=null;

    /** @var RowActionItemDetail[] */
    public array $itemDetails;

    /** @var RowActionItemModalDetail[] */
    public array $itemDetailsModal;

    /** @var HeaderModalAction[] */
    public array $headerModalActions;

    /** @var HeaderAction[] */
    public array $topActions;

    public ?Paginator $paginator;
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
    public TopRowTemplate $topRowTemplate;
    public TopActionsWrapperTemplate $topActionsWrapperTemplate;
    public TitleWrapperTemplate $titleWrapperTemplate;
    public TitleTemplate $titleTemplate;
    public PreGlobalActionSelectionTemplate $preGlobalActionSelectionTemplate;

    public bool $editMode;
    public bool $showEmptyResult;
    public bool $isFilterable;
    public bool $hasActionsColumn;
    public bool $isEditable;

    /** @var mixed[] */
    public array $rowActionsOrder;

    public string $primaryColumn;
    public ?string $editColumn;
    public bool $hiddenHeader;
    public bool $isAddable;
    public bool $inlineAdd;
    public int $countOfColumns;
    public ?string $sortByColumn;
    public ?string $sortDirection;
    public bool $hasGlobalAction;
    public int $tableColspan;
    public ?string $selectedGlobalAction;
    public ?string $globalActionContainerName;
    public ?Container $globalActionContainer;
    public bool $hasMultipleFilter;
    public ?Container $multipleFilterContainer;
    public bool $showResetFilterButton;
    public bool $hasItemDetail;
    public ?string $itemDetailAction=null;
    public ?string $sortableScope;
    public ?string $draggableScope;
    public ?string $droppableScope;
    public ?string $droppableEffect;
    public bool $hasItemModalDetail;
    public bool $hasHeaderModalAction;
    public bool $hasTopActions;
    public bool $hasTitle;
    public ?string $globalActionSelectionPrompt;
}