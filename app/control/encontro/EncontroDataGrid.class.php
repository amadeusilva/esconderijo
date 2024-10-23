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
class EncontroDataGrid extends TPage
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
        $this->setActiveRecord('ViewEncontro');       // defines the active record
        $this->setDefaultOrder('id', 'asc');  // defines the default order
        $this->setLimit(1000);

        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";

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

        $action1 = new TDataGridAction(['EncontroPanel', 'onView'],   ['key' => '{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction(['EncontroForm', 'onEdit'],   ['key' => '{id}', 'register_state' => 'false']);
        $actionverpdfdigitalizacaolivrao = new TDataGridAction([$this, 'onViewDigitalizaoLivrao'],   ['key' => '{id}', 'register_state' => 'false']);
        $actionverpdfdigitalizacaolivrao->setDisplayCondition(array($this, 'displayColumn'));
        //$action3 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}']);

        $this->datagrid->addAction($action1, 'Visualizar',   'fa:search blue');
        $this->datagrid->addAction($action2, 'Editar',   'far:edit blue');
        $this->datagrid->addAction($actionverpdfdigitalizacaolivrao, 'Ver Digitalização', 'far:fa-sharp fa-solid fa-file-pdf red');
        //$this->datagrid->addAction($action3, 'Deletar', 'far:trash-alt red');


        // create the datagrid model
        $this->datagrid->createModel();

        // search box
        $input_search = new TEntry('input_search');
        $input_search->placeholder = 'Buscar';
        $input_search->setSize('100%');

        // enable fuse search by column name
        // encontro`(`id`, `num`, `evento_id`, `local_id`, `dt_inicial`, `dt_final`, `tema`, `divisa`, `cantico_id
        $this->datagrid->enableSearch($input_search, 'id, num, evento, local, endereco, tema, divisa, cantico');

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));

        $panel = new TPanelGroup('Encontros');
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
        $panel->addHeaderActionLink('Novo',  new TAction(['EncontroForm', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
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
