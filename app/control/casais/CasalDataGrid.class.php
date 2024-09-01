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
class CasalDataGrid extends TPage
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
        $this->setActiveRecord('ViewCasal');       // defines the active record

        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";

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

        //$col_nome->enableAutoHide(1000);
        //$col_endereco->enableAutoHide(1000);

        $col_relacao_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'relacao_id']);
        $col_casal->setAction(new TAction([$this, 'onReload']), ['order' => 'casal']);
        $col_dt_inicial->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_inicial']);
        $col_dt_final->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_final']);
        $col_tipo_vinculo->setAction(new TAction([$this, 'onReload']), ['order' => 'tipo_vinculo']);
        $col_status_relacao_id->setAction(new TAction([$this, 'onReload']), ['order' => 'status_relacao_id']);

        $action1 = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => '{relacao_id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{relacao_id}']);

        $this->datagrid->addAction($action1, 'Visualizar',   'fa:search blue');
        $this->datagrid->addAction($action2, 'Deletar', 'far:trash-alt red');

        // create the datagrid model
        $this->datagrid->createModel();

        // search box
        $input_search = new TEntry('input_search');
        $input_search->placeholder = 'Buscar';
        $input_search->setSize('100%');

        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'relacao_id, casal, dt_inicial, dt_final, tipo_vinculo, status_relacao_id');

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));

        $panel = new TPanelGroup('Casais');
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
        $panel->addHeaderActionLink('Novo',  new TAction(['DadosIniciaisPF', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
        $panel->addHeaderWidget($dropdown);

        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
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
        new TQuestion('Deseja realmente excluir esta pessoa?', $action);
    }

    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('adea'); // open a transaction with database

            PessoaFisica::where('pessoa_id', '=', $param['key'])->delete();
            PessoaContato::where('pessoa_id', '=', $param['key'])->delete();

            $buscarelacao = PessoaParentesco::where('pessoa_id', '=', $param['key'])->load();

            if ($buscarelacao) {
                foreach ($buscarelacao as $br) {
                    PessoasRelacao::where('id', '=', $br->relacao_id)->delete();
                }
            }

            PessoaParentesco::where('pessoa_id', '=', $param['key'])->delete();
            PessoaParentesco::where('pessoa_parente_id', '=', $param['key'])->delete();

            $object = new Pessoa($key, FALSE); // instantiates the Active Record
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
