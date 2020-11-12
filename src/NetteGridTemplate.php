<?php
declare(strict_types=1);


namespace e2221\NetteGrid;

use e2221\NetteGrid\Column\Column;
use e2221\NetteGrid\Document\Templates\Cols\EmptyDataColTemplate;
use e2221\NetteGrid\Document\Templates\DataRowTemplate;
use e2221\NetteGrid\Document\Templates\EmptyDataRowTemplate;
use e2221\NetteGrid\Document\Templates\TheadTemplate;
use e2221\NetteGrid\Document\Templates\TitlesRowTemplate;
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

    /** @var Column[] */
    public array $columns;

    public NetteGrid $control;
    public Presenter $presenter;
    public string $uniqueID;
    public Document\Templates\TableTemplate $tableTemplate;
    public TheadTemplate $theadTemplate;
    public TitlesRowTemplate $theadTitlesRowTemplate;
    public EmptyDataRowTemplate $emptyDataRowTemplate;
    public EmptyDataColTemplate $emptyDataColTemplate;
    public DataRowTemplate $dataRowTemplate;
    public ?array $data;
    public bool $showEmptyResult;

}