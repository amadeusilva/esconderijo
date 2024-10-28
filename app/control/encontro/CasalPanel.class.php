<?php

use Adianti\Widget\Util\TImage;

/**
 * SaleSidePanelView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CasalPanel extends TWindow
{
    protected $form; // form
    protected $historico_circulos;
    protected $historico_equipes;

    // trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct($param)
    {
        parent::__construct();

        // creates the scroll panel
        $scroll = new TScroll;
        $scroll->setSize('100%', '800');

        parent::setSize(0.9, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();

        // with: 500, height: automatic
        parent::setSize(0.6, null); // use 0.6, 0.4 (for relative sizes 60%, 40%)

        //parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_Casal_Panel');
        $this->form->setFormTitle('Dados do Casal');

        $dropdown = new TDropDown('Opções', 'fa:th');
        //$dropdown->addAction(
        $dropdown->addAction('Imprimir', new TAction([$this, 'onPrint'], ['key' => 1, 'static' => '1']), 'far:file-pdf red');
        //$dropdown->addAction( 'Gerar etiqueta', new TAction([$this, 'onGeraEtiqueta'], ['key'=>$param['key'], 'static' => '1']), 'far:envelope purple');
        //$dropdown->addAction('Editar', new TAction(['AddEncontrista', 'onEdit'], ['key' => $param['relacao_id']]), 'far:edit blue');

        $this->form->addHeaderWidget($dropdown);

        $this->form->addHeaderActionLink('Fechar', new TAction([$this, 'onClose']), 'fa:times red');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
        $scroll->add($this->form);
        $container->add($scroll);

        parent::add($container);
    }

    /**
     * Load content
     */
    public function onView($param)
    {

        try {
            TTransaction::open('adea');

            //ENCONTROs
            $dados_encontristas = ViewEncontrista::where('casal_id', '=', $param['relacao_id'])->first();
            //$total_encontreiros = Montagem::where('tipo_id', '=', 2)->where('encontro_id', '=', $encontro->id)->countDistinctBy('casal_id');

            $row = $this->form->addFields([new TLabel('<b>Cod.:</b>', ''), $dados_encontristas->casal_id], [new TLabel('<b>Evento:</b>', ''), $dados_encontristas->encontro], [new TLabel('<b>Secretário?</b>', ''), $dados_encontristas->Secretario2]);
            $row->layout = ['col-sm-3', 'col-sm-5', 'col-sm-4'];

            if (!$dados_encontristas->casal_convite) {
                $dados_encontristas->casal_convite = 'NÃO INFORMADO';
            }

            $row = $this->form->addFields([new TLabel('<b>Casal:</b>', ''), $dados_encontristas->casal], [new TLabel('<b>Convite:</b>', ''), $dados_encontristas->casal_convite]);
            $row->layout = ['col-sm-6', 'col-sm-6'];

            $action_ele = new TAction(['PessoaPanel', 'onView']);
            $action_ele->setParameter('key', $dados_encontristas->DadosCasal->Ele->id);

            $nome_ele = new TActionLink($dados_encontristas->DadosCasal->Ele->nome, $action_ele, 'blue', 12, 'bu'); //biu

            $action_ela = new TAction(['PessoaPanel', 'onView']);
            $action_ela->setParameter('key', $dados_encontristas->DadosCasal->Ela->id);

            $nome_ela = new TActionLink($dados_encontristas->DadosCasal->Ela->nome, $action_ela, 'blue', 12, 'bu'); //biu

            $row = $this->form->addFields([new TLabel('<b>Ele:</b>', ''), $nome_ele], [new TLabel('<b>Nasc.:</b>', ''), TDate::date2br($dados_encontristas->DadosCasal->Ele->dt_nascimento)]);
            $row->layout = ['col-sm-8', 'col-sm-4'];

            $row = $this->form->addFields([new TLabel('<b>Ela:</b>', ''), $nome_ela], [new TLabel('<b>Nasc.:</b>', ''), TDate::date2br($dados_encontristas->DadosCasal->Ela->dt_nascimento)]);
            $row->layout = ['col-sm-8', 'col-sm-4'];

            //[new TLabel('<b>Casamento</b>', ''), TDate::date2br($dados_encontristas->DadosCasal->dt_inicial)]

            $date = new DateTime($dados_encontristas->DadosCasal->dt_inicial);
            $interval = $date->diff(new DateTime(date('Y-m-d')));

            $row = $this->form->addFields([new TLabel('<b>Casamento:</b>', ''), TDate::date2br($dados_encontristas->DadosCasal->dt_inicial)], ['<b>Há:</b>', $interval->format('%Y anos')], [new TLabel('<b>Círculo:</b>', ''), $dados_encontristas->CirculoCor]);
            $row->layout = ['col-sm-5', 'col-sm-3', 'col-sm-4'];

            //resumos
            $notebook_resumos = new TNotebook;
            $label = new TLabel('<br>Resumo de Participações', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
            $row = $this->form->addFields([$label]);
            $row->layout = ['col-sm-12'];
            $this->form->addContent([$notebook_resumos]);

            //históricos
            $notebook_historicos = new TNotebook;
            $label = new TLabel('<br>Históricos', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
            $row = $this->form->addFields([$label]);
            $row->layout = ['col-sm-12'];
            $this->form->addContent([$notebook_historicos]);

            //RESUMO
            $equipes = Equipe::where('id', '>=', 1)->load();

            if ($equipes) {

                // creates a table
                $table = new TTable;
                $table->width = '100%';
                $table->border = '1';

                // creates a label with the title
                $title = new TLabel('<b>QUADRO DE RESUMO POR EQUIPE</b>');
                $title->style = 'color: #fff;';

                // adds a row to the table
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #343a40;';
                $title = $row->addCell($title);
                $title->colspan = 4;

                $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                // adds a row for the code field
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                $row->addCell('Ordem');
                $row->addCell('Equipe');
                $row->addCell('Total (Coordenação)');
                $row->addCell('Total Geral');

                $total_coord = 0;
                $total_geral = 0;

                foreach ($equipes as $equipe) {

                    $row = $table->addRow();

                    $ordem_label = new TLabel('ordem');
                    $ordem_label->setFontStyle('b');
                    $ordem_label->setValue($equipe->id);

                    $quantidade_equipe = ViewEncontreiro::where('casal_id', '=', $param['relacao_id'])->where('equipe_id', '=', $equipe->id)->count();

                    $action = new TAction([$this, 'onViewDetalhes']);
                    $action->setParameter('equipe', $equipe->equipe);
                    $action->setParameter('contagem_equipe', $quantidade_equipe);
                    $action->setParameter('casal_id', $param['relacao_id']);
                    $action->setParameter('equipe_id', $equipe->id);
                    $action->setParameter('tipo', 1);

                    $equipe_link = new TActionLink('<b>' . $equipe->equipe . '</b>', $action, 'black', 12, 'bu'); //biu

                    $row->addCell($ordem_label);

                    $quantidade_equipe_coord = ViewEncontreiro::where('casal_id', '=', $param['relacao_id'])->where('equipe_id', '=', $equipe->id)->where('funcao_id', '=', 1)->count();

                    if ($quantidade_equipe > 0) {
                        $row->addCell($equipe_link);
                    } else {
                        $row->addCell($equipe->equipe);
                    }

                    if ($quantidade_equipe_coord > 0) {

                        $coord_label = new TLabel('coord');
                        $coord_label->setFontStyle('b');
                        $coord_label->setValue($quantidade_equipe_coord);
                        $row->addCell($coord_label);

                        $total_coord += $quantidade_equipe_coord;
                    } else {

                        $row->addCell(0);
                    }

                    if ($quantidade_equipe > 0) {
                        $geral_label = new TLabel('geral');
                        $geral_label->setFontStyle('b');
                        $geral_label->setValue($quantidade_equipe);
                        $row->addCell($geral_label);

                        $total_geral += $quantidade_equipe;
                    } else {
                        $row->addCell(0);
                    }
                }

                // creates a label with the title
                $rodape = new TLabel('<b>TOTAL</b>');
                $rodape->style = 'color: #fff;';

                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #343a40;';
                $rodape = $row->addCell($rodape);
                $rodape->colspan = 2;
                $total_coord = new TLabel('<b>' . $total_coord . '</b>');
                $total_coord->style = 'color: #fff;';
                $total_coord = $row->addCell($total_coord);
                $label_total_geral = new TLabel('<b>' . $total_geral . '</b>');
                $label_total_geral->style = 'color: #fff;';
                $row->addCell($label_total_geral);

                $notebook_resumos->appendPage('Equipes <b>(' . $total_geral . ')</b>', $table);
            }

            //PALESTRAS
            $palestras = ListaItens::where('lista_id', '=', 19)->load();

            if ($palestras) {

                // creates a table
                $table = new TTable;
                $table->width = '100%';
                $table->border = '1';

                // creates a label with the title
                $title = new TLabel('<b>QUADRO DE RESUMO POR PALESTRAS</b>');
                $title->style = 'color: #fff;';

                // adds a row to the table
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #343a40;';
                $title = $row->addCell($title);
                $title->colspan = 4;

                $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                // adds a row for the code field
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                $row->addCell('Ordem');
                $row->addCell('Palestra');
                $row->addCell('Total (Coordenação)');
                $row->addCell('Total Geral');

                $total_coord = 0;
                $total_geral = 0;
                $ordem = 0;

                foreach ($palestras as $palestra) {

                    $row = $table->addRow();

                    $ordem_label = new TLabel('ordem');
                    $ordem_label->setFontStyle('b');
                    $ordem += 1;
                    $ordem_label->setValue($ordem);

                    $quantidade_palestra = ViewPalestrante::where('casal_id', '=', $param['relacao_id'])->where('palestra_id', '=', $palestra->id)->count();

                    $action = new TAction([$this, 'onViewDetalhes']);
                    $action->setParameter('equipe', $palestra->item);
                    $action->setParameter('contagem_equipe', $quantidade_palestra);
                    $action->setParameter('casal_id', $param['relacao_id']);
                    $action->setParameter('equipe_id', $palestra->id);
                    $action->setParameter('tipo', 2);

                    $palestra_link = new TActionLink('<b>' . $palestra->item . '</b>', $action, 'black', 12, 'bu'); //biu

                    $row->addCell($ordem_label);


                    $quantidade_palestra_coord = ViewPalestrante::where('casal_id', '=', $param['relacao_id'])->where('palestra_id', '=', $palestra->id)->where('funcao_id', '=', 1)->count();

                    if ($quantidade_palestra > 0) {
                        $row->addCell($palestra_link);
                    } else {
                        $row->addCell($palestra->item);
                    }

                    if ($quantidade_palestra_coord > 0) {

                        $coord_label = new TLabel('coord');
                        $coord_label->setFontStyle('b');
                        $coord_label->setValue($quantidade_palestra_coord);
                        $row->addCell($coord_label);

                        $total_coord += $quantidade_palestra_coord;
                    } else {

                        $row->addCell(0);
                    }

                    if ($quantidade_palestra > 0) {
                        $geral_label = new TLabel('geral');
                        $geral_label->setFontStyle('b');
                        $geral_label->setValue($quantidade_palestra);
                        $row->addCell($geral_label);

                        $total_geral += $quantidade_palestra;
                    } else {
                        $row->addCell(0);
                    }
                }

                // creates a label with the title
                $rodape = new TLabel('<b>TOTAL</b>');
                $rodape->style = 'color: #fff;';

                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #343a40;';
                $rodape = $row->addCell($rodape);
                $rodape->colspan = 2;
                $total_coord = new TLabel('<b>' . $total_coord . '</b>');
                $total_coord->style = 'color: #fff;';
                $total_coord = $row->addCell($total_coord);
                $label_total_geral = new TLabel('<b>' . $total_geral . '</b>');
                $label_total_geral->style = 'color: #fff;';
                $row->addCell($label_total_geral);

                $notebook_resumos->appendPage('Palestras <b>(' . $total_geral . ')</b>', $table);
            }

            //PALESTRAS
            $edgs = ListaItens::where('lista_id', '=', 20)->load();

            if ($edgs) {

                // creates a table
                $table = new TTable;
                $table->width = '100%';
                $table->border = '1';

                // creates a label with the title
                $title = new TLabel('<b>QUADRO DE RESUMO POR PASTAS</b>');
                $title->style = 'color: #fff;';

                // adds a row to the table
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #343a40;';
                $title = $row->addCell($title);
                $title->colspan = 4;

                $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                // adds a row for the code field
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                $row->addCell('Ordem');
                $row->addCell('Pasta');
                $row->addCell('Total (Coordenação)');
                $row->addCell('Total Geral');

                $total_coord = 0;
                $total_geral = 0;
                $ordem = 0;

                foreach ($edgs as $edg) {

                    $row = $table->addRow();

                    $ordem_label = new TLabel('ordem');
                    $ordem_label->setFontStyle('b');
                    $ordem += 1;
                    $ordem_label->setValue($ordem);

                    $quantidade_edg = ViewEdg::where('casal_id', '=', $param['relacao_id'])->where('pasta_id', '=', $edg->id)->count();

                    $action = new TAction([$this, 'onViewDetalhes']);
                    $action->setParameter('equipe', $edg->item);
                    $action->setParameter('contagem_equipe', $quantidade_edg);
                    $action->setParameter('casal_id', $param['relacao_id']);
                    $action->setParameter('equipe_id', $edg->id);
                    $action->setParameter('tipo', 3);

                    $edg_link = new TActionLink('<b>' . $edg->item . '</b>', $action, 'black', 12, 'bu'); //biu

                    $row->addCell($ordem_label);


                    $quantidade_edg_coord = ViewEdg::where('casal_id', '=', $param['relacao_id'])->where('pasta_id', '=', $edg->id)->where('funcao_id', '=', 1)->count();

                    if ($quantidade_edg > 0) {
                        $row->addCell($edg_link);
                    } else {
                        $row->addCell($edg->item);
                    }

                    if ($quantidade_edg_coord > 0) {

                        $coord_label = new TLabel('coord');
                        $coord_label->setFontStyle('b');
                        $coord_label->setValue($quantidade_edg_coord);
                        $row->addCell($coord_label);

                        $total_coord += $quantidade_edg_coord;
                    } else {
                        $row->addCell(0);
                    }

                    if ($quantidade_edg > 0) {
                        $geral_label = new TLabel('geral');
                        $geral_label->setFontStyle('b');
                        $geral_label->setValue($quantidade_edg);
                        $row->addCell($geral_label);

                        $total_geral += $quantidade_edg;
                    } else {
                        $row->addCell(0);
                    }
                }

                // creates a label with the title
                $rodape = new TLabel('<b>TOTAL</b>');
                $rodape->style = 'color: #fff;';

                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #343a40;';
                $rodape = $row->addCell($rodape);
                $rodape->colspan = 2;
                $total_coord = new TLabel('<b>' . $total_coord . '</b>');
                $total_coord->style = 'color: #fff;';
                $total_coord = $row->addCell($total_coord);
                $label_total_geral = new TLabel('<b>' . $total_geral . '</b>');
                $label_total_geral->style = 'color: #fff;';
                $row->addCell($label_total_geral);

                $notebook_resumos->appendPage('EDG <b>(' . $total_geral . ')</b>', $table);
            }

            //historico de equipes
            $this->historico_equipes = new BootstrapDatagridWrapper(new TDataGrid);
            $this->historico_equipes->style = 'width:100%';
            $this->historico_equipes->disableDefaultClick();

            //$column_id = $this->historico_equipes->addColumn(new TDataGridColumn('id', 'Cód.', 'center'));
            $column_encontro = $this->historico_equipes->addColumn(new TDataGridColumn('encontro', 'Encontro', 'center'));
            //$column_circulo = $this->historico_equipes->addColumn(new TDataGridColumn('CirculoCor', 'Círculo', 'center'));]
            $column_ano = $this->historico_equipes->addColumn(new TDataGridColumn('AnoEcc', 'Ano', 'center'));
            $column_coordenar_s_n = $this->historico_equipes->addColumn(new TDataGridColumn('Coordenar', 'Coordenar?', 'center'));
            $column_funcao_id = $this->historico_equipes->addColumn(new TDataGridColumn('Funcao', 'Função', 'center'));
            $column_equipe = $this->historico_equipes->addColumn(new TDataGridColumn('equipe', 'Equipe', 'left'));

            // define row actions
            //$action1 = new TDataGridAction(['CirculoHistoricoForm', 'onEdit'],   ['key' => '{id}']);
            //$action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}', 'casal_id' => $param['relacao_id']]);

            //$this->historico_circulos->addAction($action1, 'Editar',   'far:edit blue');
            //$this->historico_circulos->addAction($action2, 'Deletar', 'far:trash-alt red');

            $this->historico_equipes->createModel();

            $historicoequipes = ViewEncontreiro::where('casal_id', '=', $param['relacao_id'])->orderBy('encontro', 'asc')->load();
            $this->historico_equipes->addItems($historicoequipes);
            //TSession::setValue('pessoa_painel_vinculos', $circulohistorico);

            $panel_historicoequipes = new TPanelGroup('<b>Histórico de Equipes</b>', '#f5f5f5');

            if ($historicoequipes) {
                $panel_historicoequipes->add($this->historico_equipes)->style = 'overflow-x:auto';
            } else {
                $label_historicoequipes = new TLabel('Não há histórico registrado para este casal.', '#dd5a43', 12, 'b');
                $panel_historicoequipes->add($label_historicoequipes);
            }

            //$panel_circulohistorico->addHeaderActionLink('<b>Adicionar</b>',  new TAction(['CirculoHistoricoForm', 'onEdit'], ['casal_id' => $param['relacao_id'], 'user_sessao_id' => TSession::getValue('userid'), 'register_state' => 'false']), 'fa:plus green');
            //$this->form->addContent([$panel_circulohistorico]);

            $notebook_historicos->appendPage('<b>Equipes</b>', $panel_historicoequipes);

            //historico de circulos
            $this->historico_circulos = new BootstrapDatagridWrapper(new TDataGrid);
            $this->historico_circulos->style = 'width:100%';
            //$this->historico_circulos->disableDefaultClick();

            $column_id = $this->historico_circulos->addColumn(new TDataGridColumn('id', 'Cód.', 'center'));
            //$column_user_sessao_id = $this->historico_circulos->addColumn(new TDataGridColumn('user_sessao_id', 'Cadastrado por', 'left'));
            //$column_casal_id = $this->historico_circulos->addColumn(new TDataGridColumn('casal_id', 'Casal', 'left'));
            $column_circulo_id = $this->historico_circulos->addColumn(new TDataGridColumn('CirculoCor', 'Círculo', 'center'));
            $column_motivo_id = $this->historico_circulos->addColumn(new TDataGridColumn('CirculoMotivo->item', 'Motivo', 'left'));
            //$column_obs_motivo = $this->historico_circulos->addColumn(new TDataGridColumn('obs_motivo', 'obs_motivo', 'left'));
            $column_dt_historico = $this->historico_circulos->addColumn(new TDataGridColumn('dt_historico', 'Data', 'left'));

            // define the transformer method over date
            $column_dt_historico->setTransformer(function ($value, $object, $row) {
                $date = new DateTime($value);
                return $date->format('d/m/Y');
            });

            // define row actions
            $action1 = new TDataGridAction(['CirculoHistoricoForm', 'onEdit'],   ['key' => '{id}']);
            $action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}', 'casal_id' => $param['relacao_id']]);

            $this->historico_circulos->addAction($action1, 'Editar',   'far:edit blue');
            $this->historico_circulos->addAction($action2, 'Deletar', 'far:trash-alt red');

            $this->historico_circulos->createModel();

            $circulohistorico = CirculoHistorico::where('casal_id', '=', $param['relacao_id'])->orderBy('id', 'asc')->load();
            $this->historico_circulos->addItems($circulohistorico);
            //TSession::setValue('pessoa_painel_vinculos', $circulohistorico);

            $panel_circulohistorico = new TPanelGroup('<b>Histórico de Círculos</b>', '#f5f5f5');

            if ($circulohistorico) {
                $panel_circulohistorico->add($this->historico_circulos)->style = 'overflow-x:auto';
            } else {
                $label_circulohistorico = new TLabel('Não há histórico registrado para este casal.', '#dd5a43', 12, 'b');
                $panel_circulohistorico->add($label_circulohistorico);
            }

            $panel_circulohistorico->addHeaderActionLink('<b>Adicionar</b>',  new TAction(['CirculoHistoricoForm', 'onEdit'], ['casal_id' => $param['relacao_id'], 'user_sessao_id' => TSession::getValue('userid'), 'register_state' => 'false']), 'fa:plus green');
            //$this->form->addContent([$panel_circulohistorico]);

            $notebook_historicos->appendPage('<b>Círculos</b>', $panel_circulohistorico);


            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
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
        new TQuestion('Deseja realmente deletar este registro ?', $action);
    }

    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('adea'); // open a transaction with database
            $object = new CirculoHistorico($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction

            $pos_action = new TDataGridAction([__CLASS__, 'onView'],   ['relacao_id' => $param['casal_id'], 'register_state' => 'false']);

            new TMessage('info', 'Registro Deletado!', $pos_action); // success message
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public static function onViewDetalhes($param)
    {

        $win = TWindow::create('Detalhes da Montagem', 0.5, 0.5);

        try {
            TTransaction::open('adea');

            if ($param['tipo'] == 1) {
                $encontreiro_equipe = ViewEncontreiro::where('casal_id', '=', $param['casal_id'])->where('equipe_id', '=', $param['equipe_id'])->orderBy('encontro, funcao_id', 'asc')->load();
                $nome_desc = 'Equipe';
            } else if ($param['tipo'] == 2) {
                $encontreiro_equipe = ViewPalestrante::where('casal_id', '=', $param['casal_id'])->where('palestra_id', '=', $param['equipe_id'])->orderBy('encontro, funcao_id', 'asc')->load();
                $nome_desc = 'Palestra';
            } else if ($param['tipo'] == 3) {
                $encontreiro_equipe = ViewEdg::where('casal_id', '=', $param['casal_id'])->where('pasta_id', '=', $param['equipe_id'])->orderBy('encontro, funcao_id', 'asc')->load();
                $nome_desc = 'Pasta';
            }

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
            $title->colspan = 6;

            $ordem = 0;

            $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

            // adds a row for the code field
            $row = $table->addRow();
            $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
            $row->addCell('Ordem');
            $row->addCell('Função');
            $row->addCell('Encontro');
            $row->addCell('Coordenar?');
            $row->addCell('Camisa?');
            $row->addCell('Círculo');

            $nome_casal = '';

            foreach ($encontreiro_equipe as $enc_equip) {
                $row = $table->addRow();

                $nome_casal = $enc_equip->casal;
                $ordem += 1;

                $ordem_label = new TLabel($ordem);
                $ordem_label->setFontStyle('b');
                $ordem_label->setValue($ordem);

                $row->addCell($ordem_label);
                $row->addCell($enc_equip->Funcao);
                $row->addCell($enc_equip->encontro);
                $row->addCell($enc_equip->Coordenar);
                $row->addCell($enc_equip->CamisaEncontroBr);
                $row->addCell($enc_equip->CirculoCor);
            }

            TTransaction::close();

            $win->add('<br>');

            $win->add("Casal: <b>{$nome_casal}</b><br>
            {$nome_desc}: <b>{$param['equipe']}</b><br>
            Total de Participações: <b>{$param['contagem_equipe']}</b><br>
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

    public function onPrint($param)
    {
        try {
            $this->onView($param);

            // string with HTML contents
            $html = clone $this->form;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();

            $options = new \Dompdf\Options();
            $options->setChroot(getcwd());

            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $file = 'app/output/sale-export.pdf';

            // write and open file
            file_put_contents($file, $dompdf->output());

            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file . '?rndval=' . uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $object->add('O navegador não suporta a exibição deste conteúdo, <a style="color:#007bff;" target=_newwindow href="' . $object->data . '"> clique aqui para baixar</a>...');

            $window->add($object);
            $window->show();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        //TScript::create("Template.closeRightPanel()");
        parent::closeWindow();
    }
}
