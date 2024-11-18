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
class EncontroPanel extends TPage
{
    protected $form; // form

    protected $form_encontristas; // form

    // trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct($param)
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_Encontro_Panel');
        $this->form->setFormTitle('Encontro');

        $dropdown = new TDropDown('Opções', 'fa:th');
        //$dropdown->addAction(
        $dropdown->addAction('Imprimir', new TAction([$this, 'onPrint'], ['key' => $param['key'], 'static' => '1']), 'far:file-pdf red');
        //$dropdown->addAction( 'Gerar etiqueta', new TAction([$this, 'onGeraEtiqueta'], ['key'=>$param['key'], 'static' => '1']), 'far:envelope purple');
        //$dropdown->addAction('Editar', new TAction([$this, 'onEdit'], ['key' => $param['key']]), 'far:edit blue');

        //$this->form->addHeaderWidget($dropdown);

        $this->form->addHeaderActionLink('Fechar', new TAction([$this, 'onClose']), 'fa:times red');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
        $container->add($this->form);

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
            $encontro = new ViewEncontro($param['key']);
            $total_encontristas = Montagem::where('tipo_id', '=', 1)->where('encontro_id', '=', $encontro->id)->countDistinctBy('casal_id');
            $total_encontreiros = Montagem::where('tipo_id', '=', 2)->where('encontro_id', '=', $encontro->id)->countDistinctBy('casal_id');
            $total = $total_encontristas + $total_encontreiros;

            //$this->form_encontro = new BootstrapFormBuilder('form_Encontro');

            $row = $this->form->addFields([new TLabel('<b>Cod.:</b>', ''), $encontro->id], [new TLabel('<b>Evento:</b>', ''), $encontro->sigla]);
            $row->layout = ['col-sm-6', 'col-sm-6'];

            $row = $this->form->addFields([new TLabel('<b>Encontristas:</b>', ''), $total_encontristas], [new TLabel('<b>Encontreiros:</b>', ''), $total_encontreiros], [new TLabel('<b>Total:</b>', ''), $total]);
            $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

            $row = $this->form->addFields([new TLabel('<b>Data Início:</b>', ''), TDate::date2br($encontro->dt_inicial)], [new TLabel('<b>Data Fim:</b>', ''), TDate::date2br($encontro->dt_final)], [new TLabel('<b>Cântico:</b>', ''), $encontro->cantico]);
            $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-6'];

            $row = $this->form->addFields([new TLabel('<b>Local:</b>', ''), $encontro->local], [new TLabel('<b>Endereço:</b>', ''), $encontro->endereco]);
            $row->layout = ['col-sm-6', 'col-sm-6'];

            $row = $this->form->addFields([new TLabel('<b>Tema:</b>', ''), $encontro->tema]);
            $row->layout = ['col-sm-12'];

            $row = $this->form->addFields([new TLabel('<b>Divisa:</b>', ''), $encontro->divisa]);
            $row->layout = ['col-sm-12'];

            $label = new TLabel('<br>Listas', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';

            $row = $this->form->addFields([$label]);
            $row->layout = ['col-sm-12'];


            //GERAL
            // creates a notebook
            $notebook_geral = new TNotebook;
            $this->form->addContent([$notebook_geral]);

            //ENCONTRISTAS
            $encontristas = ViewEncontrista::where('encontro_id', '=', $encontro->id)->groupBy('circulo_id')->countDistinctBy('id', 'contagem');

            if ($encontristas) {

                //ENCONTRISTAS
                // creates a notebook
                $notebook_encontrista = new TNotebook;

                foreach ($encontristas as $encontrista) {

                    $encontristas_circulo = ViewEncontrista::where('encontro_id', '=', $encontro->id)->where('circulo_id', '=', $encontrista->circulo_id)->orderBy('casal', 'asc')->load();

                    // creates a table
                    $table = new TTable;
                    $table->width = '100%';
                    $table->border = '1';

                    $encontro_circulos = EncontroCirculos::where('encontro_id', '=', $encontro->id)->where('circulo_id', '=', $encontrista->circulo_id)->first();
                    $circulo_cor = ListaItens::where('id', '=', $encontrista->circulo_id)->first();

                    if ($encontro_circulos) {

                        $action_coord = new TAction(['CasalPanel', 'onView']);
                        $action_coord->setParameter('relacao_id', $encontro_circulos->casal_coord_id);

                        $action_sec = new TAction(['CasalPanel', 'onView']);
                        $action_sec->setParameter('relacao_id', $encontro_circulos->casal_sec_id);

                        $casal_link_coord = new TActionLink($encontro_circulos->CasalCoord->casal, $action_coord, 'black', 12, 'u'); //biu
                        $casal_link_sec = new TActionLink($encontro_circulos->CasalSec->casal, $action_sec, 'black', 12, 'u'); //biu

                        // creates a label with the title
                        $title = new TLabel(
                            'Nome do Círculo: <b>' . $encontro_circulos->nome_circulo . '</b><br>
                        Casal Coordenador: <b>' . $casal_link_coord . '</b><br>Casal Secretaria: <b>' . $casal_link_sec . '</b>'
                        );
                    } else {
                        $title = new TLabel(
                            'Nome do Círculo: <b>' . $circulo_cor->item . '</b><br>
                        Casal Coordenador: <b> Não Encontrado</b><br>Casal Secretaria: <b>Não Encontrado</b>'
                        );
                    }

                    // adds a row to the table
                    $row = $table->addRow();
                    $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: ' . $circulo_cor->obs . ';';
                    $title = $row->addCell($title);
                    $title->colspan = 4;

                    $ordem = 0;

                    $table->style = 'border-collapse:collapse; border-bottom: 2px solid black;
                    border-top: 2px solid black;
                    
                    text-align: center;';

                    // adds a row for the code field
                    $row = $table->addRow();
                    $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                    $row->addCell('Ordem');
                    $row->addCell('Casal');
                    $row->addCell('Casamento');
                    $row->addCell('Convite');

                    foreach ($encontristas_circulo as $enc_cir) {

                        $row = $table->addRow();

                        $ordem += 1;

                        $ordem_label = new TLabel($ordem);
                        $ordem_label->setFontStyle('b');
                        $ordem_label->setValue($ordem);

                        $action = new TAction(['CasalPanel', 'onView']);
                        $action->setParameter('relacao_id', $enc_cir->casal_id);

                        $casal_link = new TActionLink($enc_cir->Secretario, $action, 'black', 12, 'u'); //biu

                        $row->addCell($ordem_label);
                        $row->addCell($casal_link);
                        //$row->addCell($enc_cir->DadosCasal->Ele->nome . ' - ' . $enc_cir->DadosCasal->Ela->nome . '<br>' . TDate::date2br($enc_cir->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($enc_cir->DadosCasal->Ela->dt_nascimento));
                        $row->addCell(TDate::date2br($enc_cir->DadosCasal->dt_inicial));
                        if ($enc_cir->casal_convite) {
                            $row->addCell($enc_cir->casal_convite);
                        } else {
                            $row->addCell('Não informado');
                        }
                    }

                    $notebook_encontrista->appendPage($circulo_cor->item . ' <b>(' . $encontrista->contagem . ')</b>', $table);
                }

                $notebook_geral->appendPage('<b>ENCONTRISTAS</b>', $notebook_encontrista);
            }

            //ENCONTREIROS
            $encontreiros = ViewEncontreiro::where('encontro_id', '=', $encontro->id)->groupBy('equipe')->countDistinctBy('id', 'contagem');

            if ($encontreiros) {

                //ENCONTREIROS
                // creates a notebook
                $notebook_encontreiro = new TNotebook;

                foreach ($encontreiros as $encontreiro) {

                    $encontreiros_equipe = ViewEncontreiro::where('encontro_id', '=', $encontro->id)->where('equipe', '=', $encontreiro->equipe)->orderBy('funcao_id, casal', 'asc')->load();

                    // creates a table
                    $table = new TTable;
                    $table->width = '100%';
                    $table->border = '1';

                    // creates a label with the title
                    $title = new TLabel('<b>' . $encontreiro->equipe . ' (' . $encontreiro->contagem . ')</b>');

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
                    $row->addCell('Função');
                    $row->addCell('Casal');
                    //$row->addCell('Nome Completo / Nascimento');
                    $row->addCell('Casamento');
                    $row->addCell('Círculo');

                    foreach ($encontreiros_equipe as $enc_equip) {
                        $row = $table->addRow();

                        $ordem += 1;

                        $ordem_label = new TLabel($ordem);
                        $ordem_label->setFontStyle('b');
                        $ordem_label->setValue($ordem);

                        $action = new TAction(['CasalPanel', 'onView']);
                        $action->setParameter('relacao_id', $enc_equip->casal_id);

                        $casal_link = new TActionLink($enc_equip->casal, $action, 'black', 12, 'u'); //biu

                        $row->addCell($ordem_label);
                        $row->addCell($enc_equip->Funcao);
                        $row->addCell($casal_link);
                        //$row->addCell($enc_equip->DadosCasal->Ele->nome . ' - ' . $enc_equip->DadosCasal->Ela->nome . '<br>' . TDate::date2br($enc_equip->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($enc_equip->DadosCasal->Ela->dt_nascimento));
                        $row->addCell(TDate::date2br($enc_equip->DadosCasal->dt_inicial));
                        $row->addCell($enc_equip->CirculoCor);
                    }

                    $notebook_encontreiro->appendPage($encontreiro->equipe, $table);
                }
                $notebook_geral->appendPage('<b>ENCONTREIROS</b>', $notebook_encontreiro);
            }

            //PALESTRANTES
            $palestrantes = ViewPalestrante::where('encontro_id', '=', $encontro->id)->countDistinctBy('id', 'contagem');

            if ($palestrantes) {

                $palestrantes_palestra = ViewPalestrante::where('encontro_id', '=', $encontro->id)->orderBy('palestra_id, casal', 'asc')->load();

                // creates a table
                $table = new TTable;
                $table->width = '100%';
                $table->border = '1';

                // creates a label with the title
                $title = new TLabel('<b>PALESTRA / PALESTRANTES</b>');
                $title->style = 'color: #fff;';

                // adds a row to the table
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #6f42c1;';
                $title = $row->addCell($title);
                $title->colspan = 5;

                $ordem = 0;

                $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                // adds a row for the code field
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                $row->addCell('Ordem');
                $row->addCell('Palestra');
                $row->addCell('Casal');
                //$row->addCell('Nome Completo / Nascimento');
                $row->addCell('Casamento');
                $row->addCell('Círculo');

                foreach ($palestrantes_palestra as $pales_pal) {
                    $row = $table->addRow();

                    $ordem += 1;

                    $ordem_label = new TLabel($ordem);
                    $ordem_label->setFontStyle('b');
                    $ordem_label->setValue($ordem);

                    $action = new TAction(['PalestranteDataGrid', 'onViewDetalhesPalestra']);
                    $action->setParameter('tipo', 2);
                    $action->setParameter('encontro_id', $pales_pal->encontro_id);
                    $action->setParameter('equipe_id', $pales_pal->palestra_id);
                    $action->setParameter('encontro', $pales_pal->encontro);
                    $action->setParameter('equipe', $pales_pal->palestra);
                    $action->setParameter('casal_id', $pales_pal->casal_id);

                    $palestra_link = new TActionLink($pales_pal->palestra, $action, 'black', 12, 'u'); //biu

                    $palestra_label = new TLabel('palestra');
                    $palestra_label->setFontStyle('b');
                    $palestra_label->setValue($palestra_link);

                    $action = new TAction(['CasalPanel', 'onView']);
                    $action->setParameter('relacao_id', $pales_pal->casal_id);

                    $casal_link = new TActionLink($pales_pal->casal, $action, 'black', 12, 'u'); //biu

                    $row->addCell($ordem_label);
                    $row->addCell($palestra_label);
                    $row->addCell($casal_link); // . ' ' . $pales_pal->Funcao);
                    //$row->addCell($pales_pal->DadosCasal->Ele->nome . ' - ' . $pales_pal->DadosCasal->Ela->nome . '<br>' . TDate::date2br($pales_pal->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($pales_pal->DadosCasal->Ela->dt_nascimento));
                    $row->addCell(TDate::date2br($pales_pal->DadosCasal->dt_inicial));
                    $row->addCell($pales_pal->CirculoCor);
                }

                $notebook_geral->appendPage('<b>PALESTRANTES</b>', $table);
            }

            //EDG
            $edgs = ViewEdg::where('encontro_id', '=', $encontro->id)->countDistinctBy('id', 'contagem');

            if ($edgs) {

                $edgs_edg = ViewEdg::where('encontro_id', '=', $encontro->id)->orderBy('pasta_id, funcao_id', 'asc')->load();

                // creates a table
                $table = new TTable;
                $table->width = '100%';
                $table->border = '1';

                // creates a label with the title
                $title = new TLabel('<b>EQUIPE DE DIREÇÃO GERAL</b>');
                $title->style = 'color: #fff;';

                // adds a row to the table
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #343a40;';
                $title = $row->addCell($title);
                $title->colspan = 5;

                $ordem = 0;

                $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                // adds a row for the code field
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                $row->addCell('Ordem');
                $row->addCell('Pasta');
                $row->addCell('Casal');
                //$row->addCell('Nome Completo / Nascimento');
                $row->addCell('Casamento');
                $row->addCell('Círculo');

                foreach ($edgs_edg as $edg_ed) {
                    $row = $table->addRow();

                    $ordem += 1;

                    $ordem_label = new TLabel($ordem);
                    $ordem_label->setFontStyle('b');
                    $ordem_label->setValue($ordem);

                    $action = new TAction(['EdgDataGrid', 'onViewDetalhesPasta']);
                    $action->setParameter('tipo', 3);
                    $action->setParameter('encontro_id', $edg_ed->encontro_id);
                    $action->setParameter('equipe_id', $edg_ed->pasta_id);
                    $action->setParameter('encontro', $edg_ed->encontro);
                    $action->setParameter('equipe', $edg_ed->pasta);
                    $action->setParameter('casal_id', $edg_ed->casal_id);

                    $pasta_link = new TActionLink($edg_ed->pasta, $action, 'black', 12, 'u'); //biu

                    $pasta_label = new TLabel('pasta');
                    $pasta_label->setFontStyle('b');
                    $pasta_label->setValue($pasta_link);

                    $action = new TAction(['CasalPanel', 'onView']);
                    $action->setParameter('relacao_id', $edg_ed->casal_id);

                    $casal_link = new TActionLink($edg_ed->casal, $action, 'black', 12, 'u'); //biu


                    $row->addCell($ordem_label);
                    $row->addCell($pasta_label);
                    $row->addCell($casal_link); // . '<br>' . $edg_ed->Funcao);
                    //$row->addCell($edg_ed->DadosCasal->Ele->nome . ' - ' . $edg_ed->DadosCasal->Ela->nome . '<br>' . TDate::date2br($edg_ed->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($edg_ed->DadosCasal->Ela->dt_nascimento));
                    $row->addCell(TDate::date2br($edg_ed->DadosCasal->dt_inicial));
                    $row->addCell($edg_ed->CirculoCor);
                }

                $notebook_geral->appendPage('<b>EDG</b>', $table);
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }


    /**
     * method onView()
     * Executed when the user clicks at the view button
     */
    public static function onViewDocImagem($param)
    {

        try {
            if ($param['relacao_id']) {

                TTransaction::open('adea');   // open a transaction with database 'samples'

                // get the parameter
                $object = new PessoasRelacao($param['relacao_id']);

                $win = TWindow::create('Documento da Relação', 0.8, 0.6);
                if ($object->doc_imagem) {
                    $img_doc = new TImage($object->doc_imagem);
                } else {
                    $img_doc = new TImage('app/images/dadosderelacao/semdocimagem.jpg');
                }

                $img_doc->width = '100%';
                $img_doc->height = '100%';
                $img_doc->style = '';
                $div_image = new TElement('div');
                //$div_image->class = 'zoom';
                $div_image->add($img_doc);


                $win->add($div_image);
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
     * Define when the action can be displayed
     */
    public function displayColumn($object)
    {
        if ($object->parentesco_id >= 921 and $object->parentesco_id <= 932) {
            return TRUE;
        }
        return FALSE;
    }

    public function displayColumn2($object)
    {
        if ($object->parentesco_id >= 921 and $object->parentesco_id <= 924) {
            return TRUE;
        } else if ($object->parentesco_id >= 927 and $object->parentesco_id <= 930) {
            return TRUE;
        }
        return FALSE;
    }

    public static function onMudaGrauParente($param) {}

    /**
     * Ask before deletion
     */
    public static function onDeleteContato($param)
    {
        // define the delete action
        $action = new TAction(array(__CLASS__, 'DeleteContato'));
        $action->setParameters($param); // pass the key parameter ahead

        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }

    /**
     * Delete a record
     */
    public static function DeleteContato($param)
    {
        try {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('adea'); // open a transaction with database
            $object = new PessoaContato($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction

            $posAction = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => $param['pessoa_id'], 'register_state' => 'false']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $posAction); // success message
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
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
        TSession::delValue('pessoa_painel');
        TSession::delValue('pessoa_painel_vinculos');
        TScript::create("Template.closeRightPanel()");
    }
}
