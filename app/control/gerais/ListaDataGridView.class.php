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
class ListaDataGridView extends TPage
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
        $this->setActiveRecord('Lista'); // defines the active record
        $this->setDefaultOrder('id', 'asc');  // defines the default order
        $this->addFilterField('id', '=', 'id'); // add a filter field
        $this->addFilterField('lista', 'like', 'lista'); // add a filter field
        $this->addFilterField('obs', 'like', 'obs'); // add a filter field
        //$this->addFilterField('(SELECT name from city WHERE id=customer.city_id)', 'like', 'city_name'); // add a filter field
        //$this->setOrderCommand('city->name', '(select name from city where city_id = id)');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->enablePopover('Popover', 'Hi <b>{name}</b>, <br> that lives at <b>{city->name} - {city->state->name}</b>');
        
        // creates the datagrid columns
        $col_id               = new TDataGridColumn('id', 'COD.', 'center', '5%');
        $col_lista            = new TDataGridColumn('lista', 'Descrição', 'rigth');
        $col_obs              = new TDataGridColumn('obs', 'Observação', 'rigth');
        
        $col_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $col_lista->setAction(new TAction([$this, 'onReload']), ['order' => 'lista']);
        $col_obs->setAction(new TAction([$this, 'onReload']), ['order' => 'obs']);

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_lista);
        $this->datagrid->addColumn($col_obs);
        
        // creates two datagrid actions
        $action1 = new TDataGridAction(['ListaFormView', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Edit', 'far:edit blue');
        $this->datagrid->addAction($action2 ,'Delete', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the form
        $this->form = new TForm('form_search_lista');
        
        // add datagrid inside form
        $this->form->add($this->datagrid);
        $this->form->style = 'overflow-x:auto';
        
        // create the form fields
        $id       = new TEntry('id');
        $lista    = new TEntry('lista');
        $obs      = new TEntry('obs');
        
        // ENTER fires exitAction
        $id->exitOnEnter();
        $lista->exitOnEnter();
        $obs->exitOnEnter();
        
        $id->setSize('100%');
        $lista->setSize('100%');
        $obs->setSize('100%');
        
        // avoid focus on tab
        $id->tabindex = -1;
        $lista->tabindex = -1;
        $obs->tabindex = -1;
        
        $id->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $lista->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $obs->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        
        // create row with search inputs
        $tr = new TElement('tr');
        $this->datagrid->prependRow($tr);
        
        $tr->add( TElement::tag('td', ''));
        $tr->add( TElement::tag('td', ''));
        $tr->add( TElement::tag('td', $id));
        $tr->add( TElement::tag('td', $lista));
        $tr->add( TElement::tag('td', $obs));
        
        $this->form->addField($id);
        $this->form->addField($lista);
        $this->form->addField($obs);
        
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data'));
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->enableCounters();
        
        $panel = new TPanelGroup('Listas Gerais');
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
        
        $panel->addHeaderActionLink('Novo',  new TAction(['ListaFormView', 'onEdit'], ['register_state' => 'false']), 'fa:plus green' );
        
        // creates the page structure using a vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        
        // add the box inside the page
        parent::add($vbox);
    }
}
