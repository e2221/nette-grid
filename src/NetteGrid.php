<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use Contributte\Application\Response\CSVResponse;
use Contributte\FormsBootstrap\BootstrapForm;
use e2221\BootstrapComponents\Modal\Modal;
use e2221\BootstrapComponents\Pagination\Pagination;
use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\HeaderActions\HeaderActionDisableEdit;
use e2221\NetteGrid\Actions\HeaderActions\HeaderActionExport;
use e2221\NetteGrid\Actions\HeaderActions\HeaderActionInlineAdd;
use e2221\NetteGrid\Actions\RowAction\IRowAction;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Actions\RowAction\RowActionDelete;
use e2221\NetteGrid\Actions\RowAction\RowActionDraggable;
use e2221\NetteGrid\Actions\RowAction\RowActionItemDetail;
use e2221\NetteGrid\Actions\RowAction\RowActionItemModalDetail;
use e2221\NetteGrid\Actions\RowAction\RowActionSortable;
use e2221\NetteGrid\Column\Column;
use e2221\NetteGrid\Column\ColumnDate;
use e2221\NetteGrid\Column\ColumnEmail;
use e2221\NetteGrid\Column\ColumnNumber;
use e2221\NetteGrid\Column\ColumnPassword;
use e2221\NetteGrid\Column\ColumnPrimary;
use e2221\NetteGrid\Column\ColumnSelect;
use e2221\NetteGrid\Column\ColumnText;
use e2221\NetteGrid\Column\ColumnTextarea;
use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Exceptions\ColumnNotFoundException;
use e2221\NetteGrid\Exceptions\NetteGridException;
use e2221\NetteGrid\GlobalActions\GlobalAction;
use e2221\NetteGrid\GlobalActions\MultipleFilter;
use e2221\utils\Html\BaseElement;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\Button;
use Nette\Utils\ArrayHash;
use Nette\Utils\Paginator;

/**
 * Class NetteGrid
 * @persistent(pagination)
 */
class NetteGrid extends Control
{
    const
        SNIPPET_DOCUMENT_AREA = 'documentArea',
        SNIPPET_ALL_CONTENT = 'gridContent',
        SNIPPET_TBODY = 'data',
        SNIPPET_ITEMS_AREA = 'dataItems',
        SNIPPET_TFOOT_AREA = 'footerArea',
        SNIPPET_TFOOT = 'footer',
        SNIPPET_HEADER = 'head',
        SNIPPET_HEAD_TITLES = 'headTitles',
        SNIPPET_GLOBAL_ACTION_CONTAINER = 'global-action-container',
        SNIPPET_PATH_ITEM_DETAIL = 'itemDetail',
        SNIPPET_HEAD_ACTIONS = 'headActions',
        SNIPPET_ITEM_DETAIL_MODAL = 'itemDetailsModal';


    /** @var IColumn[] */
    protected array $columns=[];

    /** @var array @persistent */
    public array $filter=[];

    /** @var HeaderAction[] */
    protected array $headerActions=[];

    /** @var RowAction[] */
    protected array $rowActions=[];

    /** @var array */
    protected array $rowActionsOrder=[];

    /** @var string[] Templates with changed blocks */
    protected array $templates=[];

    /** @var string Primary column name */
    protected string $primaryColumn='id';

    /** @var null|callable function(?array $filter=null, ?array $multipleFilter=null, ?array $orderBy=null, ?Paginator $paginator=null){} */
    protected $dataSourceCallback=null;

    /** @var DocumentTemplate include all document template */
    protected DocumentTemplate $documentTemplate;

    /** @var Container|null Filter container */
    protected ?Container $filterContainer=null;

    /** @var Container|null Edit container */
    protected ?Container $editContainer=null;

    /** @var Container|null Add container */
    protected ?Container $addContainer=null;

    /** @var Container|null Paginate container */
    protected ?Container $paginateContainer=null;

    /** @var Container|null Global actions container */
    protected ?Container $globalActionsContainer=null;

    /** @var Container|null Multiple filter container */
    protected ?Container $multipleFilterContainer=null;

    /** @var null|int|string @persistent Edit key */
    public $editKey=null;

    /** @var mixed|null */
    protected $data=null;

    /** @var null|callable Function that will be called after submit edit function(ArrayHash $values, $primary) */
    protected $onEditCallback=null;

    /** @var null|callable After submit inline add function(ArrayHash $values) */
    protected $onAddCallback=null;

    /** @var bool @persistent Active edit mode [true = edit is enable] */
    public bool $editMode=false;

    /** @var bool|null @persistent Enable/disable showing edit buttons */
    public ?bool $editEnabled=null;

    /** @var bool Is there at least one filterable column? */
    protected bool $isFilterable=false;

    /** @var bool Is there at least one editable column? (In line) */
    protected bool $isEditable=false;

    /** @var bool Is at least one editable column in column? */
    protected bool $isEditableInColumn=false;

    /** @var bool Is there at least one addable column? */
    protected bool $isAddable=false;

    /** @var bool @persistent */
    public bool $inlineAdd=false;

    /** @var string|null Show all option - for case null => option will not be show */
    protected ?string $showAllOption='All';

    /** @var callable|null Total items count callback function(array $filter, array $multipleFilter):int{} */
    protected $totalItemsCountCallback=null;

    /** @var int Default items per page */
    protected int $itemsPerPage=50;

    /** @var array|null Items per page selection - for case null => selection will not be show */
    protected ?array $itemsPerPageSelection=null;

    /** @var Paginator|null  */
    protected ?Paginator $paginator=null;

    /** @var int @persistent */
    public int $page=1;

    /** @var bool All main form autocomplete */
    protected bool $autocomplete=false;

    /** @var bool Autocomplete for form parts */
    protected bool $filterAutocomplete=false;
    protected bool $editAutocomplete=false;
    protected bool $addAutocomplete=false;

    /** @var string|null @persistent Sort by column */
    public ?string $sortByColumn=null;

    /** @var string|null @persistent Sort direction */
    public ?string $sortDirection=null;

    /** @var GlobalAction[] */
    protected array $globalActions=[];

    /** @var bool jQuery - selectable rows */
    protected bool $rowsSelectable=true;

    /** @var MultipleFilter[] Multiple filters  */
    public array $multipleFilters=[];

    /** @var array Multiple filter @persistent */
    public array $multipleFilter=[];

    /** @var mixed Item detail key @persistent */
    public $itemDetailKey=null;

    /** @var RowActionItemDetail[] */
    protected array $itemDetails=[];

    /** @var RowActionSortable[] */
    protected array $rowsSortActions=[];

    /** @var string|null Sortable scope to connect with another sortable objects */
    protected ?string $sortableScope=null;

    /** @var string|null Draggable scope to connect with droppable objects */
    protected ?string $draggableScope='dragDropGrid';

    /** @var RowActionDraggable[] */
    protected array $rowsDragActions=[];

    /** @var string|null Droppable scope to connect with draggable objects */
    protected ?string $droppableScope=null;

    /** @var string|null Droppable effect */
    protected ?string $droppableEffect='table-info';

    /** @var null|callable function(NetteGrid $netteGrid, $movedId, $movedToId):void */
    protected $onDropCallback=null;

    /** @var HeaderActionExport[] */
    protected array $exportActions=[];

    /** @var RowActionItemModalDetail[] */
    protected array $itemDetailsModal=[];

    /** @var string|null Item detail modal id */
    protected ?string $itemDetailModalId=null;

    public function __construct()
    {
        $this->documentTemplate = new DocumentTemplate($this);
    }

    /**
     * Get document template (includes all document templates)
     * @return DocumentTemplate
     */
    public function getDocumentTemplate(): DocumentTemplate
    {
        return $this->documentTemplate;
    }

    /**
     * Set grid droppable
     * @param callable|null $onDropCallback function(NetteGrid $netteGrid, $movedId, $movedToId):void
     * @param string|null $droppableScope   Droppable scope to connect with another draggable objects
     * @param string|null $droppableEffect Droppable effect during dragging
     */
    public function setDroppable(?callable $onDropCallback=null, ?string $droppableScope='dragDropGrid', ?string $droppableEffect='table-info')
    {
        $this->onDropCallback = $onDropCallback;
        $this->droppableScope = $droppableScope;
        $this->droppableEffect = $droppableEffect;
        $this->getDocumentTemplate()->getDataRowTemplate()->addDataAttribute('droppable-row');
        $this->getDocumentTemplate()->getEmptyDataRowTemplate()->addDataAttribute('droppable-row');
    }

    public function setEmptyDataContent()
    {

    }

    /**
     * ADD COLUMN
     * ******************************************************************************
     *
     */

    /**
     * Add custom column
     * @param string $name
     * @param IColumn $column
     * @return mixed
     */
    public function addColumn(string $name, IColumn $column)
    {
        return $this->columns[$name] = $column;
    }

    /**
     * Add primary column
     * @param string $name
     * @param string|null $label
     * @return ColumnPrimary
     */
    public function addColumnPrimary(string $name='id', ?string $label='ID'): ColumnPrimary
    {
        return $this->columns[$name] = new ColumnPrimary($this, $name, $label);
    }

    /**
     * Add Column text
     * @param string $name
     * @param string|null $label
     * @return ColumnText
     */
    public function addColumnText(string $name, ?string $label=null): ColumnText
    {
        return $this->columns[$name] = new ColumnText($this, $name, $label);
    }

    /**
     * Add Column date
     * @param string $name
     * @param string|null $label
     * @return ColumnDate
     */
    public function addColumnDate(string $name, ?string $label=null): ColumnDate
    {
        return $this->columns[$name] = new ColumnDate($this, $name, $label);
    }

    /**
     * Add Column email
     * @param string $name
     * @param string|null $label
     * @return ColumnEmail
     */
    public function addColumnEmail(string $name, ?string $label=null): ColumnEmail
    {
        return $this->columns[$name] = new ColumnEmail($this, $name, $label);
    }

    /**
     * Add Column number
     * @param string $name
     * @param string|null $label
     * @return ColumnNumber
     */
    public function addColumnNumber(string $name, ?string $label=null): ColumnNumber
    {
        return $this->columns[$name] = new ColumnNumber($this, $name, $label);
    }

    /**
     * Add Column password
     * @param string $name
     * @param string|null $label
     * @return ColumnPassword
     */
    public function addColumnPassword(string $name, ?string $label=null): ColumnPassword
    {
        return $this->columns[$name] = new ColumnPassword($this, $name, $label);
    }

    /**
     * Add Column select
     * @param string $name
     * @param string|null $label
     * @return ColumnSelect
     */
    public function addColumnSelect(string $name, ?string $label=null): ColumnSelect
    {
        return $this->columns[$name] = new ColumnSelect($this, $name, $label);
    }

    /**
     * Add column textarea
     * @param string $name
     * @param string|null $label
     * @return ColumnTextarea
     */
    public function addColumnTextarea(string $name, ?string $label=null): ColumnTextarea
    {
        return $this->columns[$name] = new ColumnTextarea($this, $name, $label);
    }

    /**
     * Get column by name
     * @param string $columnName
     * @return IColumn
     * @throws NetteGridException
     */
    public function getColumn(string $columnName): IColumn
    {
        if(isset($this->columns[$columnName]))
            return $this->columns[$columnName];
        throw new NetteGridException(sprintf('Column %s does not exist.', $columnName));
    }

    /**
     * HEADER ACTIONS
     * ******************************************************************************
     *
     */

    /**
     * Add header action directly
     * @param HeaderAction $headerAction
     * @return HeaderAction
     * @internal
     */
    public function addHeaderActionDirectly(HeaderAction $headerAction): HeaderAction
    {
        return $this->headerActions[$headerAction->name] = $headerAction;
    }

    /**
     * Add header action
     * @param string $name
     * @param string|null $title
     * @return HeaderAction
     */
    public function addHeaderAction(string $name, ?string $title=null): HeaderAction
    {
        return $this->addHeaderActionDirectly(new HeaderAction($this, $name, $title));
    }

    /**
     * Add header action - update grid
     * @param string $name
     * @param string|null $title
     * @return HeaderAction|BaseElement
     */
    public function addHeaderDataUpdateAction(string $name='_updateGrid', ?string $title='Update'): HeaderAction
    {
        $headerAction = $this->addHeaderAction($name, $title);
        $headerAction->addIconElement('fas fa-sync-alt', [], true);
        $this->onAnchor[] = function() use ($headerAction){
            $headerAction->setLink($this->link('redrawData!'));
        };
        return $headerAction;
    }

    /**
     * Add header action - disable/enable edit
     * @param string $name
     * @param string|null $title
     * @return HeaderActionDisableEdit
     */
    public function addHeaderDisableEditAction(string $name='_disableEdit', ?string $title=null): HeaderActionDisableEdit
    {
        return $this->headerActions[$name] = new HeaderActionDisableEdit($this, $name, $title);
    }

    /**
     * Add header action - inline add
     * @param string $name
     * @param string|null $title
     * @return HeaderActionInlineAdd
     */
    public function addHeaderInlineAddAction(string $name='_inlineAdd', ?string $title='Add'): HeaderActionInlineAdd
    {
        return $this->headerActions[$name] = new HeaderActionInlineAdd($this, $name, $title);
    }

    /**
     * Add header action - export
     * @param string $name
     * @param string|null $title
     * @return HeaderActionExport
     */
    public function addHeaderExportAction(string $name='_export', ?string $title='Export'): HeaderActionExport
    {
        $exportAction = new HeaderActionExport($this, $name, $title);
        $this->headerActions[$name] = $exportAction;
        $this->exportActions[$name] = $exportAction;
        return $exportAction;
    }


    /**
     * ROW ACTIONS
     * ******************************************************************************
     *
     */

    /**
     * Add row action with as child of RowAction
     * @param IRowAction $rowAction
     * @return IRowAction
     * @internal
     */
    public function addRowActionDirectly(IRowAction $rowAction): IRowAction
    {
        $action = $this->rowActions[$rowAction->getName()] = $rowAction;
        $this->onAddRowAction($action->getName());
        return $action;
    }

    /**
     * Add row action
     * @param string $name
     * @param string|null $title
     * @return RowAction
     */
    public function addRowAction(string $name, ?string $title=null): RowAction
    {
        $title = $title ?? ucfirst($name);
        $action = $this->rowActions[$name] = new RowAction($this, $name, $title);
        $this->onAddRowAction($name);
        return $action;
    }

    /**
     * Add row action item detail
     * @param string $name
     * @param string|null $title
     * @return RowActionItemDetail
     */
    public function addRowActionItemDetail(string $name, ?string $title='Item detail'): RowActionItemDetail
    {
        $title = $title ?? ucfirst($name);
        $itemDetail = new RowActionItemDetail($this, $name, $title);
        $this->rowActions[$name] = $itemDetail;
        $this->itemDetails[$name] = $itemDetail;
        $this->onAddRowAction($name);
        return $itemDetail;
    }

    /**
     * Add row action modal
     * @param string $name
     * @param string|null $title
     * @return RowActionItemModalDetail
     */
    public function addRowActionItemModalDetail(string $name, ?string $title='Item detail'): RowActionItemModalDetail
    {
        $title = $title ?? ucfirst($name);
        $itemModalDetail = new RowActionItemModalDetail($this, $name, $title);
        $this->rowActions[$name] = $itemModalDetail;
        $this->itemDetailsModal[$name] = $itemModalDetail;
        $this->onAddRowAction($name);
        return $itemModalDetail;
    }

    /**
     * @param string $name
     * @param string|null $title
     * @param string|null $senderId Sender identification
     * @param string|null $scope Scope to connect with another sortable objects
     * @return RowActionSortable
     */
    public function addRowActionRowsSortable(string $name='__rowsSortable', ?string $title='Sort row', ?string $senderId=null, ?string $scope=null): RowActionSortable
    {
        $this->sortableScope = $scope;
        $sortableAction = new RowActionSortable($this, $name, $title);
        $this->rowsSortActions[$name] = $sortableAction;
        $this->rowActions[$name] = $sortableAction;
        $tbody = $this->getDocumentTemplate()->getTbodyTemplate();
        $tbody->addDataAttribute('sortable-rows', 'true');
        if(is_string($this->sortableScope))
            $tbody->addDataAttribute('sortable-scope', $this->sortableScope);
        if(is_string($senderId))
            $tbody->addDataAttribute('sortable-sender-id', $senderId);
        $this->getDocumentTemplate()->getDataRowTemplate()->addDataAttribute('sortable-row');
        $this->onAddRowAction($name);
        return $sortableAction;
    }

    /**
     * Add draggable action
     * @param string $name
     * @param string|null $title
     * @param string|null $scope Scope to connect with another droppable objects
     * @return RowActionDraggable
     */
    public function addRowActionDraggable(string $name='__rowsDraggable', ?string $title='Move', ?string $scope='dragDropGrid'): RowActionDraggable
    {
        $this->draggableScope = $scope;
        $draggableAction = new RowActionDraggable($this, $name, $title);
        $this->rowsDragActions[$name] = $draggableAction;
        $this->rowActions[$name] = $draggableAction;
        $this->onAddRowAction($name);
        $this->getDocumentTemplate()->getDataRowTemplate()
            ->addDataAttribute('draggable-row');
        return $draggableAction;
    }

    /**
     * Add action delete
     * @param string $name
     * @param string|null $title
     * @return RowActionDelete
     */
    public function addRowActionDelete(string $name='__removeRow', ?string $title='Remove'): RowActionDelete
    {
        $deleteAction = $this->rowActions[$name] = new RowActionDelete($this, $name, $title);
        $this->onAddRowAction($name);
        return $deleteAction;
    }

    /**
     * Actions order in the cell
     * @param string $name
     * @param int $position
     */
    public function reindexActions(string $name, int $position)
    {
        $currentKey = array_search($name, $this->rowActionsOrder, true);
        unset($this->rowActionsOrder[$currentKey]);
        $this->rowActionsOrder = array_values($this->rowActionsOrder);
        array_splice($this->rowActionsOrder, $position, 0, $name);
    }

    private function onAddRowAction(string $name): void
    {
        $this->rowActionsOrder[] = $name;
    }

    /**
     * GLOBAL ACTIONS
     * ******************************************************************************
     *
     */

    /**
     * Add global action
     * @param string $name
     * @param string|null $label
     * @return GlobalAction
     */
    public function addGlobalAction(string $name, ?string $label=null): GlobalAction
    {
        return $this->globalActions[$name] = new GlobalAction($this, $name, $label);
    }


    /**
     * MULTIPLE FILTER
     * ******************************************************************************
     *
     */

    /**
     * Add multiple filter
     * @param string $name
     * @return MultipleFilter
     */
    public function addMultipleFilter(string $name): MultipleFilter
    {
        return $this->multipleFilters[$name] = new MultipleFilter($this, $name);
    }


    /**
     * HANDLERS
     * ******************************************************************************
     *
     */

    /**
     * Redraw all grid
     * @throws AbortException
     */
    public function handleRedrawGrid(): void
    {
        $this->reloadDocument();
    }

    /**
     * Redraw Data
     * @throws AbortException
     */
    public function handleRedrawData(): void
    {
        $this->reloadItems();
    }

    /**
     * Signal - Edit
     * @param mixed $editKey
     * @throws AbortException
     */
    public function handleEdit($editKey): void
    {
        $this->editKey = $editKey;
        $this->editMode = true;
        $this->reloadItem();
    }

    /**
     * Edit column handler
     * @param mixed $id
     * @param string $column
     * @throws AbortException
     * @throws NetteGridException
     */
    public function handleEditColumn($id, string $column): void
    {
        $request = $this->getPresenter()->getRequest();
        $value = $request->getPost('value');
        $data = [
            $this->primaryColumn    => $id,
            $column                 => $value
        ];
        if(is_callable($this->onEditCallback))
        {
            $fn = $this->onEditCallback;
            $fn(ArrayHash::from($data), $id);
        }
        $getColumn = $this->getColumn($column);
        $rowData = $this->getRowFromSource($id);
        $cellValue = $value;
        $cellEditValue = $value;
        foreach($rowData as $rowDataKey => $row)
        {
            $cellValue = $getColumn->getCellValueForRendering($row);
            $cellEditValue = $getColumn->getEditCellValue($row);
            break;
        }
        $this->getPresenter()->payload->_netteGrid_editColumn_newValue = $cellValue;
        $this->getPresenter()->payload->_netteGrid_editColumn_editValue = $cellEditValue;
        $this->getPresenter()->sendPayload();
    }

    /**
     * Signal - Cancel editing
     * @throws AbortException
     */
    public function handleCancelEdit(): void
    {
        $this->editMode = false;
        $this->reloadItem();
    }

    /**
     * Signal - Inline add
     * @param bool $add
     * @throws AbortException
     */
    public function handleInlineAdd(bool $add=true): void
    {
        $this->inlineAdd = $add;
        $this->reloadItems();
    }

    /**
     * Signal - Reset filter
     * @throws AbortException
     */
    public function handleResetFilter(): void
    {
        $this->filter = [];
        $this->multipleFilter = [];
        $this->reloadDocument();
    }

    /**
     * Signal - RedrawRow
     * @param mixed $rowID
     * @throws AbortException
     */
    public function handleRedrawRow($rowID): void
    {
        $this->reloadRow($rowID);
    }

    /**
     * Signal - Paginate (change page)
     * @param int $page
     * @throws AbortException
     */
    public function handlePaginate(int $page): void
    {
        $this->page = $page;
        $this->reloadItems();
    }

    /**
     * Signal - sort by column
     * @param string $columnName
     * @param string $direction
     * @throws AbortException
     */
    public function handleSortColumn(string $columnName, string $direction='ASC')
    {
        $this->sortDirection = $direction;
        $this->sortByColumn = $columnName;
        $this->columns[$columnName]->setSortDirection($direction);
        $this->reloadHeaderTitles();
        $this->reloadItems();
    }

    /**
     * Select global action
     * @param string $action
     * @throws AbortException
     */
    public function handleSelectGlobalAction(string $action): void
    {
        $this->template->selectedGlobalAction = $action;
        $this->template->globalActionContainerName = 'global_' . $action;
        $this->template->globalActionContainer = $this->globalActions[$action]->getFormContainer();
        $this->reloadGlobalActionContainer();
    }

    /**
     * Signal to call row action onClickCallback(NetteGrid $this, $row, $primary): void
     * Redraw any snippet of grid should be called by callback
     * @param string $action
     * @param mixed $primary
     */
    public function handleRowAction(string $action, $primary): void
    {
        $action = $this->rowActions[$action];
        if($action->onlyAjaxRequest === true)
            if($this->getPresenter()->isAjax() === false)
                return;
        $onClick = $action->getOnClickCallback();
        if(is_callable($onClick))
        {
            $row = $this->getDataFromSource($primary);
            foreach($row as $rowKey => $rowData)
            {
                $onClick($this, $rowData, $primary);
                break;
            }
        }
        $this->reloadDocumentArea();
    }

    /**
     * Signal - show item detail
     * @param string $itemDetailId
     * @param mixed $primary
     * @throws AbortException
     */
    public function handleItemDetail(string $itemDetailId, $primary): void
    {
        $this->itemDetailKey = $primary;
        $this->template->itemDetailKey = $primary;
        $this->template->itemDetailAction = $itemDetailId;
        $this->reloadItemDetail($itemDetailId, $primary);
    }

    /**
     * Signal - fill modal with row detail
     * @param string $itemDetailId
     * @param mixed $primary
     * @throws AbortException
     */
    public function handleItemDetailModal(string $itemDetailId, $primary): void
    {
        $itemDetail = $this->itemDetailsModal[$itemDetailId];
        $rowData = $this->getRowFromSource($primary);
        foreach($rowData as $rowKey => $row)
        {
            $itemDetail->callHeaderTitleCallback($row, $primary);
            $itemDetail->callContentCallback($row, $primary);
            break;
        }
        $this->reload(self::SNIPPET_ITEM_DETAIL_MODAL);
        $this['itemDetailModal']->reloadHeader();
    }

    /**
     * Signal - rows sort
     */
    public function handleRowsSort(): void
    {
        if($this->getPresenter()->isAjax())
        {
            $request = $this->getPresenter()->getRequest();
            $sortAction = $request->getPost('actionKey');
            $movedKey = $request->getPost('movedKey');
            $beforeKey = $request->getPost('beforeKey');
            $afterKey = $request->getPost('afterKey');
            $senderId = $request->getPost('senderId');
            if(is_string($sortAction))
            {
                $action = $this->rowsSortActions[$sortAction];
                $onSortCallback = $action->getOnSortCallback();
                if(is_callable($onSortCallback))
                    $onSortCallback($this, $movedKey, $beforeKey, $afterKey, $senderId);
            }
            $this->reloadDocumentArea();
        }
    }

    /**
     * Signal - drop row
     */
    public function handleRowDrop(): void
    {
        if($this->getPresenter()->isAjax())
        {
            $request = $this->getPresenter()->getRequest();
            $movedId = $request->getPost('movedId');
            $movedToId = $request->getPost('movedToId');
            $dropFn = $this->onDropCallback;
            if(is_callable($dropFn))
                $dropFn($this, $movedId, $movedToId);
            $this->reloadDocumentArea();
        }
    }

    /**
     * Signal - export
     * @param string $exportKey
     * @throws AbortException
     */
    public function handleExport(string $exportKey): void
    {
        $this->csvExport($this->exportActions[$exportKey]);
    }


    /**
     * Load state
     * @param array $params
     * @throws BadRequestException
     */
    public function loadState(array $params): void
    {
        parent::loadState($params);

        if($this->isFilterable === true)
        {
            $this->filterContainer = $this['form']->addContainer('filter');
            $this['form']['filterSubmit']->setValidationScope([$this['form']['filter']]);
        }

        if($this->isEditable === true)
        {
            $this->editContainer = $this['form']->addContainer('edit');
            $this['form']['editSubmit']->setValidationScope([$this['form']['edit']]);
            $this->editContainer->addHidden($this->primaryColumn);
            $this->addRowActionDirectly($this->documentTemplate->getRowActionEdit());
            $this->reindexActions('edit', 0);
        }

        if($this->isAddable === true)
        {
            $this->addContainer = $this['form']->addContainer('add');
            $this['form']['addSubmit']->setValidationScope([$this['form']['add']]);
        }

        foreach($this->columns as $columnName => $column)
        {
            if($this->isFilterable === true)
                $column->addFilterFormInput();
            if($this->isEditable === true)
                $column->addEditFormInput();
            if($this->isAddable === true)
                $column->addAddFormInput();
        }

        if($this->paginator instanceof Paginator)
        {
            $this->paginateContainer = $this['form']->addContainer('paginate');
            $this['form']['paginateSubmit']->setValidationScope([$this['form']['paginate']]);
            $itemsPerPageSelection = [];
            foreach($this->itemsPerPageSelection as $itemsPerPage)
                $itemsPerPageSelection[$itemsPerPage] = $itemsPerPage;
            if(is_string($this->showAllOption))
                array_push($itemsPerPageSelection, $this->showAllOption);
            $this->paginateContainer->addSelect('itemsPerPage', null, $itemsPerPageSelection)
                ->setHtmlAttribute('data-paginate-submit')
                ->setHtmlAttribute('data-container', 'paginateSubmit')
                ->setHtmlAttribute('class', 'form-control form-control-sm');
        }

        if(count($this->globalActions) > 0)
        {
            $this->globalActionsContainer = $this['form']->addContainer('globalActions');
            $this->globalActionsContainer->addCheckboxList('rowCheck', '', [])
                ->setHtmlAttribute('data-row-selector');

            $this->documentTemplate->getTbodyTemplate()->makeRowsSelectable($this->rowsSelectable);
            $this->documentTemplate->getDataRowTemplate()->rowsSelectable($this->rowsSelectable);
        }

        if($this->hasItemModalDetail() === true)
        {
            $this->itemDetailModalId = 'itemDetail-' . $this->getUniqueId();
            $this['itemDetailModal']->setModalId($this->itemDetailModalId);
        }
    }


    /**
     * Default renderer
     */
    public function render(): void
    {
        if($this->isFilterable === true)
            $this['form']['filter']->setDefaults($this->filter);

        $this->template->uniqueID = $this->getUniqueId();
        $this->template->isFilterable = $this->isFilterable;
        $this->template->isEditable = $this->isEditable();
        $this->template->isAddable = $this->isAddable();
        $this->template->inlineAdd = $this->inlineAdd;
        $this->template->editMode = $this->editMode;
        $this->template->hasActionsColumn = $this->hasActionColumn();
        $this->template->rowActionsOrder = $this->rowActionsOrder;
        $this->template->rowActions = $this->rowActions;
        $this->template->hiddenHeader = $this->documentTemplate->hiddenHeader;
        $this->template->headerActions = $this->headerActions;
        $this->template->paginator = $this->paginator;
        $this->template->sortByColumn = $this->sortByColumn;
        $this->template->sortDirection = $this->sortDirection;
        $this->template->hasGlobalAction = $this->hasGlobalAction();
        $this->template->globalActions = $this->globalActions;
        $this->template->tableColspan = $this->getTableColspan();
        $this->template->hasMultipleFilter = $this->hasMultipleFilter();
        $this->template->multipleFilters = $this->multipleFilters;
        $this->template->multipleFilterContainer = $this->multipleFilterContainer;
        $this->template->showResetFilterButton = $this->showResetFilterButton();
        $this->template->itemDetailKey = $this->itemDetailKey;
        $this->template->itemDetails = $this->itemDetails;
        $this->template->hasItemDetail = $this->hasItemDetail();
        $this->template->sortableScope = $this->sortableScope;
        $this->template->draggableScope = $this->draggableScope;
        $this->template->droppableScope = $this->droppableScope;
        $this->template->droppableEffect = $this->droppableEffect;
        $this->template->hasItemModalDetail = $this->hasItemModalDetail();
        $this->template->itemDetailsModal = $this->itemDetailsModal;

        //templates
        $this->template->documentTemplate = $this->documentTemplate;
        $this->template->wholeDocumentTemplate = $this->documentTemplate->getWholeDocumentTemplate();
        $this->template->tableTemplate = $this->documentTemplate->getTableTemplate();
        $this->template->theadTemplate = $this->documentTemplate->getTheadTemplate();
        $this->template->theadTitlesRowTemplate = $this->documentTemplate->getTheadTitlesRowTemplate();
        $this->template->tbodyTemplate = $this->documentTemplate->getTbodyTemplate();
        $this->template->emptyDataRowTemplate = $this->documentTemplate->getEmptyDataRowTemplate();
        $this->template->emptyDataColTemplate = $this->documentTemplate->getEmptyDataColTemplate();
        $this->template->headFilterRowTemplate = $this->documentTemplate->getHeadFilterRowTemplate();
        $this->template->headerActionsColumnTemplate = $this->documentTemplate->getHeaderActionsColTemplate();
        $this->template->dataActionsColumnTemplate = $this->documentTemplate->getDataActionsColTemplate();
        $this->template->rowActionSave = $this->documentTemplate->getRowActionSave();
        $this->template->rowActionCancel = $this->documentTemplate->getRowActionCancel();
        $this->template->rowActionEdit = $this->documentTemplate->getRowActionEdit();
        $this->template->tfootTemplate = $this->documentTemplate->getTfootTemplate();
        $this->template->tfootContentTemplate = $this->documentTemplate->getTfootContentTemplate();

        $data = $this->data ?? $this->getDataFromSource();
        $this->template->columns = $this->getColumns(true);
        $this->template->countOfColumns = $this->getCountOfPrintableColumns();
        $this->template->primaryColumn = $this->primaryColumn;
        $this->template->editRowKey = $this->editKey;
        $this->template->data = $data;
        $this->template->filter = $this->filter;
        $this->template->showEmptyResult = !((bool)$data);
        $this->template->templates = $this->templates;

        $this->template->setFile(__DIR__ . '/templates/default.latte');
        $this->template->render();
    }

    /**
     * The main form
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = new BootstrapForm();
        if($this->autocomplete === false)
            $form->setHtmlAttribute('autocomplete', 'off');
        $form->setHtmlAttribute('data-reset', 'false');
        $form->addSubmit('filterSubmit')
            ->setHtmlAttribute('class', 'd-none')
            ->onClick[] = [$this, 'filterFormSuccess'];
        $form->addSubmit('editSubmit')
            ->onClick[] = [$this, 'editFormSuccess'];
        $form->addSubmit('addSubmit', 'Add')
            ->onClick[] = [$this, 'addFormSuccess'];
        $form->addSubmit('paginateSubmit')
            ->setHtmlAttribute('class', 'd-none')
            ->onClick[] = [$this, 'paginateFormSuccess'];
        return $form;
    }

    /**
     * Add from success
     * @param Button $button
     * @throws AbortException
     * @internal
     */
    public function addFormSuccess(Button $button): void
    {
        $form = $button->getForm();
        $values = $form->values;
        if(is_callable($this->onAddCallback))
        {
            $fn = $this->onAddCallback;
            $fn($values->add);
        }
        $this->inlineAdd = false;
        $this->reloadItems();
    }

    /**
     * Edit form success
     * @param Button $button
     * @throws AbortException
     * @internal
     */
    public function editFormSuccess(Button $button): void
    {
        $form = $button->getForm();
        $values = $form->values;
        $editValues = $values->edit;
        $primaryColumn = $this->primaryColumn;
        $primaryValue = $editValues->$primaryColumn;
        if(is_callable($this->onEditCallback))
        {
            $fn = $this->onEditCallback;
            $fn($editValues, $primaryValue);
        }
        $this->editMode = false;
        $this->editKey = $primaryValue;
        $this->reloadItem();
    }

    /**
     * Filter form success
     * @param Button $button
     * @throws AbortException
     * @internal
     */
    public function filterFormSuccess(Button $button): void
    {
        $form = $button->getForm();
        $values = $form->values;
        $filterValues = (array)$values['filter'];
        foreach($filterValues as $key => $value)
            if(empty($value))
                unset($filterValues[$key]);
        $this->filter = $filterValues;
        $this->editKey = null;
        $this->editMode = false;
        $this->reloadItems();
        $this->reloadFooter();
        $this->reloadHeadActions();
    }

    /**
     * Paginate form success
     * @param Button $button
     * @throws AbortException
     * @internal
     */
    public function paginateFormSuccess(Button $button): void
    {
        $form = $button->getForm();
        $values = $form->values->paginate;
        $this->itemsPerPage = $values->itemsPerPage;
        $this->reloadItems();
        $this->reloadFooter();
    }

    /**
     * Multiple filter form success
     * @param Button $button
     * @throws AbortException
     * @internal
     */
    public function multipleFilterFormSuccess(Button $button): void
    {
        $form = $button->getForm();
        $multipleValues = $form->values->multipleFilter;
        $multiple = [];
        foreach($this->multipleFilters as $multipleFilterName => $multipleFilter)
        {
            foreach($multipleFilter->getColumns() as $columnName => $column)
            {
                foreach($multipleValues->$multipleFilterName as $inputKey => $inputValue)
                {
                    if(empty($inputValue))
                        continue;
                    if(isset($multiple[$columnName]))
                    {
                        if(is_string($multiple[$columnName]))
                        {
                            $multiple[$columnName] = [$multiple[$columnName], $inputValue];
                        }else{
                            array_push($multiple[$columnName], $inputValue);
                        }
                    }else{
                        $multiple[$columnName] = $inputValue;
                    }
                }
            }
        }
        $this->multipleFilter = $multiple;
        $this->editKey = null;
        $this->editMode = false;
        $this->reloadItems();
        $this->reloadFooter();
        $this->reloadHeadActions();
    }

    /**
     * Paginator component
     * @return Pagination
     */
    protected function createComponentPagination(): Pagination
    {
        $pagination = new Pagination();
        $pagination->setOnPaginateCallback(function(Paginator $paginator){
            $this->page = $paginator->page;
            $this->reloadItems();
            $this->reloadFooter();
        });
        $pagination->setWidth(Pagination::SMALL);
        $pagination->setAlign(Pagination::ALIGN_CENTER);
        return $pagination;
    }

    protected function createComponentItemDetailModal(): Modal
    {
        $modal = new Modal();

        return $modal;
    }

    /**
     * Get filter container
     * @return Container|null
     * @internal
     */
    public function getFilterContainer(): ?Container
    {
        return $this->filterContainer;
    }

    /**
     * Get edit container
     * @return Container|null
     */
    public function getEditContainer(): ?Container
    {
        return $this->editContainer;
    }

    /**
     * Get add container
     * @return Container|null
     */
    public function getAddContainer(): ?Container
    {
        return $this->addContainer;
    }

    /**
     * Add template
     * @param string $templatePath
     * @return NetteGrid
     */
    public function addTemplate(string $templatePath): self
    {
        $this->templates[] = $templatePath;
        return $this;
    }

    /**
     * Set data source
     * @param callable|null $dataSourceCallback
     * @return NetteGrid
     */
    public function setDataSourceCallback(?callable $dataSourceCallback): self
    {
        $this->dataSourceCallback = $dataSourceCallback;
        return $this;
    }

    /**
     * Set primary column
     * @param string $columnName
     * @return NetteGrid
     */
    public function setPrimaryColumn(string $columnName): self
    {
        $this->primaryColumn = $columnName;
        return $this;
    }

    /**
     * Set pagination
     * @param callable $totalItemsCountCallback function(array $filter, array $multipleFilter)
     * @param int $itemsPerPage default items per page
     * @param array|null $itemsPerPageSelection items per page selection - if null - selection will not be shown
     * @param string|null $showAllOption Show all option - if null - option will not be shown
     */
    public function setPagination(callable $totalItemsCountCallback, int $itemsPerPage=50, ?array $itemsPerPageSelection=null, ?string $showAllOption='All')
    {
        $this->totalItemsCountCallback = $totalItemsCountCallback;
        $this->itemsPerPage = $itemsPerPage;
        $this->itemsPerPageSelection = $itemsPerPageSelection;
        $this->showAllOption = $showAllOption;
        $this->paginator = new Paginator();
        $this->paginator->setItemsPerPage($itemsPerPage);
        $this->paginator->page = $this['pagination']->getPaginator() ? $this['pagination']->getPaginator()->page : $this->page;
        $this['pagination']->setPaginator($this->paginator);
    }

    /**
     * Is column exists?
     * @param string $columnName
     * @param bool $throw
     * @return bool
     * @throws ColumnNotFoundException
     * @internal
     */
    protected function columnExists(string $columnName, bool $throw=true): bool
    {
        $exists = array_key_exists($columnName, $this->columns);
        if($exists === false && $throw === true)
            throw new ColumnNotFoundException(sprintf("Column %s does not exist.", $columnName));
        return $exists;
    }

    /**
     * Get count of printable (non-hidden) columns
     * @return int
     * @internal
     */
    public function getCountOfPrintableColumns(): int
    {
        $count = 0;
        foreach($this->columns as $columnName => $column)
            if($column->isHidden() === false)
                $count++;
        return $count;
    }

    /**
     * Get columns
     * @param bool $onlyVisible
     * @return IColumn[]
     * @internal
     */
    public function getColumns($onlyVisible=false): array
    {
        if($onlyVisible === true)
        {
            $visibleColumns = [];
            foreach($this->columns as $columnName => $column)
                if($column->isHidden() === false)
                    $visibleColumns[$columnName] = $column;
            return $visibleColumns;
        }
        return $this->columns;
    }

    /**
     * Get data from source
     * @param mixed|null $rowID
     * @param bool $usePaginator
     * @param bool $useFilter
     * @return mixed
     * @internal
     */
    protected function getDataFromSource($rowID=null, bool $usePaginator=true, bool $useFilter=true)
    {
        //data-source was not set
        if(is_null($this->dataSourceCallback))
            return null;

        //print more then only one row
        if(is_null($rowID) === true)
        {
            if(is_null($this->editKey) === false && $this->presenter->isAjax())
                $this->filter[$this->primaryColumn] = $this->editKey;

            if($this->paginator instanceof Paginator && $usePaginator === true)
            {
                $itemsTotalCountFn = $this->totalItemsCountCallback;
                $this->paginator->setItemCount($itemsTotalCountFn($this->filter, null));
                $this->paginator->page = $this->page;
                $this->paginator->itemsPerPage = $this->itemsPerPage;
            }
        }
        //print only one row
        else{
            $filter[$this->primaryColumn] = $rowID;
        }

        $getDataFn = $this->dataSourceCallback;
        $data = $getDataFn(
            is_null($rowID) ? ($useFilter === true ? $this->filter : []) : $filter,
            is_null($rowID) ? ($useFilter === true ? $this->multipleFilter : []) : [],
            is_string($this->sortByColumn) ? [$this->sortByColumn, $this->sortDirection ?? Column::SORT_ASC] : null,
            is_null($rowID) ? ($usePaginator === true ? $this->paginator : null) : null
        );

        if(is_countable($data) === false || count($data) == 0)
            return null;
        return $data;
    }

    /**
     * Get single data row
     * @param $rowID
     * @return mixed
     */
    protected function getRowFromSource($rowID)
    {
        return $this->getDataFromSource($rowID);
    }

    /**
     * Get primary value from row
     * @param mixed $row
     * @return mixed
     * @internal
     */
    public function getRowPrimaryValue($row)
    {
        $primaryColumn = $this->primaryColumn;
        return $row->$primaryColumn;
    }

    /**
     * Set Grid editable (in line)
     * @param bool $editable
     * @internal
     */
    public function setEditable(bool $editable=true): void
    {
        $this->isEditable = $editable;
    }

    /**
     * Set Grid editable (in column)
     * @param bool $editableInColumn
     * @internal
     */
    public function setEditableInColumn(bool $editableInColumn=true): void
    {
        $this->isEditableInColumn = $editableInColumn;
    }

    /**
     * Is grid editable
     * @return bool
     * @internal
     */
    public function isEditable(): bool
    {
        return ($this->isEditable && ($this->editEnabled === true || $this->editEnabled === null));
    }

    /**
     * Set grid add able
     * @param bool $isAddable
     * @internal
     */
    public function setAddable(bool $isAddable): void
    {
        $this->isAddable = $isAddable;
    }

    /**
     * Is addable?
     * @return bool
     * @internal
     */
    public function isAddable(): bool
    {
        return $this->isAddable;
    }

    /**
     * Set Grid filterable
     * @param bool $filterable
     * @internal
     */
    public function setFilterable(bool $filterable=true): void
    {
        $this->isFilterable = $filterable;
    }

    /**
     * Set on edit callback
     * @param callable|null $onEditCallback
     * @return NetteGrid
     */
    public function setOnEditCallback(?callable $onEditCallback): self
    {
        $this->onEditCallback = $onEditCallback;
        return $this;
    }

    /**
     * Set on add callback
     * @param callable|null $onAddCallback
     * @return NetteGrid
     */
    public function setOnAddCallback(?callable $onAddCallback): self
    {
        $this->onAddCallback = $onAddCallback;
        return $this;
    }

    /**
     * Reload
     * @param null|string|string[] $snippet
     * @throws AbortException
     */
    public function reload($snippet=null): void
    {
        if($this->presenter->isAjax())
        {
            $this->redrawControl(self::SNIPPET_DOCUMENT_AREA);
            if(is_null($snippet))
            {
                $this->redrawControl(self::SNIPPET_ALL_CONTENT);
            }else if (is_string($snippet)){
                $this->redrawControl($snippet);
            }else if (is_array($snippet)){
                foreach($snippet as $snip)
                    $this->redrawControl($snip);
            }
        }else{
            $this->presenter->redirect('this');
        }
    }

    /**
     * Reload only document area
     */
    public function reloadDocumentArea(): void
    {
        $this->redrawControl(self::SNIPPET_DOCUMENT_AREA);
    }

    /**
     * Reload item detail
     * @param string $itemDetailId
     * @param mixed $primary
     * @throws AbortException
     */
    public function reloadItemDetail(string $itemDetailId, $primary): void
    {
        $this->reload(sprintf('%s-%s-%s', self::SNIPPET_PATH_ITEM_DETAIL, $itemDetailId, $primary));
    }

    /**
     * Reload global action container
     * @throws AbortException
     */
    public function reloadGlobalActionContainer(): void
    {
        $this->reload(self::SNIPPET_GLOBAL_ACTION_CONTAINER);
    }

    /**
     * Reload header
     * @throws AbortException
     */
    public function reloadHeader(): void
    {
        $this->reload(self::SNIPPET_HEADER);
    }

    /**
     * Reload header titles
     * @throws AbortException
     */
    public function reloadHeaderTitles(): void
    {
        $this->reload(self::SNIPPET_HEAD_TITLES);
    }

    /**
     * Reload footer content
     * @throws AbortException
     */
    public function reloadFooter(): void
    {
        $this->reload(self::SNIPPET_TFOOT);
    }

    /**
     * Reload all document
     * @throws AbortException
     */
    public function reloadDocument(): void
    {
        $this->editKey = null;
        $this->reload(self::SNIPPET_ALL_CONTENT);
    }

    /**
     * Reload all data (tbdoy)
     * @throws AbortException
     */
    public function reloadItems(): void
    {
        $this->editKey = null;
        $this->reload(self::SNIPPET_TBODY);
    }

    /**
     * Reload one row (only one row must be provided from datasource)
     * @throws AbortException
     */
    public function reloadItem(): void
    {
        $this->reload(self::SNIPPET_ITEMS_AREA);
    }

    /**
     * Reload one row by primary key (data will be loaded)
     * @param $rowID
     * @throws AbortException
     */
    public function reloadRow($rowID): void
    {
        $this->data = $this->getDataFromSource($rowID);
        $this->reloadItem();
    }

    /**
     * Reload hader actions
     * @throws AbortException
     */
    public function reloadHeadActions(): void
    {
        $this->reload(self::SNIPPET_HEAD_ACTIONS);
    }

    /**
     * Has grid action column?
     * @return bool
     * @internal
     */
    public function hasActionColumn(): bool
    {
        return $this->isFilterable || count($this->rowActions) > 0 || count($this->headerActions) > 0 || $this->inlineAdd;
    }

    /**
     * Set autocomplete on all form
     * @param bool $autocomplete
     * @return NetteGrid
     */
    public function setAutocomplete(bool $autocomplete=true): self
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    /**
     * Set autocomplete for inline add container
     * @param bool $addAutocomplete
     * @return NetteGrid
     */
    public function setAddAutocomplete(bool $addAutocomplete=true): self
    {
        $this->addAutocomplete = $addAutocomplete;
        return $this;
    }

    /**
     * Set autocomplete for edit container
     * @param bool $editAutocomplete
     * @return NetteGrid
     */
    public function setEditAutocomplete(bool $editAutocomplete): self
    {
        $this->editAutocomplete = $editAutocomplete;
        return $this;
    }

    /**
     * Set autocomplete for filter container
     * @param bool $filterAutocomplete
     * @return NetteGrid
     */
    public function setFilterAutocomplete(bool $filterAutocomplete): self
    {
        $this->filterAutocomplete = $filterAutocomplete;
        return $this;
    }

    /**
     * @return bool
     * @internal
     */
    public function isAutocomplete(): bool
    {
        return $this->autocomplete;
    }

    /**
     * @return bool
     * @internal
     */
    public function isFilterAutocomplete(): bool
    {
        return $this->filterAutocomplete;
    }

    /**
     * @return bool
     * @internal
     */
    public function isEditAutocomplete(): bool
    {
        return $this->editAutocomplete;
    }

    /**
     * @return bool
     * @internal
     */
    public function isAddAutocomplete(): bool
    {
        return $this->addAutocomplete;
    }

    /**
     * Set jQuery selectable - it is enabled by default, you can disable it
     * @param bool $rowsSelectable
     * @return NetteGrid
     */
    public function setRowsSelectable(bool $rowsSelectable=true): self
    {
        $this->rowsSelectable = $rowsSelectable;
        return $this;
    }

    /**
     * Get table full colspan
     * @return int
     * @internal
     */
    public function getTableColspan(): int
    {
        return
            $this->getCountOfPrintableColumns() + (int)$this->hasActionColumn() + (int)$this->hasGlobalAction();
    }

    /**
     * Has global action?
     * @return bool
     * @internal
     */
    public function hasGlobalAction(): bool
    {
        return (bool)count($this->globalActions);
    }

    /**
     * Has multiple filter?
     * @return bool
     * @internal
     */
    public function hasMultipleFilter(): bool
    {
        return (bool)count($this->multipleFilters);
    }

    /**
     * Has item detail?
     * @return bool
     * @internal
     */
    public function hasItemDetail(): bool
    {
        return (bool)count($this->itemDetails);
    }

    /**
     * Has item modal detail?
     * @return bool
     * @internal
     */
    public function hasItemModalDetail(): bool
    {
        return (bool)count($this->itemDetailsModal);
    }

    /**
     * Get multiple filter container
     * @return Container
     * @internal
     */
    public function getMultipleFilterContainer(): Container
    {
        if(isset($this->multipleFilterContainer) == false)
        {
            $this->multipleFilterContainer = $this['form']->addContainer('multipleFilter');
            $this['form']->addSubmit('multipleFilterSubmit')
                ->setHtmlAttribute('class', 'd-none')
                ->setValidationScope([$this['form']['multipleFilter']])
                ->onClick[] = [$this, 'multipleFilterFormSuccess'];
        }
        return $this->multipleFilterContainer;
    }

    /**
     * Show reset filter button
     * @return bool
     * @internal
     */
    public function showResetFilterButton(): bool
    {
        return count($this->filter) > 0 || count($this->multipleFilter) > 0;
    }

    /**
     * Csv export
     * @param HeaderActionExport $actionExport
     * @throws AbortException
     */
    protected function csvExport(HeaderActionExport $actionExport)
    {
        $dataToExport = [];
        $includeHiddenColumns = $actionExport->isExportHiddenColumns();
        $columnsToExport = is_array($actionExport->getColumnsToExport()) ? $actionExport->getColumnsToExport() : $this->columns;
        //Header
        if($actionExport->isExportWithHeader()) {
            foreach ($columnsToExport as $columnName => $column) {
                if ($includeHiddenColumns === false)
                    if ($column->isHidden() === true)
                        continue;
                $dataToExport[0][] = $column->getLabel();
            }
        }
        //Data
        $dataFromSource = $this->getDataFromSource(null, false, $actionExport->isRespectFilter());
        if(is_countable($dataFromSource))
        {
            foreach($dataFromSource as $dataKey => $data)
            {
                $row = [];
                foreach($columnsToExport as $columnName => $column)
                {
                    if($includeHiddenColumns === false)
                        if($column->isHidden() === true)
                            continue;
                    $row[] = $column->getExportCellValue($data);
                }
                $dataToExport[] = $row;
            }
        }
        $exportResponse = new CSVResponse($dataToExport, $actionExport->getExportFileName(), $actionExport->getEncoding(), $actionExport->getDelimiter(), true);
        $this->getPresenter()->sendResponse($exportResponse);
    }

    /**
     * Get item detail modal id
     * @return string|null
     */
    public function getItemDetailModalId(): ?string
    {
        return $this->itemDetailModalId;
    }

}