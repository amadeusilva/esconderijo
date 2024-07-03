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
class PessoaFisicaDataGrid extends TPage
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

        //deleta dados da sessão
        TSession::delValue('dados_iniciais_pf');
        TSession::delValue('dados_parentes_pf');
        TSession::delValue('endereco_pessoa');
        TSession::delValue('dados_relacao');
        TSession::delValue('pessoa_painel');
        TSession::delValue('pessoa_painel_vinculos');

        $this->setDatabase('adea');        // defines the database
        $this->setActiveRecord('ViewPessoaFisica');       // defines the active record

        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'right');
        $col_cpf = new TDataGridColumn('cpf', 'CPF', 'center');
        $col_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $col_popular = new TDataGridColumn('popular', 'Popular', 'left');
        $col_genero = new TDataGridColumn('genero', 'Gênero', 'center');
        $col_dt_nascimento = new TDataGridColumn('dt_nascimento', 'Nascimento', 'center');
        $col_endereco = new TDataGridColumn('endereco', 'Endereço', 'left');
        $col_status_pessoa = new TDataGridColumn('status_pessoa', 'Status', 'center');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_cpf);
        $this->datagrid->addColumn($col_nome);
        $this->datagrid->addColumn($col_popular);
        $this->datagrid->addColumn($col_genero);
        $this->datagrid->addColumn($col_dt_nascimento);
        $this->datagrid->addColumn($col_endereco);
        $this->datagrid->addColumn($col_status_pessoa);

        // define the transformer method over date
        $col_dt_nascimento->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $col_genero->setTransformer(function ($value) {
            return $value == 'F' ? 'Feminino' : 'Masculino';
        });

        $col_nome->enableAutoHide(1000);
        $col_endereco->enableAutoHide(1000);

        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_cpf->setAction(new TAction([$this, 'onReload']), ['order' => 'cpf']);
        $col_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $col_popular->setAction(new TAction([$this, 'onReload']), ['order' => 'popular']);
        $col_genero->setAction(new TAction([$this, 'onReload']), ['order' => 'genero']);
        $col_dt_nascimento->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_nascimento']);
        $col_endereco->setAction(new TAction([$this, 'onReload']), ['order' => 'endereco']);
        $col_status_pessoa->setAction(new TAction([$this, 'onReload']), ['order' => 'status_pessoa']);

        $action1 = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => '{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}']);

        $this->datagrid->addAction($action1, 'Visualizar',   'fa:search blue');
        $this->datagrid->addAction($action2, 'Deletar', 'far:trash-alt red');

        // create the datagrid model
        $this->datagrid->createModel();

        // search box
        $input_search = new TEntry('input_search');
        $input_search->placeholder = 'Buscar';
        $input_search->setSize('100%');

        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'id, cpf, nome, popular, genero, dt_nascimento, endereco, status_pessoa');

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));

        $panel = new TPanelGroup('Pessoas Físicas');
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
        $panel->addHeaderActionLink('Nova',  new TAction(['DadosIniciaisPF', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
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
        new TQuestion(AdiantiCoreTranslator::translate('Deseja realmente excluir esta pessoa?'), $action);
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

            $buscarelacao = PessoaParentesco::where('pessoa_id', '=', $param['key'])->first();

            if ($buscarelacao) {
                PessoasRelacao::where('relacao_id', '=', $buscarelacao->id)->delete();
            }

            PessoaParentesco::where('pessoa_id', '=', $param['key'])->delete();

            $buscarelacao2 = PessoaParentesco::where('pessoa_parente_id', '=', $param['key'])->first();

            if ($buscarelacao2) {
                PessoasRelacao::where('relacao_id', '=', $buscarelacao2->id)->delete();
            }

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
