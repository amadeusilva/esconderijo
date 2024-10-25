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
class EncontroDataGrid extends TPage
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
        $this->setActiveRecord('ViewEncontro');         // defines the active record
        $this->setDefaultOrder('id', 'asc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('relacao_id', '=', 'casal_id'); // filterField, operator, formField

        $this->addFilterField('dt_inicial', '>=', 'date_from', function ($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction

        $this->addFilterField('dt_inicial', '<=', 'date_to', function ($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_EncontroDataGrid');
        $this->form->setFormTitle('Lista de Encontros (Filtros)');

        // create the form fields
        $id        = new TEntry('id');
        $date_from = new TDate('date_from');
        $date_to   = new TDate('date_to');

        // add the fields
        $this->form->addFields([new TLabel('Id')],          [$id]);
        $this->form->addFields(
            [new TLabel('Data (Inicial)')],
            [$date_from],
            [new TLabel('Data (Final)')],
            [$date_to]
        );

        $id->setSize('50%');
        $date_from->setSize('100%');
        $date_to->setSize('100%');
        $date_from->setMask('dd/mm/yyyy');
        $date_to->setMask('dd/mm/yyyy');

        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('EncontroDataGrid_filter_data'));

        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Novo',  new TAction(['EncontroForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        //$this->datagrid->disableDefaultClick();

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'center');
        $col_num = new TDataGridColumn('num', 'Número', 'center');
        $col_evento = new TDataGridColumn('evento', 'Evento', 'left');
        $col_local1 = new TDataGridColumn('local', 'Local', 'left');
        $col_local2 = new TDataGridColumn('NomePopular->popular', 'Local', 'left');
        $col_endereco = new TDataGridColumn('endereco', 'Endereço', 'left');
        $col_dt_inicial = new TDataGridColumn('dt_inicial', 'Início', 'center');
        $col_dt_final = new TDataGridColumn('dt_final', 'Término', 'center');
        $col_tema = new TDataGridColumn('tema', 'Tema', 'left');
        //divisa
        $col_cantico = new TDataGridColumn('cantico', 'Cântico', 'left');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_num);
        $this->datagrid->addColumn($col_evento);
        $this->datagrid->addColumn($col_local1);
        $this->datagrid->addColumn($col_local2);
        $this->datagrid->addColumn($col_endereco);
        $this->datagrid->addColumn($col_dt_inicial);
        $this->datagrid->addColumn($col_dt_final);
        $this->datagrid->addColumn($col_tema);
        $this->datagrid->addColumn($col_cantico);

        // define the transformer method over date
        $col_dt_inicial->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $col_dt_final->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $col_local1->enableAutoHide(1000);
        $col_endereco->enableAutoHide(1000);
        $col_tema->enableAutoHide(1000);
        $col_cantico->enableAutoHide(1000);

        // creates the datagrid column actions
        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_num->setAction(new TAction([$this, 'onReload']), ['order' => 'num']);
        $col_evento->setAction(new TAction([$this, 'onReload']), ['order' => 'evento']);
        $col_local1->setAction(new TAction([$this, 'onReload']), ['order' => 'local']);
        $col_local2->setAction(new TAction([$this, 'onReload']), ['order' => 'local']);
        $col_endereco->setAction(new TAction([$this, 'onReload']), ['order' => 'endereco']);
        $col_dt_inicial->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_inicial']);
        $col_dt_final->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_final']);
        $col_tema->setAction(new TAction([$this, 'onReload']), ['order' => 'tema']);
        $col_cantico->setAction(new TAction([$this, 'onReload']), ['order' => 'cantico']);

        $action_view   = new TDataGridAction(['EncontroPanel', 'onView'],   ['key' => '{id}', 'register_state' => 'false']);
        $action_edit   = new TDataGridAction(['EncontroForm', 'onEdit'],   ['key' => '{id}', 'register_state' => 'false']);
        $actionverpdfdigitalizacaolivrao = new TDataGridAction([$this, 'onViewDigitalizaoLivrao'],   ['key' => '{id}', 'register_state' => 'false']);
        $actionverpdfdigitalizacaolivrao->setDisplayCondition(array($this, 'displayColumn'));
        //$action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );


        $this->datagrid->addAction($action_view, 'Ver Detalhes', 'fa:search green fa-fw');
        $this->datagrid->addAction($action_edit, 'Editar',   'far:edit blue fa-fw');
        $this->datagrid->addAction($actionverpdfdigitalizacaolivrao, 'Ver Digitalização', 'far:fa-sharp fa-solid fa-file-pdf red');
        //$this->datagrid->addAction($action_delete, 'Delete', 'far:trash-alt red fa-fw');

        // create the datagrid model
        $this->datagrid->createModel();

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }

    /**
     * Define when the action can be displayed
     */
    public function displayColumn($object)
    {

        $encontro = Encontro::find($object->id);

        if (isset($encontro->livrao_pdf) and !empty($encontro->livrao_pdf)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * method onView()
     * Executed when the user clicks at the view button
     */
    public static function onViewDigitalizaoLivrao($param)
    {

        try {
            if ($param['key']) {

                TTransaction::open('adea');   // open a transaction with database 'samples'

                // get the parameter
                $encontro = new Encontro($param['key']);

                $win = TWindow::create('Digitalização (PDF) - ' . $encontro->num . ' ' . $encontro->Evento->item, 0.8, 0.8);
                //if ($object->livrao_pdf) {
                $object = new TElement('object');
                $object->data  = 'http://localhost/ADEA/download.php?file=' . $encontro->livrao_pdf;
                $object->type  = 'application/pdf';
                $object->style = "width: 100%; height:calc(100% - 10px)";
                $object->add('O navegador não suporta a exibição deste conteúdo, <a style="color:#007bff;" target=_newwindow href="' . $object->data . '"> clique aqui para baixar</a>...');
                //} //else {
                //   $img_doc = new TImage('app/images/dadosderelacao/semdocimagem.jpg');
                //}

                $win->add($object);
                //$win->add("<center><img style='height:500px;float:right;margin:5px' src='{$object->doc_imagem}'></center>");
                $win->show();

                TTransaction::close();           // close the transaction
            } else {
                $this->form->clear(true);
            }
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
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
