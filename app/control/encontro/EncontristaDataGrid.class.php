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
class EncontristaDataGrid extends TPage
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

        //atualização cadastral
        //TSession::delValue('dados_pf_atualizacao_cadastral');

        $this->setDatabase('adea');        // defines the database
        $this->setActiveRecord('ViewEncontrista');       // defines the active record

        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'center');
        $col_casal_id    = new TDataGridColumn('casal_id', 'Nº', 'center');
        $col_encontro_id = new TDataGridColumn('encontro_id', 'Encontro', 'left');
        $col_encontro = new TDataGridColumn('encontro', 'Evento', 'left');
        $col_casal = new TDataGridColumn('casal', 'Casal', 'left');
        $col_secretario_s_n = new TDataGridColumn('secretario_s_n', 'Secretário?', 'center');
        $col_circulo = new TDataGridColumn('circulo', 'Círculo', 'left');
        $col_casal_convite = new TDataGridColumn('casal_convite', 'Casal Convite', 'left');

        //parent::addAttribute('conducao_propria');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_casal_id);
        $this->datagrid->addColumn($col_encontro_id);
        $this->datagrid->addColumn($col_encontro);
        $this->datagrid->addColumn($col_casal);
        $this->datagrid->addColumn($col_secretario_s_n);
        $this->datagrid->addColumn($col_circulo);
        $this->datagrid->addColumn($col_casal_convite);

        $col_secretario_s_n->setTransformer(function ($value) {
            if ($value == 1) {
                $div = new TElement('span');
                $div->class = "label label-success";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add('Sim');
                return $div;
            } else {
                $div = new TElement('span');
                $div->class = "label label-danger";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add('Não');
                return $div;
            }
        });

        //$col_tema->enableAutoHide(1000);
        //$col_cantico->enableAutoHide(1000);

        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_casal_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'casal_id']);
        $col_encontro_id->setAction(new TAction([$this, 'onReload']), ['order' => 'encontro_id']);
        $col_encontro->setAction(new TAction([$this, 'onReload']), ['order' => 'encontro']);
        $col_casal->setAction(new TAction([$this, 'onReload']), ['order' => 'casal']);
        $col_secretario_s_n->setAction(new TAction([$this, 'onReload']), ['order' => 'secretario_s_n']);
        $col_circulo->setAction(new TAction([$this, 'onReload']), ['order' => 'circulo']);
        $col_casal_convite->setAction(new TAction([$this, 'onReload']), ['order' => 'casal_convite']);

        $action1 = new TDataGridAction(['EncontristaForm', 'onEdit'],   ['key' => '{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}']);
        $action3 = new TDataGridAction(['AddEncontrista', 'onEdit'],   ['id' => '{id}', 'casal_id' => '{casal_id}', 'register_state' => 'false']);

        $this->datagrid->addAction($action1, 'Editar',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Deletar', 'far:trash-alt red');
        $this->datagrid->addAction($action3, 'Editar',   'far:edit orange');

        // create the datagrid model
        $this->datagrid->createModel();

        // search box
        $input_search = new TEntry('input_search');
        $input_search->placeholder = 'Buscar';
        $input_search->setSize('100%');

        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'id, encontro_id, encontro, casal, secretario_s_n, circulo, casal_convite');

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));

        $panel = new TPanelGroup('Encontristas');
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
        $panel->addHeaderActionLink('Avulso',  new TAction(['AddEncontrista', 'onClear'], ['register_state' => 'false']), 'fa:plus orange');
        $panel->addHeaderActionLink('Novo',  new TAction(['EncontristaForm', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
        $panel->addHeaderWidget($dropdown);

        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);

        // add the table inside the page
        parent::add($vbox);
    }

    /**
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array(__CLASS__, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead

        // shows a dialog to the user
        new TQuestion('Deseja realmente excluir este encontrista?', $action);
    }

    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('adea'); // open a transaction with database

            Encontrista::where('montagem_id', '=', $param['key'])->delete();

            /*
            $buscarelacao = PessoaParentesco::where('pessoa_id', '=', $param['key'])->load();

            if ($buscarelacao) {
                foreach ($buscarelacao as $br) {
                    PessoasRelacao::where('id', '=', $br->relacao_id)->delete();
                }
            }

            PessoaParentesco::where('pessoa_id', '=', $param['key'])->delete();
            PessoaParentesco::where('pessoa_parente_id', '=', $param['key'])->delete();
            */

            $object = new Montagem($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database

            TTransaction::close(); // close the transaction

            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
