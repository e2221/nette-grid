<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use Contributte\FormsBootstrap\BootstrapForm;
use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Column\ColumnPrimary;
use e2221\NetteGrid\Column\ColumnText;
use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Exceptions\ColumnNotFoundException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\Button;
use Nette\Utils\ArrayHash;

class NetteGrid extends Control
{
    const MAIN_CONTENT_SNIPPET = 'gridContent';

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

    /** @var null|int|string @persistent Edit key */
    public $editKey=null;

    /** @var string|null @persistent Edit only column */
    public ?string $editColumn=null;

    /** @var mixed|null */
    protected $data=null;

    /** @var null|callable Function that will be called after submit edit function(ArrayHash $values, $primary) */
    protected $onEditCallback=null;

    /** @var bool @persistent Active edit mode [true = edit is enable] */
    public bool $editMode=false;

    /** @var bool Is there at least one filterable column? */
    protected bool $isFilterable=false;

    protected bool $isEditable=false;

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
     * ADD ROW ACTIONS
     * ******************************************************************************
     *
     */

    /**
     * Add row action with as child of RowAction
     * @param RowAction $rowAction
     * @return RowAction
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

    public function resortActions(string $name, int $position)
    {
        // todo
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
        if($this->presenter->isAjax())
        {
            $this->redrawControl('documentArea');
            $this->redrawControl(self::MAIN_CONTENT_SNIPPET);
        }else{
            $this->redirect('this');
        }
    }

    /**
     * Redraw Data
     * @throws AbortException
     */
    public function handleRedrawData(): void
    {
        if($this->presenter->isAjax()){
            $this->redrawControl('documentArea');
            $this->redrawControl('data');
        }else{
            $this->redirect('this');
        }
    }

    /**
     * Signal - Edit
     */
    public function handleEdit(): void
    {
        if($this->presenter->isAjax())
        {
            $this->redrawControl('documentArea');
            $this->redrawControl('data');
        }
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
            $fn($data, $id);
        }
        $this->getPresenter()->payload->_netteGrid_editColumn_newValue = $value;
        $this->getPresenter()->sendPayload();
    }

    /**
     * Signal - Cancel editing
     */
    public function handleCancelEdit(): void
    {
        $this->editMode = false;
        $this->editColumn = null;
        if($this->presenter->isAjax())
        {
            $this->redrawControl('documentArea');
            $this->redrawControl('data');
        }else{
            $this->filter = [];
        }
    }

    /**
     * Signal - RedrawRow
     * @param mixed $rowID
     */
    public function handleRedrawRow($rowID): void
    {
        if($this->presenter->isAjax())
        {
            $this->data = $this->getDataFromSource($rowID);
            $this->redrawControl('documentArea');
            $this->redrawControl('data');
        }
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
        }

        if($this->isEditable === true)
        {
            $this->editContainer = $this['form']->addContainer('edit');
            $this->editContainer->addHidden($this->primaryColumn);
            $this->addRowActionDirectly($this->documentTemplate->getRowActionEdit());
        }

        foreach($this->columns as $columnName => $column)
        {
            if($this->isFilterable === true)
                $column->addFilterFormInput();
            if($this->isEditable === true)
                $column->addEditFormInput();
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
        $this->template->isEditable = $this->isEditable;
        $this->template->editMode = $this->editMode;
        $this->template->hasActionsColumn = $this->isFilterable || count($this->rowActions) > 0;
        $this->template->rowActionsOrder = $this->rowActionsOrder;
        $this->template->rowActions = $this->rowActions;

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

        $data = $this->data ?? $this->getDataFromSource();
        $this->template->columns = $this->getColumns(true);
        $this->template->primaryColumn = $this->primaryColumn;
        $this->template->editRowKey = $this->editKey;
        $this->template->editColumn = $this->editColumn;
        $this->template->data = $data;
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
        return $form;
    }

    /**
     * Edit form success
     * @param Button $button
     * @param ArrayHash $values
     */
    public function editFormSuccess(Button $button, ArrayHash $values): void
    {
        if($this->presenter->isAjax())
        {
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
            $this->redrawControl('documentArea');
            $this->redrawControl('data');
        }
    }

    /**
     * Filter form success
     * @param Button $button
     * @param ArrayHash $values
     * @throws AbortException
     * @internal
     */
    public function filterFormSuccess(Button $button, ArrayHash $values): void
    {
        $filterValues = (array)$values['filter'];
        foreach($filterValues as $key => $value)
            if(empty($value))
                unset($filterValues[$key]);
        $this->filter = $filterValues;
        $this->editKey = null;
        $this->editMode = false;
        if(count($this->filter) > 0)
        {
            $this->handleRedrawData();
        }else{
            $this->handleRedrawGrid();
        }
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
    protected function getColumns($onlyVisible=false): array
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
        $getDataFn = $this->dataSourceCallback;
        $data = $getDataFn($this->filter);
        if(is_countable($data) === false || count($data) == 0)
            return null;
        return $data;
    }

    /**
     * Get snipped id of main content snippet
     * @return string
     */
    public function getMainSnippetId(): string
    {
        return $this->getSnippetId(self::MAIN_CONTENT_SNIPPET);
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
     * Set Grid filterable
     * @param bool $filterable
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
}