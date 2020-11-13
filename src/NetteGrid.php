<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use Contributte\FormsBootstrap\BootstrapForm;
use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Column\Column;
use e2221\NetteGrid\Column\ColumnPrimary;
use e2221\NetteGrid\Column\ColumnText;
use e2221\NetteGrid\Document\DocumentTemplate;
use e2221\NetteGrid\Exceptions\ColumnNotFoundException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class NetteGrid extends Control
{
    /** @var Column[] */
    protected array $columns=[];

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
     * ADD COLUMN
     * ******************************************************************************
     *
     */

    /**
     * Add primary column
     * @param string $name
     * @param string|null $label
     * @return ColumnPrimary
     * @throws ColumnNotFoundException
     */
    public function addColumnPrimary(string $name='id', ?string $label='ID'): ColumnPrimary
    {
        return $this->columns[] = new ColumnPrimary($this, $name, $label);
    }

    /**
     * Add Column text
     * @param string $name
     * @param string|null $label
     * @return ColumnText
     */
    public function addColumnText(string $name, ?string $label=null): ColumnText
    {
        return $this->columns[] = new ColumnText($this, $name, $label);
    }

    /**
     * Default renderer
     */
    public function render(): void
    {
        $this->template->uniqueID = $this->getUniqueId();

        //templates
        $this->template->documentTemplate = $this->documentTemplate;
        $this->template->tableTemplate = $this->documentTemplate->getTableTemplate();
        $this->template->theadTemplate = $this->documentTemplate->getTheadTemplate();
        $this->template->theadTitlesRowTemplate = $this->documentTemplate->getTheadTitlesRowTemplate();
        $this->template->tbodyTemplate = $this->documentTemplate->getTbodyTemplate();
        $this->template->emptyDataRowTemplate = $this->documentTemplate->getEmptyDataRowTemplate();
        $this->template->emptyDataColTemplate = $this->documentTemplate->getEmptyDataColTemplate();


        $data = $this->getDataFromSource();
        $this->template->columns = $this->columns;
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

        return $form;
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
     * @throws ColumnNotFoundException
     */
    public function setPrimaryColumn(string $columnName): self
    {
        if($this->columnExists($columnName))
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
     * Get data from source
     * @return mixed[]|null
     */
    protected function getDataFromSource()
    {
        if(is_null($this->dataSourceCallback))
            return null;
        $getDataFn = $this->dataSourceCallback;
        $data = $getDataFn();
        if(is_countable($data) === false || count($data) == 0)
            return null;
        return $data;
    }
}