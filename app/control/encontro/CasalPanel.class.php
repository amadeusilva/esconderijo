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
class CasalPanel extends TPage
{
    protected $form; // form

    protected $form_encontro; // form
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

        $this->form = new BootstrapFormBuilder('form_Casal_Panel');
        $this->form->setFormTitle('Nome do Casal');

        $dropdown = new TDropDown('Opções', 'fa:th');
        //$dropdown->addAction(
        $dropdown->addAction('Imprimir', new TAction([$this, 'onPrint'], ['key' => $param['key'], 'static' => '1']), 'far:file-pdf red');
        //$dropdown->addAction( 'Gerar etiqueta', new TAction([$this, 'onGeraEtiqueta'], ['key'=>$param['key'], 'static' => '1']), 'far:envelope purple');
        //$dropdown->addAction('Editar', new TAction([$this, 'onEdit'], ['key' => $param['key']]), 'far:edit blue');

        $this->form->addHeaderWidget($dropdown);

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

            $this->form_encontro = new BootstrapFormBuilder('form_Encontro');

            $row = $this->form_encontro->addFields([new TLabel('<b>Cod.:</b>', ''), $encontro->id], [new TLabel('<b>Evento:</b>', ''), $encontro->sigla]);
            $row->layout = ['col-sm-6', 'col-sm-6'];

            $row = $this->form_encontro->addFields([new TLabel('<b>Encontristas:</b>', ''), $total_encontristas], [new TLabel('<b>Encontreiros:</b>', ''), $total_encontreiros], [new TLabel('<b>Total:</b>', ''), $total]);
            $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

            $row = $this->form_encontro->addFields([new TLabel('<b>Data Início:</b>', ''), TDate::date2br($encontro->dt_inicial)], [new TLabel('<b>Data Fim:</b>', ''), TDate::date2br($encontro->dt_final)], [new TLabel('<b>Cântico:</b>', ''), $encontro->cantico]);
            $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-6'];

            $row = $this->form_encontro->addFields([new TLabel('<b>Local:</b>', ''), $encontro->local], [new TLabel('<b>Endereço:</b>', ''), $encontro->endereco]);
            $row->layout = ['col-sm-6', 'col-sm-6'];

            $row = $this->form_encontro->addFields([new TLabel('<b>Tema:</b>', ''), $encontro->tema]);
            $row->layout = ['col-sm-12'];

            $row = $this->form_encontro->addFields([new TLabel('<b>Divisa:</b>', ''), $encontro->divisa]);
            $row->layout = ['col-sm-12'];

            $label = new TLabel('<br>Listas', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';

            $row = $this->form_encontro->addFields([$label]);
            $row->layout = ['col-sm-12'];

            //ENCONTRISTAS
            $button_encontrista = new TButton('show_hide_encontrista');
            $button_encontrista->class = 'btn btn-default btn-sm active';
            $button_encontrista->setLabel('Exibir Lista de <b>Encontristas</b> por Círculos');
            $button_encontrista->addFunction("\$('[oid=notebook_encontrista-measures]').slideToggle(); $(this).toggleClass( 'active' )");
            $row = $this->form_encontro->addFields([$button_encontrista]);
            $row->layout = ['col-sm-12'];
            // creates a notebook
            $notebook_encontrista = new TNotebook;
            $notebook_encontrista->oid = 'notebook_encontrista-measures';

            //ENCONTREIROS
            $button_encontreiro = new TButton('show_hide_encontreiro');
            $button_encontreiro->class = 'btn btn-default btn-sm active';
            $button_encontreiro->setLabel('Exibir Lista de <b>Encontreiros</b> por Equipe');
            $button_encontreiro->addFunction("\$('[oid=notebook_encontreiro-measures]').slideToggle(); $(this).toggleClass( 'active' )");
            $row = $this->form_encontro->addFields([$button_encontreiro]);
            $row->layout = ['col-sm-12'];
            // creates a notebook
            $notebook_encontreiro = new TNotebook;
            $notebook_encontreiro->oid = 'notebook_encontreiro-measures';

            //PALESTRANTES
            $button_palestrante = new TButton('show_hide_palestrante');
            $button_palestrante->class = 'btn btn-default btn-sm active';
            $button_palestrante->setLabel('Exibir Lista de <b>Palestrantes</b>');
            $button_palestrante->addFunction("\$('[oid=notebook_palestrante-measures]').slideToggle(); $(this).toggleClass( 'active' )");
            $row = $this->form_encontro->addFields([$button_palestrante]);
            $row->layout = ['col-sm-12'];
            // creates a notebook
            $notebook_palestrante = new TNotebook;
            $notebook_palestrante->oid = 'notebook_palestrante-measures';

            //EDG
            $button_edg = new TButton('show_hide_edg');
            $button_edg->class = 'btn btn-default btn-sm active';
            $button_edg->setLabel('Exibir <b>Equipe de Direção Geral</b>');
            $button_edg->addFunction("\$('[oid=notebook_edg-measures]').slideToggle(); $(this).toggleClass( 'active' )");
            $row = $this->form_encontro->addFields([$button_edg]);
            $row->layout = ['col-sm-12'];
            // creates a notebook
            $notebook_edg = new TNotebook;
            $notebook_edg->oid = 'notebook_edg-measures';

            // creates a frame
            $frame_dados = new TFrame;
            $frame_dados->setLegend('Dados');
            $frame_dados->add($this->form_encontro);
            $this->form->addContent([$frame_dados]);

            // creates a frame
            $frame_listas = new TFrame;
            $frame_listas->setLegend('Listas');
            $frame_listas->add($notebook_encontrista);
            $frame_listas->add($notebook_encontreiro);
            $frame_listas->add($notebook_palestrante);
            $frame_listas->add($notebook_edg);

            $this->form->addContent([$frame_listas]);

            //ENCONTRISTAS
            $encontristas = ViewEncontrista::where('encontro_id', '=', $encontro->id)->groupBy('circulo')->countDistinctBy('id', 'contagem');

            if ($encontristas) {

                foreach ($encontristas as $encontrista) {

                    $encontristas_circulo = ViewEncontrista::where('encontro_id', '=', $encontro->id)->where('circulo', '=', $encontrista->circulo)->orderBy('casal', 'asc')->load();

                    // creates a table
                    $table = new TTable;
                    $table->width = '100%';
                    $table->border = '1';

                    // creates a label with the title
                    $title = new TLabel('<b>' . $encontrista->circulo . ' (' . $encontrista->contagem . ')</b>');

                    $circulo_cor = ListaItens::where('item', '=', $encontrista->circulo)->first();
                    $circulo_cor_item = $circulo_cor->obs;
                    // adds a row to the table
                    $row = $table->addRow();
                    $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: ' . $circulo_cor_item . ';';
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
                    $row->addCell('Nome Completo / Nascimento');
                    $row->addCell('Casamento');

                    foreach ($encontristas_circulo as $enc_cir) {
                        $row = $table->addRow();

                        $ordem += 1;

                        $ordem_label = new TLabel($ordem);
                        $ordem_label->setFontStyle('b');
                        $ordem_label->setValue($ordem);

                        $row->addCell($ordem_label);
                        $row->addCell($enc_cir->casal . ' ' . $enc_cir->Secretario);
                        $row->addCell($enc_cir->DadosCasal->Ele->nome . ' - ' . $enc_cir->DadosCasal->Ela->nome . '<br>' . TDate::date2br($enc_cir->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($enc_cir->DadosCasal->Ela->dt_nascimento));
                        $row->addCell(TDate::date2br($enc_cir->DadosCasal->dt_inicial));
                    }

                    $notebook_encontrista->appendPage($encontrista->circulo, $table);
                }
            }

            //ENCONTREIROS
            $encontreiros = ViewEncontreiro::where('encontro_id', '=', $encontro->id)->groupBy('equipe')->countDistinctBy('id', 'contagem');

            if ($encontreiros) {

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
                    $title->colspan = 6;

                    $ordem = 0;

                    $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                    // adds a row for the code field
                    $row = $table->addRow();
                    $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                    $row->addCell('Ordem');
                    $row->addCell('Função');
                    $row->addCell('Casal');
                    $row->addCell('Nome Completo / Nascimento');
                    $row->addCell('Casamento');
                    $row->addCell('Círculo');

                    foreach ($encontreiros_equipe as $enc_equip) {
                        $row = $table->addRow();

                        $ordem += 1;

                        $ordem_label = new TLabel($ordem);
                        $ordem_label->setFontStyle('b');
                        $ordem_label->setValue($ordem);

                        $row->addCell($ordem_label);
                        $row->addCell($enc_equip->Funcao);
                        $row->addCell($enc_equip->casal);
                        $row->addCell($enc_equip->DadosCasal->Ele->nome . ' - ' . $enc_equip->DadosCasal->Ela->nome . '<br>' . TDate::date2br($enc_equip->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($enc_equip->DadosCasal->Ela->dt_nascimento));
                        $row->addCell(TDate::date2br($enc_equip->DadosCasal->dt_inicial));
                        $row->addCell($enc_equip->CirculoCor);
                    }

                    $notebook_encontreiro->appendPage($encontreiro->equipe, $table);
                }
            }

            //PALESTRANTES
            $palestrantes = ViewEncontreiro::where('casal_id', '=', $param['relacao_id'])->countDistinctBy('id', 'contagem');

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
                $title->colspan = 6;

                $ordem = 0;

                $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                // adds a row for the code field
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                $row->addCell('Ordem');
                $row->addCell('Palestra');
                $row->addCell('Casal');
                $row->addCell('Nome Completo / Nascimento');
                $row->addCell('Casamento');
                $row->addCell('Círculo');

                foreach ($palestrantes_palestra as $pales_pal) {
                    $row = $table->addRow();

                    $ordem += 1;

                    $ordem_label = new TLabel($ordem);
                    $ordem_label->setFontStyle('b');
                    $ordem_label->setValue($ordem);

                    $palestra_label = new TLabel('palestra');
                    $palestra_label->setFontStyle('b');
                    $palestra_label->setValue($pales_pal->palestra);

                    $row->addCell($ordem_label);
                    $row->addCell($palestra_label);
                    $row->addCell($pales_pal->casal . ' ' . $pales_pal->Funcao);
                    $row->addCell($pales_pal->DadosCasal->Ele->nome . ' - ' . $pales_pal->DadosCasal->Ela->nome . '<br>' . TDate::date2br($pales_pal->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($pales_pal->DadosCasal->Ela->dt_nascimento));
                    $row->addCell(TDate::date2br($pales_pal->DadosCasal->dt_inicial));
                    $row->addCell($pales_pal->CirculoCor);
                }

                $notebook_palestrante->appendPage('LISTA DE PALESTRANTES', $table);
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
                $title->colspan = 6;

                $ordem = 0;

                $table->style = 'border-collapse:collapse; border-bottom: 2px solid black; border-top: 2px solid black; text-align: center;';

                // adds a row for the code field
                $row = $table->addRow();
                $row->style = 'font-weight: bold; border-bottom: 2px solid black; background-color: #dcdcdc;';
                $row->addCell('Ordem');
                $row->addCell('Pasta');
                $row->addCell('Casal');
                $row->addCell('Nome Completo / Nascimento');
                $row->addCell('Casamento');
                $row->addCell('Círculo');

                foreach ($edgs_edg as $edg_ed) {
                    $row = $table->addRow();

                    $ordem += 1;

                    $ordem_label = new TLabel($ordem);
                    $ordem_label->setFontStyle('b');
                    $ordem_label->setValue($ordem);

                    $pasta_label = new TLabel('pasta');
                    $pasta_label->setFontStyle('b');
                    $pasta_label->setValue($edg_ed->pasta);

                    $row->addCell($ordem_label);
                    $row->addCell($pasta_label);
                    $row->addCell($edg_ed->casal . '<br>' . $edg_ed->Funcao);
                    $row->addCell($edg_ed->DadosCasal->Ele->nome . ' - ' . $edg_ed->DadosCasal->Ela->nome . '<br>' . TDate::date2br($edg_ed->DadosCasal->Ele->dt_nascimento) . ' - ' . TDate::date2br($edg_ed->DadosCasal->Ela->dt_nascimento));
                    $row->addCell(TDate::date2br($edg_ed->DadosCasal->dt_inicial));
                    $row->addCell($edg_ed->CirculoCor);
                }

                $notebook_edg->appendPage('EDG', $table);
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
