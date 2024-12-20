<?php

use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;

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
class PessoaFisicaDataGrid extends TPage
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

        //deleta dados da sessão
        TSession::delValue('dados_iniciais_pf');
        TSession::delValue('dados_parentes_pf');
        TSession::delValue('endereco_pessoa');
        TSession::delValue('dados_relacao');

        TSession::delValue('pessoa_painel');
        TSession::delValue('pessoa_painel_vinculos');

        //atualização cadastral
        //TSession::delValue('dados_pf_atualizacao_cadastral');

        $this->setDatabase('adea');          // defines the database
        $this->setActiveRecord('ViewPessoaFisica');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('id', '=', 'nome'); // filterField, operator, formField
        $this->addFilterField('popular', 'ilike', 'popular'); // filterField, operator, formField
        $this->addFilterField('genero', '=', 'genero'); // filterField, operator, formField
        $this->addFilterField('dt_nascimento', '=', 'dt_nascimento'); // filterField, operator, formField
        $this->addFilterField('endereco', 'ilike', 'endereco'); // filterField, operator, formField

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_PessoaFisicaDataGrid');
        $this->form->setFormTitle('Lista de Pessoas (Filtros)');

        // create the form fields
        $id        = new TEntry('id');

        $nome = new TDBCombo('nome', 'adea', 'ViewPessoaFisica', 'id', '{nome} ({Nascimento})', 'id');
        $nome->enableSearch();

        $popular = new TEntry('popular');

        $endereco = new TEntry('endereco');
        //$dt_nascimento = new TDate('dt_nascimento');

        // add the fields
        $this->form->addFields([new TLabel('Id')],          [$id]);
        $this->form->addFields([new TLabel('Nome')],    [$nome]);
        $this->form->addFields([new TLabel('Nome Popular')],    [$popular]);
        $this->form->addFields([new TLabel('Endereço')],    [$endereco]);
        //$this->form->addFields([new TLabel('Nascimento')],    [$dt_nascimento]);

        $id->setSize('50%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        //$dt_nascimento->setSize('100%');

        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('EncontristaDataGrid_filter_data'));

        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Nova',  new TAction(['DadosIniciaisPF', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        //$this->datagrid->disableDefaultClick();

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'right');
        $col_cpf = new TDataGridColumn('cpf', 'CPF', 'center');
        $col_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $col_popular = new TDataGridColumn('popular', 'Popular', 'left');
        $col_genero = new TDataGridColumn('genero', 'Gênero', 'center');
        $col_dt_nascimento = new TDataGridColumn('dt_nascimento', 'Nascimento', 'center');
        $col_endereco = new TDataGridColumn('endereco', 'Endereço', 'left');
        $col_status_pessoa = new TDataGridColumn('status_pessoa', 'Status', 'center');

        // add the columns to the DataGrid
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

        // creates the datagrid column actions
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

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
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


    /**
     * Clear filters
     */
    function clear()
    {
        $this->clearFilters();
        $this->onReload();
    }
}
