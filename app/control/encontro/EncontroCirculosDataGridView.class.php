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
class EncontroCirculosDataGridView extends TPage
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
        $this->setActiveRecord('EncontroCirculos');       // defines the active record
        $this->addFilterField('encontro_id', '=', 'encontro_id'); // filter field, operator, form field
        $this->addFilterField('circulo_id', '=', 'circulo_id'); // filter field, operator, form field
        $this->addFilterField('nome_circulo', 'ilike', 'nome_circulo'); // filter field, operator, form field
        $this->setDefaultOrder('id', 'asc');  // define the default order

        // creates the form
        $this->form = new BootstrapFormBuilder('form_EncontroCirculosDataGrid');
        $this->form->setFormTitle('Círculos');

        $nome_circulo = new TEntry('nome_circulo');
        $nome_circulo->setSize('100%');
        $this->form->addFields([new TLabel('Nome do Círculo:')], [$nome_circulo]);

        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id');
        $encontro_id->enableSearch();
        $encontro_id->setSize('100%');
        $this->form->addFields([new TLabel('Encontro:')], [$encontro_id]);

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterCirculo);
        $circulo_id->enableSearch();
        $circulo_id->setSize('100%');
        $this->form->addFields([new TLabel('Círculo:')], [$circulo_id]);

        $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

        // add form actions
        $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addActionLink('Novo',  new TAction(['EncontroCirculosForm', 'onEdit']), 'fa:plus-circle green');
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');

        // keep the form filled with the search data
        $this->form->setData(TSession::getValue('ParentescosDataGridView_filter_data'));

        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";

        // creates the datagrid columns        
        $col_id    = new TDataGridColumn('id', 'Id', 'center', '10%');
        $col_encontro_id  = new TDataGridColumn('Encontro->sigla', 'Encontro', 'center');
        $col_circulo_id = new TDataGridColumn('CirculoCor', 'Círculo', 'center');
        $col_nome_circulo = new TDataGridColumn('nome_circulo', 'Nome', 'left');
        $col_casal_coord_id = new TDataGridColumn('CasalCoord->casal', 'Coordenador', 'left');
        $col_casal_sec_id = new TDataGridColumn('CasalSec->casal', 'Secretário', 'left');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_encontro_id);
        $this->datagrid->addColumn($col_circulo_id);
        $this->datagrid->addColumn($col_nome_circulo);
        $this->datagrid->addColumn($col_casal_coord_id);
        $this->datagrid->addColumn($col_casal_sec_id);

        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_encontro_id->setAction(new TAction([$this, 'onReload']), ['order' => 'encontro_id']);

        $action1 = new TDataGridAction(['EncontroCirculosForm', 'onEdit'],   ['key' => '{id}']);
        //$action2 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}']);

        $this->datagrid->addAction($action1, 'Edit',   'far:edit blue');
        //$this->datagrid->addAction($action2, 'Delete', 'far:trash-alt red');

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
