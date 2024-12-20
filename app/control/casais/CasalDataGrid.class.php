<?php

/**
 * SaleList
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
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;

    use Adianti\Base\AdiantiStandardListTrait;

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('adea');          // defines the database
        $this->setActiveRecord('ViewCasal');         // defines the active record
        $this->setDefaultOrder('relacao_id', 'asc');    // defines the default order
        $this->addFilterField('relacao_id', '=', 'relacao_id'); // filterField, operator, formField
        $this->addFilterField('relacao_id', '=', 'casal_id'); // filterField, operator, formField

        $this->addFilterField('dt_inicial', '>=', 'date_from', function ($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction

        $this->addFilterField('dt_inicial', '<=', 'date_to', function ($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction

        $this->addFilterField('ele_id', '=', 'ele_id'); // filterField, operator, formField
        $this->addFilterField('ela_id', '=', 'ela_id'); // filterField, operator, formField

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_CasalDataGrid');
        $this->form->setFormTitle('Lista de Casais (Filtros)');

        $this->setAfterSearchCallback( [$this, 'closeWindow' ] );


        // create the form fields
        $id        = new TEntry('relacao_id');
        $date_from = new TDate('date_from');
        $date_to   = new TDate('date_to');

        //$casal_id = new TDBUniqueSearch('casal_id', 'adea', 'ViewCasal', 'relacao_id', 'casal');
        //$casal_id->setMinLength(1);
        //$casal_id->setMask('{casal} ({Casamento})');

        $casal_id = new TDBCombo('casal_id', 'adea', 'ViewCasal', 'relacao_id', '{casal} ({Casamento})', 'relacao_id');
        $casal_id->enableSearch();

        $ele_id = new TDBCombo('ele_id', 'adea', 'ViewCasal', 'ele_id', '{ele_nome} ({EleNascimento})', 'ele_id');
        $ele_id->enableSearch();

        $ela_id = new TDBCombo('ela_id', 'adea', 'ViewCasal', 'ela_id', '{ela_nome} ({ElaNascimento})', 'ela_id');
        $ela_id->enableSearch();

        //$ele_id = new TDBUniqueSearch('ele_id', 'adea', 'ViewCasal', 'Ele->nome', 'Ele->nome');
        //$ele_id->setMinLength(1);
        //$ele_id->setMask('{Ele->nome} ({Ele->dt_nascimento})');

        // add the fields
        $this->form->addFields([new TLabel('Id')],          [$id]);
        $this->form->addFields(
            [new TLabel('Data (Inicial)')],
            [$date_from],
            [new TLabel('Data (Final)')],
            [$date_to]
        );
        $this->form->addFields([new TLabel('Casal')],    [$casal_id]);
        $this->form->addFields([new TLabel('Ele')],    [$ele_id]);
        $this->form->addFields([new TLabel('Ela')],    [$ela_id]);

        $id->setSize('50%');
        $date_from->setSize('100%');
        $date_to->setSize('100%');
        $date_from->setMask('dd/mm/yyyy');
        $date_to->setMask('dd/mm/yyyy');
        $casal_id->setSize('100%');
        $ele_id->setSize('100%');
        $ela_id->setSize('100%');

        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('CasalDataGrid_filter_data'));

        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        //$this->datagrid->disableDefaultClick();

        // creates the datagrid columns
        $col_relacao_id = new TDataGridColumn('relacao_id', 'Id', 'center');
        $col_ele_id = new TDataGridColumn('Ele->nome', 'Ele', 'right');
        $col_ela_id = new TDataGridColumn('Ela->nome', 'Ela', 'right');
        //$col_parentesco_id = new TDataGridColumn('parentesco_id', 'Id', 'right');
        //$col_parentesco = new TDataGridColumn('parentesco', 'Id', 'right');		
        $col_casal = new TDataGridColumn('casal', 'Casal', 'left');
        $col_dt_inicial = new TDataGridColumn('dt_inicial', 'Data', 'center');
        //$col_dt_final = new TDataGridColumn('dt_final', 'Data Final', 'center');
        $col_tipo_vinculo = new TDataGridColumn('tipo_vinculo', 'Vínculo', 'left');
        $col_status_relacao_id = new TDataGridColumn('status_relacao_id', 'Status', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($col_relacao_id);
        $this->datagrid->addColumn($col_casal);

        $this->datagrid->addColumn($col_ele_id);
        $this->datagrid->addColumn($col_ela_id);

        $this->datagrid->addColumn($col_dt_inicial);
        //$this->datagrid->addColumn($col_dt_final);
        $this->datagrid->addColumn($col_tipo_vinculo);
        $this->datagrid->addColumn($col_status_relacao_id);

        /*
        $col_status_relacao_id->setTransformer( function($value, $object, $row, $cell) {
            $cell->href='#';
            $dropdown = new TDropDown($object->status->name, '');
            $dropdown->getButton()->style .= ';color:white;border-radius:5px;background:'.$object->status->color;
            
            TTransaction::open('samples');
            $statuses = SaleStatus::orderBy('id')->load();
            foreach ($statuses as $status)
            {
                $params = ['id' => $object->id,
                           'status_id' => $status->id, 
                           'offset' => $_REQUEST['offset'] ?? 0,
                           'limit' => $_REQUEST['limit'] ?? 10,
                           'page' => $_REQUEST['page'] ?? 1,
                           'first_page' => $_REQUEST['first_page'] ?? 1,
                           'register_state' => 'false'];
                
                $dropdown->addAction( $status->name, new TAction([$this, 'changeStatus'], $params ), 'fas:circle  ' . $status->color );
            }
            TTransaction::close();
            
            return $dropdown;
        });
        */

        // creates the datagrid column actions
        $col_relacao_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'relacao_id']);
        $col_casal->setAction(new TAction([$this, 'onReload']), ['order' => 'casal']);
        $col_dt_inicial->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_inicial']);
        //$col_dt_final->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_final']);
        $col_tipo_vinculo->setAction(new TAction([$this, 'onReload']), ['order' => 'tipo_vinculo']);
        $col_status_relacao_id->setAction(new TAction([$this, 'onReload']), ['order' => 'status_relacao_id']);

        // define the transformer method over date
        $col_dt_inicial->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        /*
        $col_dt_final->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            if ($value == '0000-00-00') {
                return '-';
            } else {
                return $date->format('d/m/Y');
            }
        });
        */

        $action_view   = new TDataGridAction(['CasalPanel', 'onView'],   ['key' => '{relacao_id}', 'register_state' => 'false']);
        //$action_edit   = new TDataGridAction(['SaleForm', 'onEdit'],   ['key' => '{id}', 'register_state' => 'false'] );
        //$action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );

        $this->datagrid->addAction($action_view, _t('View details'), 'fa:search green fa-fw');
        //$this->datagrid->addAction($action_edit, 'Edit',   'far:edit blue fa-fw');
        //$this->datagrid->addAction($action_delete, 'Delete', 'far:trash-alt red fa-fw');

        // create the datagrid model
        $this->datagrid->createModel();

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        $panel->addHeaderActionLink('Novo', new TAction(['DadosIniciaisPF', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
        $panel->addHeaderActionLink('Limpar', new TAction([$this, 'clear'], ['register_state' => 'false']), 'fa:eraser red');
        $btn = $panel->addHeaderActionLink('Filtros', new TAction([$this, 'onShowWindowFilters']), 'fa:filter');
        $btn->class = 'btn btn-primary';

        // header actions
        //$dropdown = new TDropDown('Exportar', 'fa:list');
        //$dropdown->setPullSide('right');
        //$dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        //$dropdown->addAction('Gerar CSV', new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        //$dropdown->addAction('Gerar PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        //$panel->addHeaderWidget( $dropdown );

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($panel);
        parent::add($container);
    }

    /**
     *
     */
    public static function onShowWindowFilters($param = null)
    {
        try
        {
            // create a window
            $page = TWindow::create('Filters', 600, null);
            $page->removePadding();
            
            // instantiate self class, populate filters in construct
            $embed = new self;
            
            // embed form inside window
            $page->add($embed->form);
            $page->setIsWrapped(true);
            $page->show();
        }
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());    
        }
    }
    
    /**
     * Close Windows
     */
    public static function closeWindow($param = null)
    {
        TWindow::closeWindow();
    }

    /**
     *
     */
    public function changeStatus($param)
    {
        try {
            TTransaction::open('samples');
            $sale = Sale::find($param['id']);
            $sale->status_id = $param['status_id'];
            $sale->store();
            TTransaction::close();

            $this->onReload($param);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
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
