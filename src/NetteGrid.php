<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use Contributte\FormsBootstrap\BootstrapForm;
use e2221\BootstrapComponents\Pagination\Pagination;
use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\HeaderActions\HeaderActionDisableEdit;
use e2221\NetteGrid\Actions\HeaderActions\HeaderActionInlineAdd;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Column\ColumnPrimary;
use e2221\NetteGrid\Column\ColumnText;
use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Exceptions\ColumnNotFoundException;
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
        SNIPPET_TFOOT = 'footer';

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

    /** @var bool Is there at least one editable column? */
    protected bool $isEditable=false;

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

    public function setEmptyDataContent()
    {

    }

    /**
     * ADD COLUMN
     * ******************************************************************************
     *
     */

    /**
     * Add column
     * @param string $name
     * @param IColumn $column
     * @return IColumn
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
     * ROW ACTIONS
     * ******************************************************************************
     *
     */

    /**
     * Add row action with as child of RowAction
     * @param RowAction $rowAction
     * @return RowAction
     * @internal
     */
    public function addRowActionDirectly(RowAction $rowAction): RowAction
    {
        $action = $this->rowActions[$rowAction->name] = $rowAction;
        $this->onAddRowAction($action->name);
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
        $this->getPresenter()->payload->_netteGrid_editColumn_newValue = $value;
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
        $this->reloadDocument();
    }

    /**
     * Signal - RedrawRow
     * @param mixed $rowID
     * @throws AbortException
     */
    public function handleRedrawRow($rowID): void
    {
        $this->data = $this->getDataFromSource($rowID);
        $this->reloadItem();
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
            $itemsPerPageSelection = $this->itemsPerPageSelection;
            if(is_string($this->showAllOption))
                array_push($itemsPerPageSelection, $this->showAllOption);
            $this->paginateContainer->addSelect('itemsPerPage', null, $itemsPerPageSelection)
                ->setHtmlAttribute('data-paginate-submit')
                ->setHtmlAttribute('data-container', 'paginate');
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
        $this->reloadDocument();
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
     * @return mixed[]|null
     */
    protected function getDataFromSource($rowID=null)
    {
        if(is_null($this->dataSourceCallback))
            return null;

        if($rowID)
        {
            $this->filter[$this->primaryColumn] = $rowID;
        }else if(is_null($this->editKey) === false && $this->presenter->isAjax())
        {
            $this->filter[$this->primaryColumn] = $this->editKey;
        }

        if($this->paginator instanceof Paginator)
        {
            $itemsTotalCountFn = $this->totalItemsCountCallback;
            $this->paginator->setItemCount($itemsTotalCountFn($this->filter, null));
            $this->paginator->page = $this->page;
        }

        $getDataFn = $this->dataSourceCallback;
        $data = $getDataFn($this->filter, null, null, $this->paginator);
        if(is_countable($data) === false || count($data) == 0)
            return null;
        return $data;
    }

    /**
     * Get primary value from row
     * @param mixed $row
     * @return mixed
     */
    public function getRowPrimaryValue($row)
    {
        $primaryColumn = $this->primaryColumn;
        return $row->$primaryColumn;
    }

    /**
     * Set Grid editable
     * @param bool $editable
     * @internal
     */
    public function setEditable(bool $editable=true): void
    {
        $this->isEditable = $editable;
    }

    /**
     * Is grid editable
     * @return bool
     */
    public function isEditable(): bool
    {
        return ($this->isEditable && $this->editEnabled);
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
                $this->redrawControl(self::SNIPPET_DOCUMENT_AREA);
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
     * Reload footer content
     * @throws AbortException
     */
    public function reloadFooter(): void
    {
        $this->reload([self::SNIPPET_TFOOT]);
    }

    /**
     * Reload all document
     * @throws AbortException
     */
    public function reloadDocument(): void
    {
        $this->reload(self::SNIPPET_ALL_CONTENT);
    }

    /**
     * Reload all data (tbdoy)
     * @throws AbortException
     */
    public function reloadItems(): void
    {
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
     * Has grid action column?
     * @return bool
     * @internal
     */
    public function hasActionColumn(): bool
    {
        return $this->isFilterable || count($this->rowActions) > 0 || count($this->headerActions) > 0 || $this->inlineAdd;
    }
}