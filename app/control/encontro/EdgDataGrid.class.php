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
class EdgDataGrid extends TPage
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
        $this->setActiveRecord('ViewEdg');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('encontro_id', '=', 'encontro_id'); // filterField, operator, formField
        $this->addFilterField('circulo_id', '=', 'circulo_id'); // filterField, operator, formField
        $this->addFilterField('pasta_id', '=', 'pasta_id'); // filterField, operator, formField
        $this->addFilterField('funcao_id', '=', 'funcao_id');
        $this->addFilterField('casal_id', '=', 'casal_id'); // filterField, operator, formField

        $this->addFilterField('casal_id', '=', 'ele_id'); // filterField, operator, formField
        $this->addFilterField('casal_id', '=', 'ela_id'); // filterField, operator, formField

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_EdgDataGrid');
        $this->form->setFormTitle('Lista de Palestrantes (Filtros)');

        // create the form fields
        $id        = new TEntry('id');

        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id');
        $encontro_id->enableSearch();

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterCirculo);
        $circulo_id->enableSearch();

        $filterPasta = new TCriteria;
        $filterPasta->add(new TFilter('lista_id', '=', '20'));
        $pasta_id = new TDBCombo('pasta_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterPasta);
        $pasta_id->enableSearch();

        $funcao_id = new TCombo('funcao_id');
        $funcao_id->enableSearch();
        $funcao_id->setSize('100%');
        $funcao_id->addItems([1 => 'COORDENADOR', 2 => 'ADJUNTO']);

        $casal_id = new TDBCombo('casal_id', 'adea', 'ViewCasal', 'relacao_id', '{casal} ({Casamento})', 'relacao_id');
        $casal_id->enableSearch();

        $ele_id = new TDBCombo('ele_id', 'adea', 'ViewCasal', 'relacao_id', '{Ele->nome} ({Ele->Nascimento})', 'ele_id');
        $ele_id->enableSearch();

        $ela_id = new TDBCombo('ela_id', 'adea', 'ViewCasal', 'relacao_id', '{Ela->nome} ({Ela->Nascimento})', 'ela_id');
        $ela_id->enableSearch();

        // add the fields
        $this->form->addFields([new TLabel('Id')],          [$id]);
        $this->form->addFields([new TLabel('Encontro')],    [$encontro_id]);
        $this->form->addFields([new TLabel('Círculo')],     [$circulo_id]);
        $this->form->addFields([new TLabel('Equipe')],      [$pasta_id]);
        $this->form->addFields([new TLabel('Função')],      [$funcao_id]);
        $this->form->addFields([new TLabel('Casal')],       [$casal_id]);
        $this->form->addFields([new TLabel('Ele')],         [$ele_id]);
        $this->form->addFields([new TLabel('Ela')],         [$ela_id]);

        $id->setSize('50%');
        $casal_id->setSize('100%');
        $ele_id->setSize('100%');
        $ela_id->setSize('100%');

        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('EdgDataGrid_filter_data'));

        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Novo',  new TAction(['EncontreiroForm', 'onEdit'], ['tipo_enc_id' => 3, 'register_state' => 'false']), 'fa:plus green');
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        //$this->datagrid->disableDefaultClick();

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'center');
        $col_casal_id    = new TDataGridColumn('casal_id', 'Nº', 'center');
        $col_encontro_id = new TDataGridColumn('encontro_id', 'Encontro', 'center');
        $col_encontro = new TDataGridColumn('encontro', 'Evento', 'left');
        $col_casal = new TDataGridColumn('casal', 'Casal', 'left');
        $col_circulo = new TDataGridColumn('circulo', 'Círculo', 'left');
        //$col_camisa_encontro_br = new TDataGridColumn('camisa_encontro_br', 'Camisa Branca', 'center');
        //$col_camisa_encontro_cor = new TDataGridColumn('camisa_encontro_cor', 'Camisa Círculo', 'center');
        //$col_disponibilidade_nt = new TDataGridColumn('disponibilidade_nt', 'Disp. Noite?', 'center');
        //$col_coordenar_s_n = new TDataGridColumn('coordenar_s_n', 'Coordenar?', 'center');
        $col_funcao_id = new TDataGridColumn('funcao_id', 'Função', 'center');
        $col_equipe = new TDataGridColumn('pasta', 'Pasta', 'left');


        $col_casal_id->setTransformer(function ($value) {
            if ($value) {
                //$icon  = "<i class='far fa-envelope' aria-hidden='true'></i>"; //{$icon} 
                return "<a generator='adianti' href='index.php?class=CasalPanel&method=onView&relacao_id=$value'>$value</a>";
            }
            return $value;
        });

        $col_encontro_id->setTransformer(function ($value) {
            if ($value) {
                //$icon  = "<i class='far fa-envelope' aria-hidden='true'></i>"; //{$icon} 
                return "<a generator='adianti' href='index.php?class=EncontroPanel&method=onView&key=$value'>$value</a>";
            }
            return $value;
        });

        $col_casal->setTransformer(function ($value, $objeto) {

            if ($objeto) {
                //$icon  = "<i class='far fa-envelope' aria-hidden='true'></i>"; //{$icon} 
                return "<a generator='adianti' href='index.php?class=CasalPanel&method=onView&relacao_id=$objeto->casal_id'>$value</a>";
            }
            return $value;
        });

        $col_equipe->setTransformer(function ($value, $objeto) {

            if ($objeto) {
                //$icon  = "<i class='far fa-envelope' aria-hidden='true'></i>"; //{$icon} 
                return "<a generator='adianti' href='index.php?class=EdgDataGrid&method=onViewDetalhesPasta&tipo=3&encontro_id=$objeto->encontro_id&equipe_id=$objeto->pasta_id&encontro=$objeto->encontro&equipe=$objeto->pasta&casal_id=$objeto->casal_id'>$value</a>";
            }
            return $value;
        });

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_casal_id);
        $this->datagrid->addColumn($col_encontro_id);
        $this->datagrid->addColumn($col_encontro);
        $this->datagrid->addColumn($col_casal);
        $this->datagrid->addColumn($col_circulo);
        //$this->datagrid->addColumn($col_camisa_encontro_br);
        //$this->datagrid->addColumn($col_camisa_encontro_cor);
        //$this->datagrid->addColumn($col_disponibilidade_nt);
        //$this->datagrid->addColumn($col_coordenar_s_n);
        $this->datagrid->addColumn($col_funcao_id);
        $this->datagrid->addColumn($col_equipe);

        $col_funcao_id->setTransformer(function ($value) {
            if ($value == 1) {
                $div = new TElement('span');
                $div->class = "label label-success";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add('Palestrante');
                return $div;
            } else {
                $div = new TElement('span');
                $div->class = "label label-info";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add('Apoio');
                return $div;
            }
        });

        //$col_camisa_encontro_br->enableAutoHide(1000);

        // creates the datagrid column actions
        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_casal_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'casal_id']);
        $col_encontro_id->setAction(new TAction([$this, 'onReload']), ['order' => 'encontro_id']);
        $col_encontro->setAction(new TAction([$this, 'onReload']), ['order' => 'encontro']);
        $col_casal->setAction(new TAction([$this, 'onReload']), ['order' => 'casal']);
        $col_circulo->setAction(new TAction([$this, 'onReload']), ['order' => 'circulo']);
        $col_funcao_id->setAction(new TAction([$this, 'onReload']), ['order' => 'funcao_id']);
        $col_equipe->setAction(new TAction([$this, 'onReload']), ['order' => 'pasta']);

        $action1 = new TDataGridAction(['EncontreiroForm', 'onEdit'],   ['key' => '{id}', 'tipo_enc_id' => 3, 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}', 'encontro_id' => '{encontro_id}', 'casal_id' => '{casal_id}', 'palestra_id' => '{equipe_id}']);

        $this->datagrid->addAction($action1, 'Editar',   'far:edit blue');
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

    public static function onViewDetalhesPasta($param)
    {
        try {
            TTransaction::open('adea');

            if ($param['tipo'] == 1) {
                $encontreiro_equipe = ViewEncontreiro::where('encontro_id', '=', $param['encontro_id'])->where('equipe_id', '=', $param['equipe_id'])->orderBy('funcao_id, casal', 'asc')->load();
                $nome_desc = 'Equipe';
            } else if ($param['tipo'] == 2) {
                $encontreiro_equipe = ViewPalestrante::where('palestra_id', '=', $param['equipe_id'])->orderBy('encontro_id, casal', 'desc')->load();
                $nome_desc = 'Palestra';
            } else if ($param['tipo'] == 3) {
                $encontreiro_equipe = ViewEdg::where('pasta_id', '=', $param['equipe_id'])->orderBy('encontro_id, casal', 'desc')->load();
                $nome_desc = 'Pasta';
            }

            $win = TWindow::create('Detalhes da Pasta', 0.6, 0.8);

            // creates a table
            $table = new TTable;
            $table->width = '100%';
            $table->border = '1';

            // creates a label with the title
            $title = new TLabel('<b>' . $param['equipe'] . '</b>');

            // adds a row to the table
            $row = $table->addRow();
            $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #6c757d;';
            $title = $row->addCell($title);
            $title->colspan = 5;

            $ordem = 0;

            $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

            // adds a row for the code field
            $row = $table->addRow();
            $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
            $row->addCell('Ordem');
            $row->addCell('Encontro');
            $row->addCell('Casal');
            $row->addCell('Casamento');
            $row->addCell('Círculo');

            $nome_casal = '';

            foreach ($encontreiro_equipe as $enc_equip) {
                $row = $table->addRow();

                $ordem += 1;

                if ($param['casal_id'] == $enc_equip->casal_id) {
                    $nome_casal = $enc_equip->casal;
                    $row->style = 'font-weight: bold; background-color: #eee8aa;';
                }

                $ordem_label = new TLabel($ordem);
                $ordem_label->setFontStyle('b');
                $ordem_label->setValue($ordem);

                $row->addCell($ordem_label);
                $row->addCell($enc_equip->encontro);
                $row->addCell($enc_equip->casal);
                $row->addCell($enc_equip->DadosCasal->Casamento);


                $row->addCell($enc_equip->CirculoCor);
            }

            TTransaction::close();

            $win->add('<br>');

            $win->add("Casal: <b>{$nome_casal}</b><br>
            {$nome_desc}: <b>{$param['equipe']}</b><br>
            Encontro: <b>{$param['encontro']}</b><br>
            ");

            $win->add('<br>');

            $win->add($table);
            $win->show();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }

        //$notebook_encontreiro->appendPage($encontreiro->equipe . ' <b>(' . $encontreiro->contagem . ')</b>', $table);


        //$win->add("<br> &nbsp; You have clicked at <b>{$name}</b>");

    }

    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead

        // shows a dialog to the user
        new TQuestion('Deseja realmente deletar este registro ?', $action);
    }

    /**
     * method Delete()
     * Delete a record
     */
    public static function Delete($param)
    {
        try {
            // get the parameter $key
            $key = $param['id'];

            // open a transaction with database 'samples'
            TTransaction::open('adea');

            // instantiates object Category
            $montagem = new Montagem($key);

            if ($montagem) {

                $equipes = $montagem->Encontreiro->EncontreiroEquipe;
                $quantidade = $montagem->Encontreiro->ContagemEncontreiroEquipe;

                if ($quantidade == 1) {
                    // deletes the object from the database
                    $montagem->delete();
                } else {
                    EncontreiroEquipe::where('encontreiro_id', '=', $montagem->Encontreiro->id)->where('equipe_id', '=', $param['equipe_id'])->delete();
                }
            }

            // close the transaction
            TTransaction::close();

            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', 'Registro Deletado', $pos_action);
        } catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
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
