<?php

use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;

/**
 * CityWindow Registration
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EddDadosPessoa extends TWindow
{
    use ControlePessoas;
    protected $form; // form

    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setModal(true);
        parent::removePadding();
        parent::setSize(500, null);
        parent::setTitle('Dados Gerais');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Endereco');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_pf');
        $this->form->setClientValidation(true);

        //dados de endereço
        $id             = new TEntry('id');
        $cpf_cnpj       = new TEntry('cpf_cnpj');
        //$cpf_cnpj->setExitAction(new TAction(array($this, 'onConsultaCPF')));
        $cpf_cnpj->setMask('999.999.999-99');
        $nome  = new TDBEntry('nome', 'adea', 'Pessoa', 'nome');
        $nome->setInnerIcon(new TImage('fa:user blue'), 'left');
        $nome->placeholder = ' Nome Completo';
        $nome->forceUpperCase();
        $popular  = new TDBEntry('popular', 'adea', 'Pessoa', 'popular');
        $popular->setInnerIcon(new TImage('fa:user blue'), 'left');
        $popular->placeholder = ' Nome pelo qual é conhecido ou gosta de ser chamado';
        $popular->forceUpperCase();

        $filterEC = new TCriteria;
        $filterEC->add(new TFilter('lista_id', '=', '17'));
        //$filterEC->add(new TFilter('id', '<', '0'));
        $estado_civil_id = new TDBCombo('estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterEC);
        //$estado_civil_id->setChangeAction(new TAction(array($this, 'onEstadocivilChange')));

        $dt_nascimento  = new TDate('dt_nascimento');
        //$dt_nascimento->setDatabaseMask('yyyy-mm-dd');
        $dt_nascimento->setMask('dd/mm/yyyy');
        $dt_nascimento->setExitAction(new TAction(array($this, 'onCalculaIdade')));
        $idade = new TEntry('idade');
        $genero         = new TCombo('genero');
        $genero->addItems(['M' => 'Masculino', 'F' => 'Feminino']);
        //$genero->setChangeAction(new TAction(array($this, 'onGeneroChange')));

        // define some properties for the form fields
        $id->setEditable(FALSE);
        $cpf_cnpj->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $estado_civil_id->setSize('100%');
        $dt_nascimento->setSize('100%');
        $idade->setSize('100%');
        $genero->setSize('100%');

        // add the fields
        $row = $this->form->addFields(
            [
                new TLabel('Cod.'),
                $id
            ],
            [
                new TLabel('CPF'),
                $cpf_cnpj
            ]
        );
        $row->layout = ['col-sm-4', 'col-sm-8'];

        $row = $this->form->addFields(
            [
                new TLabel('Nome'),
                $nome
            ]
        );
        $row->layout = ['col-sm-12'];

        // add the fields
        $row = $this->form->addFields(
            [
                new TLabel('Nome Popular'),
                $popular
            ],
            [
                new TLabel('Gênero'),
                $genero
            ],
        );
        $row->layout = ['col-sm-8', 'col-sm-4'];

        $row = $this->form->addFields(
            [
                new TLabel('Nascimento'),
                $dt_nascimento
            ],
            [
                new TLabel('Idade'),
                $idade
            ],
            [
                new TLabel('Estado Civil'),
                $estado_civil_id
            ]
        );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        if (TSession::getValue('dados_relacao')) {

            $label = new TLabel('<br>Dados da Relação', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
            $dados_relacao = TSession::getValue('dados_relacao');

            if (!empty($dados_relacao['doc_imagem'])) {
                $dados_relacao['doc_imagem'] = substr((json_decode(urldecode($dados_relacao['doc_imagem']))->fileName), 4); // aqui foi a solução
            }

            if ($dados_relacao['doc_imagem']) {
                $c = new THyperLink('(Documento Anexado)', 'download.php?file=tmp/' . $dados_relacao['doc_imagem'], 'blue', 12, 'biu');
            } else {
                $c = '';
            }

            $labeldadosrelacao = new TLabel('Tipo de Vínculo: ' . $dados_relacao['tipo_vinculo'] . ' - (' . $dados_relacao['dt_inicial'] . ') - Há ' . $dados_relacao['tempo'] . '. ' . $c, '#555555', 12, 'b');
            $this->form->addContent([$label]);
            $this->form->addContent([$labeldadosrelacao]);
        }

        // validations
        $id->addValidation('Estado', new TRequiredValidator);
        $cpf_cnpj->addValidation('CPF', new TCPFValidator);
        $cpf_cnpj->addValidation('CPF', new TRequiredValidator);
        $nome->addValidation('Nome', new TRequiredValidator);
        $popular->addValidation('Popular', new TRequiredValidator);
        $estado_civil_id->addValidation('Estado Civil', new TRequiredValidator);
        $dt_nascimento->addValidation('Nascimento', new TRequiredValidator);
        $genero->addValidation('Gênero', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdite($param)
    {
        try {
            if ($param['pessoa_id']) {

                TTransaction::open('adea');   // open a transaction with database 'samples'

                $pessoa = new Pessoa($param['pessoa_id']);

                if ($pessoa) {

                    $pessoafisica = PessoaFisica::where('pessoa_id', '=', $param['pessoa_id'])->first();

                    $this->form->setData($pessoa);   // fill the form with the active record data

                    $pessoa->genero = $pessoafisica->genero;
                    $pessoa->dt_nascimento =  TDate::date2br($pessoafisica->dt_nascimento);
                    $pessoa->estado_civil_id = $pessoafisica->estado_civil_id;
                    TForm::sendData('form_pf', $pessoa);
                }

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
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            $this->form->validate(); // run form validation

            $data = $this->form->getData(); // get form data as array

            $pessoa_painel = TSession::getValue('pessoa_painel');

            if ($data) {
                $consultaendereco = Endereco::where('logradouro_id', '=', $data->logradouro_id)->where('n', '=', $data->n)->where('bairro_id', '=', $data->bairro_id)->first();
                if ($consultaendereco) {
                    $pessoaendereco = new Pessoa($pessoa_painel->id);
                    $pessoaendereco->endereco_id = $consultaendereco->id;
                    $pessoaendereco->store();
                } else {
                    $endereco = new Endereco();
                    $endereco->fromArray((array) $data); // load the object with data
                    $endereco->store();

                    $pessoaendereco = new Pessoa($pessoa_painel->id);
                    $pessoaendereco->endereco_id = $endereco->id;
                    $pessoaendereco->store();
                }
            }

            // fill the form with the active record data
            $this->form->setData($data);

            self::onClose();

            $posAction = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => $pessoa_painel->id, 'register_state' => 'false']);

            TTransaction::close();  // close the transaction

            // shows the success message
            new TMessage('info', 'Registro Salvo!', $posAction);
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData($this->form->getData()); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
