<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use Contributte\FormsBootstrap\BootstrapForm;
use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Column\Column;
use e2221\NetteGrid\Column\ColumnPrimary;
use e2221\NetteGrid\Column\ColumnText;
use e2221\NetteGrid\Column\IColumn;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Exceptions\ColumnNotFoundException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
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

    /** @var string[] Templates with changed blocks */
    protected array $templates=[];

    /** @var string Primary column name */
    protected string $primaryColumn='id';

    /** @var null|callable function(?array $filter=null, ?array $multipleFilter=null, ?array $orderBy=null, ?Paginator $paginator=null){} */
    protected $dataSourceCallback=null;

    /** @var DocumentTemplate include all document template */
    protected DocumentTemplate $documentTemplate;

    /** @var Container|null */
    protected ?Container $filterContainer=null;

    /** @var bool Is there at least one filterable column? */
    protected bool $isFilterable=false;

    public function __construct()
    {
        $this->documentTemplate = new DocumentTemplate($this);
        $this->filterContainer = $this['form']->addContainer('filter');
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
     * HANDLERS
     * ******************************************************************************
     *
     */

    /**
     * Redraw all grid
     */
    public function handleRedrawGrid(): void
    {
        $this->redrawControl('documentArea');
        $this->redrawControl(self::MAIN_CONTENT_SNIPPET);
    }

    public function handleRedrawData(): void
    {
        $this->redrawControl('documentArea');
        $this->redrawControl('data');
    }

    /**
     * Load state
     * @param array $params
     * @throws BadRequestException
     */
    public function loadState(array $params): void
    {
        parent::loadState($params);
        foreach($this->columns as $columnName => $column)
        {
            if($column->addFilterFormInput() === true)
                $this->isFilterable = true;
        }
    }


    /**
     * Default renderer
     */
    public function render(): void
    {
        $this['form']['filter']->setDefaults($this->filter);

        $this->template->uniqueID = $this->getUniqueId();
        $this->template->isFilterable = $this->isFilterable;
        $this->template->hasActionsColumn = $this->isFilterable;

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


        $data = $this->getDataFromSource();
        $this->template->columns = $this->getColumns(true);
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
            ->onClick[] = [$this, 'filterForm'];

        //$form->onSuccess[] = [$this, 'filterForm'];
        return $form;
    }

    /** @internal  */
    public function filterForm($button, ArrayHash $values): void
    {
        //dumpe($values["filter"]);
        $filterValues = (array)$values['filter'];
        foreach($filterValues as $key => $value)
            if(empty($value))
                unset($filterValues[$key]);
        $this->filter = $filterValues;
        $this->handleRedrawData();
    }

    /**
     * Get from
     * @return Form
     */
    public function getForm(): Form
    {
        return $this['form'];
    }

    /**
     * Get filter container
     * @return Container
     * @internal
     */
    public function getFilterContainer(): Container
    {
        return $this->filterContainer;
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
     * @return mixed[]|null
     */
    protected function getDataFromSource()
    {
        if(is_null($this->dataSourceCallback))
            return null;
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
}