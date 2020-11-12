<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use Contributte\FormsBootstrap\BootstrapForm;
use e2221\NetteGrid\Actions\HeaderActions\HeaderAction;
use e2221\NetteGrid\Actions\RowAction\RowAction;
use e2221\NetteGrid\Column\Column;
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

    /** @var null|callable  */
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
        $this->template->tableTemplate = $this->documentTemplate->getTableTemplate();
        $this->template->theadTemplate = $this->documentTemplate->getTheadTemplate();
        $this->template->theadTitlesRowTemplate = $this->documentTemplate->getTheadTitlesRowTemplate();

        $this->template->columns = $this->columns;
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
}