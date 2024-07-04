<?php

use Adianti\Widget\Form\TEntry;

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
class ParentescosDataGridView extends TPage
{
    protected $form;     // registration form
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
        $this->setActiveRecord('PessoaParentesco');       // defines the active record
        $this->addFilterField('(SELECT nome from pessoa WHERE id=pessoa_parentesco.pessoa_id)', 'like', 'pessoa_id'); // add a filter field
        $this->addFilterField('parentesco_id', '=', 'parentesco_id'); // filter field, operator, form field
        $this->addFilterField('(SELECT nome from pessoa WHERE id=pessoa_parentesco.pessoa_parente_id)', 'like', 'pessoa_parente_id'); // add a filter field
        $this->setDefaultOrder('id', 'asc');  // define the default order

        // creates the form
        $this->form = new BootstrapFormBuilder('form_parentescos');
        $this->form->setFormTitle('Relação de Parentescos');

        $pessoa_id = new TEntry('pessoa_id');
        $pessoa_id->setSize('100%');
        $this->form->addFields([new TLabel('Pessoa:')], [$pessoa_id]);

        $filterPa = new TCriteria;
        $filterPa->add(new TFilter('lista_id', '=', '12'));
        $parentesco_id = new TDBCombo('parentesco_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterPa);
        $parentesco_id->enableSearch();
        $parentesco_id->setSize('100%');
        $this->form->addFields([new TLabel('Parentesco:')], [$parentesco_id]);

        $pessoa_parente_id = new TEntry('pessoa_parente_id');
        $pessoa_parente_id->setSize('100%');
        $this->form->addFields([new TLabel('Parente:')], [$pessoa_parente_id]);

        // add form actions
        $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addActionLink('Novo',  new TAction(['EddPessoaParentesco', 'onClear']), 'fa:plus-circle green');
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');

        // keep the form filled with the search data
        $this->form->setData(TSession::getValue('ParentescosDataGridView_filter_data'));

        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'center', '10%');
        $col_pessoa_id  = new TDataGridColumn('Pessoa->nome', 'Pessoa', 'left');
        $col_parentesco_id = new TDataGridColumn('Parentesco->item', 'Parentesco', 'left');
        $col_pessoa_parente_id = new TDataGridColumn('PessoaParente->nome', 'Parente', 'left');
        $col_obs_parentesco = new TDataGridColumn('obs_parentesco', 'Observação', 'left');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_pessoa_id);
        $this->datagrid->addColumn($col_parentesco_id);
        $this->datagrid->addColumn($col_pessoa_parente_id);
        $this->datagrid->addColumn($col_obs_parentesco);

        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa_id']);

        $action1 = new TDataGridAction(['EddPessoaParentesco', 'onEdit'],   ['key' => '{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}']);

        $this->datagrid->addAction($action1, 'Edit',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Delete', 'far:trash-alt red');

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));

        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        // add the table inside the page
        parent::add($vbox);
    }

    /**
     * Clear filters
     */
    function clear()
    {
        $this->clearFilters();
        $this->onReload();
    }
}
