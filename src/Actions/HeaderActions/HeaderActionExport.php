<?php
declare(strict_types=1);

namespace e2221\NetteGrid\Actions\HeaderActions;


use e2221\NetteGrid\Column\IColumn;
use Nette\Application\UI\InvalidLinkException;

class HeaderActionExport extends HeaderAction
{
    /** @var IColumn[]|null for case null - all columns will be exported */
    protected ?array $columnsToExport=null;

    /** @var bool Respect active filter */
    protected bool $respectFilter=false;

    /** @var string Export file name (without extension) */
    protected string $exportFileName='export';

    /** @var bool Export with header */
    protected bool $exportWithHeader=true;

    /** @var bool Export with hidden columns (usually id column) */
    protected bool $exportHiddenColumns=true;

    /** @var string Export file encoding (cs - convert to windows-1250) */
    protected string $encoding='utf-8';

    /** @var string Set csv delimiter */
    protected string $delimiter=';';

    /**
     * @throws InvalidLinkException
     */
    public function beforeRender(): void
    {
        $this->addIconElement('fas fa-file-export', [], true);
        $this->setLink($this->netteGrid->link('export!', $this->name));
        parent::beforeRender();
    }

    /**
     * Add column to export
     * @param IColumn $column
     * @return HeaderActionExport
     */
    public function addColumnToExport(IColumn $column): self
    {
        $this->columnsToExport[$column->getName()] = $column;
        return $this;
    }

    /**
     * @return IColumn[]|null
     */
    public function getColumnsToExport(): ?array
    {
        return $this->columnsToExport;
    }

    /**
     * Set respect filter
     * @param bool $respectFilter
     * @return HeaderActionExport
     */
    public function setRespectFilter(bool $respectFilter=true): self
    {
        $this->respectFilter = $respectFilter;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRespectFilter(): bool
    {
        return $this->respectFilter;
    }

    /**
     * Set export file name (without extension)
     * @param string $exportFileName
     * @return HeaderActionExport
     */
    public function setExportFileName(string $exportFileName): self
    {
        $this->exportFileName = $exportFileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getExportFileName(): string
    {
        return $this->exportFileName;
    }

    /**
     * Set export with header
     * @param bool $exportWithHeader
     * @return HeaderActionExport
     */
    public function setExportWithHeader(bool $exportWithHeader=true): self
    {
        $this->exportWithHeader = $exportWithHeader;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportWithHeader(): bool
    {
        return $this->exportWithHeader;
    }

    /**
     * Set export with hidden columns
     * @param bool $exportHiddenColumns
     * @return HeaderActionExport
     */
    public function setExportHiddenColumns(bool $exportHiddenColumns=true): self
    {
        $this->exportHiddenColumns = $exportHiddenColumns;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportHiddenColumns(): bool
    {
        return $this->exportHiddenColumns;
    }

    /**
     * Set encoding
     * @param string $encoding
     * @return HeaderActionExport
     */
    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Set delimiter
     * @param string $delimiter
     * @return HeaderActionExport
     */
    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

}