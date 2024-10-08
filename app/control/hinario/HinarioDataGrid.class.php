<?php

/**
 * StandardDataGridView Listing
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class HinarioDataGrid extends TPage
{
    protected $datagrid; // listing
    protected $pageNavigation;

    // trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('adea');        // defines the database
        $this->setActiveRecord('Hinario');       // defines the active record

        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'right');
        $col_ordem = new TDataGridColumn('ordem', 'Ordem', 'center');
        $col_titulo = new TDataGridColumn('titulo', 'Título', 'left');
        //$col_hino = new TDataGridColumn('hino', 'Hino', 'left');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_ordem);
        $this->datagrid->addColumn($col_titulo);
        //$this->datagrid->addColumn($col_hino);

        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_ordem->setAction(new TAction([$this, 'onReload']), ['order' => 'ordem']);
        $col_titulo->setAction(new TAction([$this, 'onReload']), ['order' => 'titulo']);
        //$col_hino->setAction(new TAction([$this, 'onReload']), ['order' => 'hino']);

        $action1 = new TDataGridAction(['HinarioPanel', 'onView'],   ['key' => '{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction(['HinarioForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
        $action3 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}']);

        $this->datagrid->addAction($action1, 'Visualizar',   'fa:search blue');
        $this->datagrid->addAction($action2, 'Editar',   'far:edit blue');
        $this->datagrid->addAction($action3, 'Deletar', 'far:trash-alt red');

        // create the datagrid model
        $this->datagrid->createModel();

        // search box
        $input_search = new TEntry('input_search');
        $input_search->placeholder = 'Buscar';
        $input_search->setSize('100%');

        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'id, ordem, titulo, hino');

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));

        $panel = new TPanelGroup('Hinário');
        $panel->addHeaderWidget($input_search);
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // turn on horizontal scrolling inside panel body
        $panel->getBody()->style = "overflow-x:auto;";

        // header actions
        $dropdown = new TDropDown('Export', 'fa:download');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction('Save as CSV', new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static' => '1']), 'fa:table fa-fw blue');
        $dropdown->addAction('Save as XLS', new TAction([$this, 'onExportXLS'], ['register_state' => 'false', 'static' => '1']), 'fa:file-excel fa-fw purple');
        $dropdown->addAction('Save as PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static' => '1']), 'far:file-pdf fa-fw red');
        $dropdown->addAction('Save as XML', new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static' => '1']), 'fa:code fa-fw green');

        // add form actions
        $panel->addHeaderActionLink('Novo',  new TAction(['HinarioForm', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
        $panel->addHeaderWidget($dropdown);

        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);

        // add the table inside the page
        parent::add($vbox);
    }
}
