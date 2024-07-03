<?php
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
class CasalDataGridView extends TPage
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
        $this->setActiveRecord('Casal'); // defines the active record
        $this->setDefaultOrder('id', 'asc');  // defines the default order
        $this->addFilterField('id', '=', 'id'); // add a filter field
        $this->addFilterField('ele_id', '=', 'ele_id'); // add a filter field
        $this->addFilterField('ela_id', '=', 'ela_id'); // add a filter field
        $this->addFilterField('dt_casamento', '=', 'dt_casamento'); // add a filter field
        $this->addFilterField('cartorio_id', '=', 'cartorio_id'); // add a filter field
        $this->addFilterField('fone', '=', 'fone'); // add a filter field
        $this->addFilterField('email', '=', 'email'); // add a filter field
        $this->addFilterField('status_pessoa', '=', 'status_pessoa'); // add a filter field
        //$this->addFilterField('(SELECT logradouro FROM logradouro WHERE id=logradouro_id.id)', 'like', 'endereco_id'); // add a filter field
        $this->addFilterField('(SELECT n from endereco WHERE id=pessoa.endereco_id)', 'like', 'endereco_id'); // add a filter field
        //$this->setOrderCommand('status_pessoa', '(select nome from pessoa where pessoa_id = id)');
        
        //filtrar pessoa juridica
        //$criteria = new TCriteria;
        //$criteria->add(new TFilter('tipo_pessoa', '!=', 77) );
        //$this->setCriteria($criteria); // define a standard filter
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->enablePopover('Popover', 'Hi <b>{name}</b>, <br> that lives at <b>{city->name} - {city->state->name}</b>');
        
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `cep`,
        //`logradouro_id`, `n`, `bairro_id`, `ponto_referencia`, `status_pessoa`, `ck_pessoa`

        //id`, `pessoa_id`, `genero`, `dt_nascimento`, `profissao_id`, `tm_camisa`

        // creates the datagrid columns
        $col_id                 = new TDataGridColumn('id', 'COD.', 'center', '5%');
        $col_ele_id             = new TDataGridColumn('Ele->popular', 'Ele', 'left', '10%');
        $col_ela_id             = new TDataGridColumn('Ela->popular', 'Ela', 'left', '10%');
        $col_dt_casamento       = new TDataGridColumn('dt_casamento', 'Casamento', 'center', '10%');
        $col_cartorio_id        = new TDataGridColumn('Cartorio->popular', 'Cartório', 'left', '10%');
        $col_conducao_propria   = new TDataGridColumn('ConducaoPropria', 'Condução', 'left');
        $col_status_casal       = new TDataGridColumn('StatusCasal->item', 'Status', 'center', '5%');
        
        $col_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $col_ele_id->setAction(new TAction([$this, 'onReload']), ['order' => 'ele_id']);
        $col_ela_id->setAction(new TAction([$this, 'onReload']), ['order' => 'ela_id']);
        $col_dt_casamento->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_casamento']);
        $col_cartorio_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cartorio_id']);
        $col_conducao_propria->setAction(new TAction([$this, 'onReload']), ['order' => 'conducao_propria']);
        $col_status_casal->setAction(new TAction([$this, 'onReload']), ['order' => 'status_casal']);

        $col_dt_casamento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_ele_id);
        $this->datagrid->addColumn($col_ela_id);
        $this->datagrid->addColumn($col_dt_casamento);
        $this->datagrid->addColumn($col_cartorio_id);
        $this->datagrid->addColumn($col_conducao_propria);
        $this->datagrid->addColumn($col_status_casal);
        
        // creates two datagrid actions
        $action1 = new TDataGridAction(['CasalFormView', 'onEdit'], ['key'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Edit', 'far:edit blue');
        $this->datagrid->addAction($action2 ,'Delete', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the form
        $this->form = new TForm('form_search_casal');
        
        // add datagrid inside form
        $this->form->add($this->datagrid);
        $this->form->style = 'overflow-x:auto';

        // create the form fields
        $id                 = new TEntry('id');
        $ele_id             = new TEntry('ele_id');
        $ela_id             = new TEntry('ela_id');
        $dt_casamento       = new TEntry('dt_casamento');
        $cartorio_id        = new TEntry('cartorio_id');
        $conducao_propria   = new TEntry('conducao_propria');
        $filterStatusPessoa = new TCriteria;
        $filterStatusPessoa->add(new TFilter('lista_id', '=', '5'));
        $status_casal       = new TDBCombo('status_casal', 'adea', 'ListaItens', 'id', 'item', 'id', $filterStatusPessoa);
        
        // ENTER fires exitAction
        $id->exitOnEnter();
        $ele_id->exitOnEnter();
        $ela_id->exitOnEnter();
        $dt_casamento->exitOnEnter();
        $cartorio_id->exitOnEnter();
        $conducao_propria->exitOnEnter();
        
        $id->setSize('100%');
        $ele_id->setSize('100%');
        $ela_id->setSize('100%');
        $dt_casamento->setSize('100%');
        $cartorio_id->setSize('100%');
        $conducao_propria->setSize('100%');
        $status_casal->setSize('100%');

        // avoid focus on tab
        $id->tabindex = -1;
        $ele_id->tabindex = -1;
        $ela_id->tabindex = -1;
        $dt_casamento->tabindex = -1;
        $cartorio_id->tabindex = -1;
        $conducao_propria->tabindex = -1;
        $status_casal->tabindex = -1;
        
        $id->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        //$ele_id->setChangeAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $ele_id->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $ela_id->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $dt_casamento->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $cartorio_id->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $conducao_propria->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        
        // create row with search inputs
        $tr = new TElement('tr');
        $this->datagrid->prependRow($tr);
        
        $tr->add( TElement::tag('td', ''));
        $tr->add( TElement::tag('td', ''));
        $tr->add( TElement::tag('td', $id));
        $tr->add( TElement::tag('td', $ele_id));
        $tr->add( TElement::tag('td', $ela_id));
        $tr->add( TElement::tag('td', $dt_casamento));
        $tr->add( TElement::tag('td', $cartorio_id));
        $tr->add( TElement::tag('td', $conducao_propria));
        $tr->add( TElement::tag('td', $status_casal));
        
        $this->form->addField($id);
        $this->form->addField($ele_id);
        $this->form->addField($ela_id);
        $this->form->addField($dt_casamento);
        $this->form->addField($cartorio_id);
        $this->form->addField($conducao_propria);
        $this->form->addField($status_casal);
        
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data'));
        
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
        $dropdown->addAction( 'Save as CSV', new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdown->addAction( 'Save as XLS', new TAction([$this, 'onExportXLS'], ['register_state' => 'false', 'static'=>'1']), 'fa:file-excel fa-fw purple' );
        $dropdown->addAction( 'Save as PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdown->addAction( 'Save as XML', new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
        $panel->addHeaderWidget( $dropdown );
        
        $panel->addHeaderActionLink('Novo',  new TAction(['CasalFormView', 'onEdit'], ['register_state' => 'false']), 'fa:plus green' );
        
        // creates the page structure using a vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        
        // add the box inside the page
        parent::add($vbox);
    }
}