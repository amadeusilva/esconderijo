<?php

use Adianti\Widget\Form\TDate;

/**
 * CustomerDataGridView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CasalDataGrid extends TPage
{
    private $form;      // search form
    private $datagrid;  // listing
    private $pageNavigation;

    use Adianti\Base\AdiantiStandardListTrait;

    /**
     * Class constructor
     * Creates the page, the search form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('adea'); // defines the database
        $this->setActiveRecord('ViewCasal'); // defines the active record
        $this->setDefaultOrder('relacao_id', 'asc');  // defines the default order
        $this->addFilterField('relacao_id', '=', 'relacao_id'); // add a filter field
        $this->addFilterField('casal', 'ilike', 'casal'); // add a filter field
        $this->addFilterField('dt_inicial', '=', 'dt_inicial'); // add a filter field
        $this->addFilterField('dt_final', '=', 'dt_final'); // add a filter field
        $this->addFilterField('tipo_vinculo', 'ilike', 'tipo_vinculo'); // add a filter field
        $this->addFilterField('status_relacao_id', '=', 'status_relacao_id'); // add a filter field
        //$this->addFilterField('email', '=', 'email'); // add a filter field
        //$this->addFilterField('status_pessoa', '=', 'status_pessoa'); // add a filter field
        //$this->addFilterField('(SELECT logradouro FROM logradouro WHERE id=logradouro_id.id)', 'like', 'endereco_id'); // add a filter field
        //$this->addFilterField('(SELECT n from endereco WHERE id=pessoa.endereco_id)', 'like', 'endereco_id'); // add a filter field
        //$this->setOrderCommand('status_pessoa', '(select nome from pessoa where pessoa_id = id)');

        //filtrar pessoa juridica
        //$criteria = new TCriteria;
        //$criteria->add(new TFilter('tipo_pessoa', '!=', 1));
        //$this->setCriteria($criteria); // define a standard filter

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->enablePopover('Popover', 'Hi <b>{name}</b>, <br> that lives at <b>{city->name} - {city->state->name}</b>');

        // creates the datagrid columns
        $col_relacao_id = new TDataGridColumn('relacao_id', 'Id', 'center');
        //$col_ele_id = new TDataGridColumn('ele_id', 'Id', 'right');
        //$col_ela_id = new TDataGridColumn('ela_id', 'Id', 'right');
        //$col_parentesco_id = new TDataGridColumn('parentesco_id', 'Id', 'right');
        //$col_parentesco = new TDataGridColumn('parentesco', 'Id', 'right');		
        $col_casal = new TDataGridColumn('casal', 'Casal', 'left');
        $col_dt_inicial = new TDataGridColumn('dt_inicial', 'Data inicial', 'center');
        $col_dt_final = new TDataGridColumn('dt_final', 'Data Final', 'center');
        $col_tipo_vinculo = new TDataGridColumn('tipo_vinculo', 'Vínculo', 'left');
        $col_status_relacao_id = new TDataGridColumn('status_relacao_id', 'Status', 'center');

        $col_relacao_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'relacao_id']);
        $col_casal->setAction(new TAction([$this, 'onReload']), ['order' => 'casal']);
        $col_dt_inicial->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_inicial']);
        $col_dt_final->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_final']);
        $col_tipo_vinculo->setAction(new TAction([$this, 'onReload']), ['order' => 'tipo_vinculo']);
        $col_status_relacao_id->setAction(new TAction([$this, 'onReload']), ['order' => 'status_relacao_id']);

        $this->datagrid->addColumn($col_relacao_id);
        $this->datagrid->addColumn($col_casal);
        $this->datagrid->addColumn($col_dt_inicial);
        $this->datagrid->addColumn($col_dt_final);
        $this->datagrid->addColumn($col_tipo_vinculo);
        $this->datagrid->addColumn($col_status_relacao_id);

        // define the transformer method over date
        $col_dt_inicial->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        $col_dt_final->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            if ($value == '0000-00-00') {
                return '-';
            } else {
                return $date->format('d/m/Y');
            }
        });

        $action1 = new TDataGridAction(['CasalPanel', 'onView'],   ['key' => '{relacao_id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{relacao_id}']);

        $this->datagrid->addAction($action1, 'Visualizar',   'fa:search blue');
        $this->datagrid->addAction($action2, 'Deletar', 'far:trash-alt red');

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the form
        $this->form = new TForm('form_search_casal');

        // add datagrid inside form
        $this->form->add($this->datagrid);
        $this->form->style = 'overflow-x:auto';

        // create the form fields
        $relacao_id            = new TEntry('relacao_id');
        $casal                 = new TEntry('casal');
        $dt_inicial            = new TDate('dt_inicial');
        $dt_inicial->setMask('dd/mm/yyyy');
        $dt_inicial->setDatabaseMask('yyyy-mm-dd');
        $dt_final              = new TDate('dt_final');
        $dt_final->setMask('dd/mm/yyyy');
        $dt_final->setDatabaseMask('yyyy-mm-dd');
        $tipo_vinculo          = new TEntry('tipo_vinculo');
        $status_relacao_id     = new TEntry('status_relacao_id');

        /*
        $filterTipo = new TCriteria;
        $filterTipo->add(new TFilter('id', '!=', 1));
        $filterTipo->add(new TFilter('lista_id', '=', 15));
        $tipo_pessoa        = new TDBCombo('tipo_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterTipo);
        $cpf_cnpj           = new TEntry('cpf_cnpj');
        $nome               = new TEntry('nome');
        $popular            = new TEntry('popular');
        //$fone               = new TEntry('fone');
        //$email              = new TEntry('email');
        //$endereco_id        = new TEntry('endereco_id');
        //$filterStatusPessoa = new TCriteria;
        //$filterStatusPessoa->add(new TFilter('lista_id', '=', 5));
        //$status_pessoa      = new TDBCombo('status_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterStatusPessoa);
        */
        // ENTER fires exitAction

        $relacao_id->exitOnEnter();
        $casal->exitOnEnter();
        $dt_inicial->exitOnEnter();
        $dt_final->exitOnEnter();
        $tipo_vinculo->exitOnEnter();
        $status_relacao_id->exitOnEnter();
        //$endereco_id->exitOnEnter();

        $relacao_id->setSize('100%');
        $casal->setSize('100%');
        $dt_inicial->setSize('100%');
        $dt_final->setSize('100%');
        $tipo_vinculo->setSize('100%');
        $status_relacao_id->setSize('100%');
        //$email->setSize('100%');
        //$endereco_id->setSize('100%');
        //$status_pessoa->setSize('100%');

        // avoid focus on tab
        $relacao_id->tabindex = -1;
        $casal->tabindex = -1;
        $dt_inicial->tabindex = -1;
        $dt_final->tabindex = -1;
        $tipo_vinculo->tabindex = -1;
        $status_relacao_id->tabindex = -1;
        //$email->tabindex = -1;
        //$endereco_id->tabindex = -1;
        //$status_pessoa->tabindex = -1;

        $relacao_id->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        $casal->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        $dt_inicial->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        $dt_final->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        $tipo_vinculo->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        $status_relacao_id->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        //$email->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        //$endereco_id->setExitAction(new TAction([$this, 'onSearch'], ['static' => '1']));
        //$status_pessoa->setChangeAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );

        // create row with search inputs
        $tr = new TElement('tr');
        $this->datagrid->prependRow($tr);

        $tr->add(TElement::tag('td', ''));
        $tr->add(TElement::tag('td', ''));
        $tr->add(TElement::tag('td', $relacao_id));
        $tr->add(TElement::tag('td', $casal));
        $tr->add(TElement::tag('td', $dt_inicial));
        $tr->add(TElement::tag('td', $dt_final));
        $tr->add(TElement::tag('td', $tipo_vinculo));
        $tr->add(TElement::tag('td', $status_relacao_id));
        //$tr->add(TElement::tag('td', $email));
        //$tr->add(TElement::tag('td', $endereco_id));
        //$tr->add( TElement::tag('td', $status_pessoa));

        $this->form->addField($relacao_id);
        $this->form->addField($casal);
        $this->form->addField($dt_inicial);
        $this->form->addField($dt_final);
        $this->form->addField($tipo_vinculo);
        $this->form->addField($status_relacao_id);
        //$this->form->addField($email);
        //$this->form->addField($endereco_id);
        //$this->form->addField($status_pessoa);

        $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->enableCounters();

        $panel = new TPanelGroup('Lista de Casais');
        $panel->add($this->form);
        $panel->addFooter($this->pageNavigation);

        // header actions
        $dropdown = new TDropDown('Exportar', 'fa:download');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction('Save as CSV', new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static' => '1']), 'fa:table fa-fw blue');
        $dropdown->addAction('Save as XLS', new TAction([$this, 'onExportXLS'], ['register_state' => 'false', 'static' => '1']), 'fa:file-excel fa-fw purple');
        $dropdown->addAction('Save as PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static' => '1']), 'far:file-pdf fa-fw red');
        $dropdown->addAction('Save as XML', new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static' => '1']), 'fa:code fa-fw green');
        $panel->addHeaderWidget($dropdown);

        $panel->addHeaderActionLink('Novo',  new TAction(['DadosIniciaisPF', 'onClear'], ['register_state' => 'false']), 'fa:plus green');

        // creates the page structure using a vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);

        // add the box inside the page
        parent::add($vbox);
    }
}
