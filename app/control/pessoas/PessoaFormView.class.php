<?php

use Adianti\Validator\TCNPJValidator;

/**
 * CustomerFormView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PessoaFormView extends TPage
{

    use ControleEndereco;
    use ControlePessoas;

    private $form; // form
    private $embedded;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param, $embedded = false)
    {
        parent::__construct();

        $this->embedded = $embedded;

        if (!$this->embedded) {
            parent::setTargetContainer('adianti_right_panel');
        }

        // creates the form
        $this->form = new BootstrapFormBuilder('form_pessoa');
        if (!$this->embedded) {
            $this->form->setFormTitle('Pessoa Jurídica');
        }
        $this->form->setClientValidation(true);

        //dados da pessoa fisica
        $id             = new TEntry('id');
        $filterTipo = new TCriteria;
        $filterTipo->add(new TFilter('id', '!=', 1));
        $filterTipo->add(new TFilter('lista_id', '=', '15'));
        $tipo_pessoa   = new TDBCombo('tipo_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterTipo);

        $cpf_cnpj       = new TEntry('cpf_cnpj');
        $cpf_cnpj->setMask('99.999.999/9999-99');
        $nome           = new TEntry('nome');
        $popular        = new TEntry('popular');
        $fone           = new TEntry('fone');
        $fone->setMask('(99) 99999-9999');
        $email          = new TEntry('email');

        //$filterStatusPessoa = new TCriteria;
        //$filterStatusPessoa->add(new TFilter('lista_id', '=', '5'));
        //$status_pessoa   = new TDBCombo('status_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterStatusPessoa);

        // define some properties for the form fields
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $cpf_cnpj->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        //$status_pessoa->setSize('100%');
        $tipo_pessoa->setSize('100%');

        //dados de endereço
        $cep  = new TDBEntry('cep', 'adea', 'Endereco', 'cep');
        $cep->setMask('99.999-999');

        $estado_id       = new TDBCombo('estado_id', 'adea', 'Estado', 'id', 'estado', 'estado');
        $estado_id->enableSearch();

        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $cidade_id = new TDBCombo('cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $filter);
        $cidade_id->enableSearch();

        $tipo_id       = new TDBCombo('tipo_id', 'adea', 'TipoLogradouro', 'id', 'tipo', 'tipo');
        $tipo_id->enableSearch();

        $logradouro_id = new TDBCombo('logradouro_id', 'adea', 'Logradouro', 'id', 'logradouro', 'logradouro', $filter);
        $logradouro_id->enableSearch();

        $bairro_id = new TDBCombo('bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $filter);
        $bairro_id->enableSearch();

        $n                  = new TEntry('n');
        $ponto_referencia  = new TDBEntry('ponto_referencia', 'adea', 'ENDERECO', 'ponto_referencia');
        $ponto_referencia->placeholder = 'PRÓXIMO A PRAÇAS, HOSPITAIS, EMPRESAS...';
        $ponto_referencia->forceUpperCase();

        $cep->setExitAction(new TAction(array($this, 'onCEPAction')));

        // define some properties for the form fields
        $cep->setSize('100%');
        $estado_id->setSize('100%');
        $cidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $logradouro_id->setSize('100%');
        $bairro_id->setSize('100%');
        $n->setSize('100%');
        $ponto_referencia->setSize('100%');

        //Dados
        $this->form->appendPage('Dados');
        $row = $this->form->addFields(
            [
                new TLabel('Cod.'),
                $id
            ],
            [
                new TLabel('CNPJ'),
                $cpf_cnpj
            ],
            [
                new TLabel('Tipo'),
                $tipo_pessoa
            ]
        );
        $row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-4'];

        $row = $this->form->addFields(
            [
                new TLabel('Razão Social'),
                $nome
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Nome Fantasia'),
                $popular
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Fone'),
                $fone
            ],
            [
                new TLabel('Email'),
                $email
            ]
        );
        $row->layout = ['col-sm-5', 'col-sm-7'];

        //Endereço
        $this->form->appendPage('Endereço');
        $row = $this->form->addFields(
            [new TLabel('CEP'),    $cep],
            [
                new TLabel('Estado'),
                $estado_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Cidade'),
                $cidade_id
            ],
            [
                new TLabel('Tipo'),
                $tipo_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Endereço'),
                $logradouro_id
            ],
            [
                new TLabel('Nº'),
                $n
            ]
        );
        $row->layout = ['col-sm-9', 'col-sm-3'];

        $row = $this->form->addFields(
            [
                new TLabel('Bairro'),
                $bairro_id
            ],
            [
                new TLabel('Ponto de Referência'),
                $ponto_referencia
            ]
        );
        $row->layout = ['col-sm-5', 'col-sm-7'];

        $cpf_cnpj->addValidation('CNPJ', new TCNPJValidator);
        $tipo_pessoa->addValidation('Tipo de Pessoa', new TRequiredValidator);
        $nome->addValidation('Nome Completo', new TRequiredValidator);
        $popular->addValidation('Nome Popular', new TRequiredValidator);
        $fone->addValidation('Fone', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $email->addValidation('Email', new TRequiredValidator);
        //$status_pessoa->addValidation('Status', new TRequiredValidator);

        $estado_id->setChangeAction(new TAction(array($this, 'onStateChange')));
        $cidade_id->setChangeAction(new TAction(array($this, 'onCityChange')));
        $tipo_id->setChangeAction(new TAction(array($this, 'onTipoChange')));

        // validations
        $estado_id->addValidation('Estado', new TRequiredValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $tipo_id->addValidation('Tipo', new TRequiredValidator);
        //$logradouro_id->addValidation('Logradouro', new TRequiredValidator);
        $bairro_id->addValidation('Bairro', new TRequiredValidator);
        $n->addValidation('Nº', new TRequiredValidator);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave'], ['embedded' => $embedded ? '1' : '0']), 'fa:save green');

        if (!$this->embedded) {
            $this->form->addActionLink('Clear', new TAction([$this, 'onClear']), 'fa:eraser red');
            $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        }

        // add the form inside the page
        parent::add($this->form);
    }

    /**
     * method onSave
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {

        $param['status_pessoa'] = 21;
        $param['ck_pessoa'] = 1;

        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            if (empty($param['nome'])) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Nome'));
            }

            // read the form data and instantiates an Active Record

            if (isset($param['logradouro_id'])) {
                $consultaendereco = Endereco::where('logradouro_id', '=', $param['logradouro_id'])->where('n', '=', $param['n'])->where('bairro_id', '=', $param['bairro_id'])->first();

                if ($consultaendereco) {
                    $param['endereco_id'] = $consultaendereco->id;
                } else {
                    $endereco = new Endereco();
                    $endereco->fromArray($param);
                    $endereco->store();
                    $param['endereco_id'] = $endereco->id;
                }
            }

            $pessoa = new Pessoa();
            $pessoa->fromArray($param);

            // stores the object in the database
            $pessoa->store();

            PessoaContato::where('pessoa_id', '=', $pessoa->id)->delete();

            if (isset($param['fone']) and !empty($param['fone'])) {
                $pessoacontatofone = new PessoaContato();
                $pessoacontatofone->pessoa_id = $pessoa->id;
                $pessoacontatofone->tipo_contato_id = 101;
                $pessoacontatofone->contato = $param['fone'];
                $pessoacontatofone->status_contato_id = 1;
                $pessoacontatofone->store();
            }
            if (isset($param['email']) and !empty($param['email'])) {
                $pessoacontatoemail = new PessoaContato();
                $pessoacontatoemail->pessoa_id = $pessoa->id;
                $pessoacontatoemail->tipo_contato_id = 102;
                $pessoacontatoemail->contato = $param['email'];
                $pessoacontatoemail->status_contato_id = 1;
                $pessoacontatoemail->store();
            }

            $data = new stdClass;
            $data->id = $pessoa->id;
            TForm::sendData('form_pessoa', $data);

            if (!$param['embedded']) {
                TScript::create("Template.closeRightPanel()");

                $posAction = new TAction(array('PessoaDataGridView', 'onReload'));
                $posAction->setParameter('target_container', 'adianti_div_content');

                // shows the success message
                new TMessage('info', 'Record saved', $posAction);
            } else {
                TWindow::closeWindowByName('PessoaFormWindow');
            }

            TTransaction::close(); // close the transaction
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * method onEdit
     * Edit a record data
     */
    function onEdit($param)
    {
        try {
            if (isset($param['id'])) {
                // open a transaction with database 'samples'
                TTransaction::open('adea');

                // load the Active Record according to its ID
                $pessoa = new Pessoa($param['id']);

                $fone = PessoaContato::where('pessoa_id', '=', $pessoa->id)->where('tipo_contato_id', '=', 101)->first();
                if ($fone) {
                    $pessoa->fone = $fone->contato;
                }
                $email = PessoaContato::where('pessoa_id', '=', $pessoa->id)->where('tipo_contato_id', '=', 102)->first();
                if ($email) {
                    $pessoa->email = $email->contato;
                }

                $endereco = new Endereco($pessoa->endereco_id);

                // fill the form with the active record data*/
                $this->form->setData($pessoa);

                if ($endereco) {
                    // force fire events
                    $data = new stdClass;
                    $data->cep                  = $endereco->cep;
                    $data->estado_id            = $endereco->bairro->cidade->estado->id;
                    $data->cidade_id            = $endereco->bairro->cidade->id;
                    $data->tipo_id              = $endereco->logradouro->tipo->id;
                    $data->logradouro_id        = $endereco->logradouro_id;
                    $data->n                    = $endereco->n;
                    $data->bairro_id            = $endereco->bairro_id;
                    $data->ponto_referencia     = $endereco->ponto_referencia;
                    TForm::sendData('form_pessoa', $data);
                }

                // close the transaction
                TTransaction::close();
            } else {
                $this->onClear($param);
            }
        } catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear();
    }

    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
