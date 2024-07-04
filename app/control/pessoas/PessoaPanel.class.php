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
class PessoaPanel extends TPage
{
    protected $form; // form
    protected $pessoa;
    protected $formpessoa; // form
    protected $formpessoaendereco; // form
    protected $formpessoarelacao; // form
    protected $lista_contatos;
    protected $lista_parentes;

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

        $this->form = new BootstrapFormBuilder('form_Pessoa_Panel');
        $this->form->setFormTitle('Pessoa');

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

            //PESSOA
            $panelpessoa = new TPanelGroup('<b>Dados Gerais</b>', '#f5f5f5');

            //$labelcontatos = new TLabel('Não há contatos cadastrados para esta pessoa.', '#dd5a43', 12, 'b');

            $this->pessoa = new ViewPessoaFisica($param['key']);
            TSession::setValue('pessoa_painel', $this->pessoa);

            $date = new DateTime($this->pessoa->dt_nascimento);
            $interval = $date->diff(new DateTime(date('Y-m-d')));

            $this->pessoa->genero = $this->pessoa->genero == 'F' ? 'Feminino' : 'Masculino';
            $this->pessoa->dt_nascimento =  TDate::date2br($this->pessoa->dt_nascimento);

            $this->formpessoa = new BootstrapFormBuilder('form_Pessoa');

            $row = $this->formpessoa->addFields(['<b>Cod.:</b>', $this->pessoa->id], ['<b>CPF:</b>', $this->pessoa->cpf], ['<b>Status:</b>', $this->pessoa->status_pessoa]);
            $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];
            $row = $this->formpessoa->addFields(['<b>Nome:</b>', $this->pessoa->nome], ['<b>Popular:</b>', $this->pessoa->popular]);
            $row->layout = ['col-sm-7', 'col-sm-5'];
            $row = $this->formpessoa->addFields(['<b>Nascimento:</b>', $this->pessoa->dt_nascimento], ['<b>Idade:</b>', $interval->format('%Y anos')]);
            $row->layout = ['col-sm-7', 'col-sm-5'];
            $row = $this->formpessoa->addFields(['<b>Gênero:</b>', $this->pessoa->genero], ['<b>Estado Civil:</b>', $this->pessoa->estado_civil]);
            $row->layout = ['col-sm-7', 'col-sm-5'];

            $panelpessoa->add($this->formpessoa)->style = 'overflow-x:auto';

            $this->form->addContent([$panelpessoa]);

            //ENDEREÇO
            $panelpessoaendereco = new TPanelGroup('<b>Endereço</b>', '#f5f5f5');

            $this->formpessoaendereco = new BootstrapFormBuilder('form_PessoaEndereco');

            $row = $this->formpessoaendereco->addFields(['<b>Endereço:</b>', $this->pessoa->endereco]);
            $row->layout = ['col-sm-12'];


            if ($this->pessoa->endereco) {
                $panelpessoaendereco->add($this->formpessoaendereco)->style = 'overflow-x:auto';
                $panelpessoaendereco->addHeaderActionLink('<b>Editar</b>',  new TAction(['EddEndereco', 'onEdite'], ['pessoa_id' => $this->pessoa->id, 'register_state' => 'false']), 'far:edit blue');
            } else {
                $labelcontatos = new TLabel('Não há endereço cadastrado para esta pessoa.', '#dd5a43', 12, 'b');
                $panelpessoaendereco->add($labelcontatos);
                $panelpessoaendereco->addHeaderActionLink('<b>Cadastrar</b>',  new TAction(['EddEndereco', 'onEdite'], ['pessoa_id' => $this->pessoa->id, 'register_state' => 'false']), 'fa:plus green');
            }

            $this->form->addContent([$panelpessoaendereco]);

            //CONTATOS
            $this->lista_contatos = new BootstrapDatagridWrapper(new TDataGrid);
            $this->lista_contatos->style = 'width:100%';
            $this->lista_contatos->disableDefaultClick();

            $column_tipo_contato_id = $this->lista_contatos->addColumn(new TDataGridColumn('TipoContato->item', 'Tipo', 'left'));
            $column_contato = $this->lista_contatos->addColumn(new TDataGridColumn('contato', 'Contato', 'left'));
            $column_status_contato_id    = $this->lista_contatos->addColumn(new TDataGridColumn('status_contato_id', 'Ativo?', 'center'));

            $column_status_contato_id->setTransformer(function ($value) {
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

            $column_contato->setTransformer(function ($value, $object) {
                if ($value) {
                    if ($object->tipo_contato_id == 101) {
                        $value = str_replace([' ', '-', '(', ')'], ['', '', '', ''], $value);
                        $icon  = "<i class='fab fa-whatsapp' aria-hidden='true'></i>";
                        return "{$icon} <a target='newwindow' href='https://api.whatsapp.com/send?phone=55{$value}&text=Olá'> {$value} </a>";
                    } else if ($object->tipo_contato_id == 102) {
                        $icon  = "<i class='far fa-envelope' aria-hidden='true'></i>";
                        return "{$icon} <a generator='newwindow' href='mailto:$value'>$value</a>";
                    }
                }

                return $value;
            });

            $actioneditarcontato = new TDataGridAction(['EddPessoaContato', 'onEdite'], ['id' => '{id}', 'tipo_contato_id' => '{tipo_contato_id}', 'pessoa_id' => $this->pessoa->id]);
            $this->lista_contatos->addAction($actioneditarcontato, 'Editar',   'far:edit blue');

            $actiondeletecontato = new TDataGridAction([$this, 'onDeleteContato'],   ['id' => '{id}', 'pessoa_id' => $this->pessoa->id]);
            $this->lista_contatos->addAction($actiondeletecontato, 'Deletar', 'far:trash-alt red');

            $this->lista_contatos->createModel();

            $pessoacontatos = PessoaContato::where('pessoa_id', '=', $this->pessoa->id)->orderBy('id', 'asc')->load();
            $this->lista_contatos->addItems($pessoacontatos);

            $panelcontatos = new TPanelGroup('<b>Contatos</b>', '#f5f5f5');

            if ($pessoacontatos) {
                $panelcontatos->add($this->lista_contatos)->style = 'overflow-x:auto';
            } else {
                $labelcontatos = new TLabel('Não há contatos cadastrados para esta pessoa.', '#dd5a43', 12, 'b');
                $panelcontatos->add($labelcontatos);
            }

            $this->form->addContent([$panelcontatos]);

            $dropdowncontatos = new TDropDown('<b>Adicionar</b>', 'fa:plus green');
            //$dropdown->addAction(
            $dropdowncontatos->addAction('Fone', new TAction(['EddPessoaContato', 'onEdit'], ['pessoa_id' => $this->pessoa->id, 'tipo_contato_id' => 101]), 'fas:phone-alt fa-fw blue');
            $dropdowncontatos->addAction('Email', new TAction(['EddPessoaContato', 'onEdit'], ['pessoa_id' => $this->pessoa->id, 'tipo_contato_id' => 102]), 'fas:envelope fa-fw blue');

            //$panelcontatos->addHeaderActionLink($dropdown);
            $panelcontatos->addHeaderWidget($dropdowncontatos);

            //PARENTES
            $this->lista_parentes = new BootstrapDatagridWrapper(new TDataGrid);
            $this->lista_parentes->style = 'width:100%';
            $this->lista_parentes->disableDefaultClick();

            $column_parentesco_id = $this->lista_parentes->addColumn(new TDataGridColumn('Parentesco->item', 'Grau', 'left'));
            $column_pessoa_parente_id = $this->lista_parentes->addColumn(new TDataGridColumn('PessoaParente->nome', 'Nome', 'left'));
            $column_obs_parentesco = $this->lista_parentes->addColumn(new TDataGridColumn('obs_parentesco', 'Obs', 'left'));

            $column_pessoa_parente_id->setTransformer(function ($value, $object) {
                if ($value) {
                    $icon  = "<i class='far fa-user' aria-hidden='true'></i>"; //fa:user blue
                    return "{$icon} <a generator='adianti' href='index.php?class=PessoaPanel&method=onView&key=$object->pessoa_parente_id'>$value</a>";
                }
                return $value;
            });

            $actionverrelacao = new TDataGridAction(['DadosRelacao', 'onVerRelacao'],   ['id' => '{id}']);
            $actionverpdfrelacao = new TDataGridAction([$this, 'onViewDocImagem'],   ['id' => '{id}']);

            $this->lista_parentes->addAction($actionverrelacao, 'Ver dados da relação', 'fas:eye fa-fw green');
            $this->lista_parentes->addAction($actionverpdfrelacao, 'Ver documento', 'far:fa-sharp fa-solid fa-file-pdf red');

            $actionverrelacao->setDisplayCondition(array($this, 'displayColumn'));
            $actionverpdfrelacao->setDisplayCondition(array($this, 'displayColumn2'));

            $this->lista_parentes->createModel();

            $pessoavinculos = PessoaParentesco::where('pessoa_id', '=', $this->pessoa->id)->orderBy('id', 'asc')->load();
            $this->lista_parentes->addItems($pessoavinculos);
            TSession::setValue('pessoa_painel_vinculos', $pessoavinculos);

            $panelparentes = new TPanelGroup('<b>Vínculos</b>', '#f5f5f5');

            if ($pessoavinculos) {
                $panelparentes->add($this->lista_parentes)->style = 'overflow-x:auto';
            } else {
                $labelparentes = new TLabel('Não há vínculos cadastrados para esta pessoa.', '#dd5a43', 12, 'b');
                $panelparentes->add($labelparentes);
            }

            $panelparentes->addHeaderActionLink('<b>Adicionar</b>',  new TAction(['AddParente', 'onLoad'], ['pessoa_painel_id' => $this->pessoa->id, 'pessoa_painel_cpf' => $this->pessoa->cpf, 'pessoa_genero_painel_id' => $this->pessoa->genero, 'register_state' => 'false']), 'fa:plus green');
            $this->form->addContent([$panelparentes]);

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
            if ($param['id']) {

                TTransaction::open('adea');   // open a transaction with database 'samples'

                $key = $param['id'];  // get the parameter
                $object = PessoasRelacao::where('relacao_id', '=', $key)->first();

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

    public static function onMudaGrauParente($param)
    {
    }

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
        TScript::create("Template.closeRightPanel()");
    }
}
