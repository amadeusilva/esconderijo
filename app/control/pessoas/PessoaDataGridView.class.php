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
class PessoaDataGridView extends TPage
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
        $this->setActiveRecord('Pessoa'); // defines the active record
        $this->setDefaultOrder('id', 'asc');  // defines the default order
        $this->addFilterField('id', '=', 'id'); // add a filter field
        $this->addFilterField('tipo_pessoa', '=', 'tipo_pessoa'); // add a filter field
        $this->addFilterField('cpf_cnpj', '=', 'cpf_cnpj'); // add a filter field
        $this->addFilterField('nome', '=', 'nome'); // add a filter field
        $this->addFilterField('popular', '=', 'popular'); // add a filter field
        $this->addFilterField('fone', '=', 'fone'); // add a filter field
        $this->addFilterField('email', '=', 'email'); // add a filter field
        $this->addFilterField('status_pessoa', '=', 'status_pessoa'); // add a filter field
        //$this->addFilterField('(SELECT logradouro FROM logradouro WHERE id=logradouro_id.id)', 'like', 'endereco_id'); // add a filter field
        $this->addFilterField('(SELECT n from endereco WHERE id=pessoa.endereco_id)', 'like', 'endereco_id'); // add a filter field
        //$this->setOrderCommand('status_pessoa', '(select nome from pessoa where pessoa_id = id)');
        
        //filtrar pessoa juridica
        $criteria = new TCriteria;
        $criteria->add(new TFilter('tipo_pessoa', '!=', 77) );
        $this->setCriteria($criteria); // define a standard filter
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->enablePopover('Popover', 'Hi <b>{name}</b>, <br> that lives at <b>{city->name} - {city->state->name}</b>');
        
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `cep`,
        //`logradouro_id`, `n`, `bairro_id`, `ponto_referencia`, `status_pessoa`, `ck_pessoa`

        //id`, `pessoa_id`, `genero`, `dt_nascimento`, `profissao_id`, `tm_camisa`

        // creates the datagrid columns
        $col_id                 = new TDataGridColumn('id', 'COD.', 'center', '5%');
        $col_tipo_pessoa        = new TDataGridColumn('TipoPessoa->item', 'Tipo', 'center', '10%');
        $col_cpf_cnpj           = new TDataGridColumn('cpf_cnpj', 'CNPJ', 'center', '10%');
        $col_nome               = new TDataGridColumn('nome', 'Nome', 'left');
        $col_popular            = new TDataGridColumn('popular', 'Popular', 'left');
        $col_fone               = new TDataGridColumn('fone', 'Fone', 'left');
        $col_email              = new TDataGridColumn('email', 'Email', 'left');
        $col_endereco_id        = new TDataGridColumn('EnderecoCompleto', 'Endereço', 'left');
        $col_status_pessoa      = new TDataGridColumn('StatusPessoa->item', 'Status', 'center');
        
        $col_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $col_tipo_pessoa->setAction(new TAction([$this, 'onReload']), ['order' => 'tipo_pessoa']);
        $col_cpf_cnpj->setAction(new TAction([$this, 'onReload']), ['order' => 'cpf_cnpj']);
        $col_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $col_popular->setAction(new TAction([$this, 'onReload']), ['order' => 'popular']);
        $col_fone->setAction(new TAction([$this, 'onReload']), ['order' => 'fone']);
        $col_email->setAction(new TAction([$this, 'onReload']), ['order' => 'email']);
        $col_endereco_id->setAction(new TAction([$this, 'onReload']), ['order' => 'endereco_id']);
        $col_status_pessoa->setAction(new TAction([$this, 'onReload']), ['order' => 'status_pessoa']);

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_tipo_pessoa);
        $this->datagrid->addColumn($col_cpf_cnpj);
        $this->datagrid->addColumn($col_nome);
        $this->datagrid->addColumn($col_popular);
        $this->datagrid->addColumn($col_fone);
        $this->datagrid->addColumn($col_email);
        $this->datagrid->addColumn($col_endereco_id);
        $this->datagrid->addColumn($col_status_pessoa);
        
        // creates two datagrid actions
        $action1 = new TDataGridAction(['PessoaFormView', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Edit', 'far:edit blue');
        $this->datagrid->addAction($action2 ,'Delete', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the form
        $this->form = new TForm('form_search_pessoa');
        
        // add datagrid inside form
        $this->form->add($this->datagrid);
        $this->form->style = 'overflow-x:auto';

        // create the form fields
        $id                 = new TEntry('id');
        $filterTipo = new TCriteria;
        $filterTipo->add(new TFilter('id', '!=', '77'));
        $filterTipo->add(new TFilter('lista_id', '=', '15'));
        $tipo_pessoa        = new TDBCombo('tipo_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterTipo);
        $cpf_cnpj           = new TEntry('cpf_cnpj');
        $nome               = new TEntry('nome');
        $popular            = new TEntry('popular');
        $fone               = new TEntry('fone');
        $email              = new TEntry('email');
        $endereco_id        = new TEntry('endereco_id');
        $filterStatusPessoa = new TCriteria;
        $filterStatusPessoa->add(new TFilter('lista_id', '=', '5'));
        $status_pessoa      = new TDBCombo('status_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterStatusPessoa);
        
        // ENTER fires exitAction
        $id->exitOnEnter();
        $cpf_cnpj->exitOnEnter();
        $nome->exitOnEnter();
        $popular->exitOnEnter();
        $fone->exitOnEnter();
        $email->exitOnEnter();
        $endereco_id->exitOnEnter();
        
        $id->setSize('100%');
        $tipo_pessoa->setSize('100%');
        $cpf_cnpj->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $endereco_id->setSize('100%');
        $status_pessoa->setSize('100%');

        // avoid focus on tab
        $id->tabindex = -1;
        $tipo_pessoa->tabindex = -1;
        $cpf_cnpj->tabindex = -1;
        $nome->tabindex = -1;
        $popular->tabindex = -1;
        $fone->tabindex = -1;
        $email->tabindex = -1;
        $endereco_id->tabindex = -1;
        $status_pessoa->tabindex = -1;
        
        $id->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $tipo_pessoa->setChangeAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $cpf_cnpj->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $nome->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $popular->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $fone->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $email->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $endereco_id->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $status_pessoa->setChangeAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        
        // create row with search inputs
        $tr = new TElement('tr');
        $this->datagrid->prependRow($tr);
        
        $tr->add( TElement::tag('td', ''));
        $tr->add( TElement::tag('td', ''));
        $tr->add( TElement::tag('td', $id));
        $tr->add( TElement::tag('td', $tipo_pessoa));
        $tr->add( TElement::tag('td', $cpf_cnpj));
        $tr->add( TElement::tag('td', $nome));
        $tr->add( TElement::tag('td', $popular));
        $tr->add( TElement::tag('td', $fone));
        $tr->add( TElement::tag('td', $email));
        $tr->add( TElement::tag('td', $endereco_id));
        $tr->add( TElement::tag('td', $status_pessoa));
        
        $this->form->addField($id);
        $this->form->addField($tipo_pessoa);
        $this->form->addField($cpf_cnpj);
        $this->form->addField($nome);
        $this->form->addField($popular);
        $this->form->addField($fone);
        $this->form->addField($email);
        $this->form->addField($endereco_id);
        $this->form->addField($status_pessoa);
        
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data'));
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->enableCounters();
        
        $panel = new TPanelGroup('Lista de Pessoas');
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
        
        $panel->addHeaderActionLink('Novo',  new TAction(['PessoaFormView', 'onEdit'], ['register_state' => 'false']), 'fa:plus green' );
        
        // creates the page structure using a vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        
        // add the box inside the page
        parent::add($vbox);
    }
}
