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
        $filterTipo->add(new TFilter('id', '!=', '77'));
        $filterTipo->add(new TFilter('lista_id', '=', '15'));
        $tipo_pessoa   = new TDBCombo('tipo_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterTipo);

        $cpf_cnpj       = new TEntry('cpf_cnpj');
        $cpf_cnpj->setMask('99.999.999/9999-99');
        $nome           = new TEntry('nome');
        $popular        = new TEntry('popular');
        $fone           = new TEntry('fone');
        $fone->setMask('(99) 99999-9999');
        $email          = new TEntry('email');

        $filterStatusPessoa = new TCriteria;
        $filterStatusPessoa->add(new TFilter('lista_id', '=', '5'));
        $status_pessoa   = new TDBCombo('status_pessoa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterStatusPessoa);

        //endereço da pessoa
        //`cep`, `logradouro_id`, `n`, `bairro_id`, `ponto_referencia`
        $cep                 = new TEntry('cep');
        $cep->setMask('99.999-999');
        $estado_id       = new TDBCombo('estado_id', 'adea', 'Estado', 'id', 'estado', 'estado');
        $estado_id->enableSearch();

        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $cidade_id = new TDBCombo('cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $filter);
        $cidade_id->enableSearch();

        $filterItem = new TCriteria;
        $filterItem->add(new TFilter('lista_id', '=', '1'));
        $tipo_id       = new TDBCombo('tipo_id', 'adea', 'ListaItens', 'id', 'item', 'item', $filterItem);
        $tipo_id->enableSearch();

        $logradouro_id = new TDBCombo('logradouro_id', 'adea', 'Logradouro', 'id', 'logradouro', 'logradouro', $filter);
        $logradouro_id->enableSearch();

        $bairro_id = new TDBCombo('bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $filter);
        $bairro_id->enableSearch();

        $n                  = new TEntry('n');
        $ponto_referencia   = new TEntry('ponto_referencia');

        // define some properties for the form fields
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `status_pessoa`, `ck_pessoa`
        //id`, `pessoa_id`, `genero`, `dt_nascimento`, `profissao_id`, `tm_camisa`
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $cpf_cnpj->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $status_pessoa->setSize('100%');
        $tipo_pessoa->setSize('100%');

        $cep->setSize('100%');
        $estado_id->setSize('100%');
        $cidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $logradouro_id->setSize('100%');
        $bairro_id->setSize('100%');
        $n->setSize('100%');
        $ponto_referencia->setSize('100%');

        //Dados
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `cep`,
        //`logradouro_id`, `n`, `bairro_id`, `ponto_referencia`, `status_pessoa`, `ck_pessoa`
        $this->form->appendPage('Dados');
        $this->form->addFields(
            [new TLabel('Cod.')],
            [$id],
            [new TLabel('CNPJ')],
            [$cpf_cnpj]
        );

        $this->form->addFields(
            [new TLabel('Razão Social')],
            [$nome],
            [new TLabel('Tipo')],
            [$tipo_pessoa]
        );

        $this->form->addFields(
            [new TLabel('Nome Fantasia')],
            [$popular],
            [new TLabel('Fone')],
            [$fone]
        );

        $this->form->addFields(
            [new TLabel('Email')],
            [$email],
            [new TLabel('Status')],
            [$status_pessoa]
        );

        //Endereço
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `cep`,
        //`logradouro_id`, `n`, `bairro_id`, `ponto_referencia`, `status_pessoa`, `ck_pessoa`

        $this->form->appendPage('Endereço');
        $this->form->addFields([new TLabel('CEP')],    [$cep]);

        $this->form->addFields(
            [new TLabel('Estado')],
            [$estado_id],
            [new TLabel('Cidade')],
            [$cidade_id]
        );

        $this->form->addFields(
            [new TLabel('Tipo')],
            [$tipo_id],
            [new TLabel('Endereço')],
            [$logradouro_id]
        );

        $this->form->addFields(
            [new TLabel('Nº')],
            [$n],
            [new TLabel('Bairro')],
            [$bairro_id]
        );

        $this->form->addFields([new TLabel('Ponto de Referência')],    [$ponto_referencia]);

        $estado_id->setChangeAction(new TAction(array($this, 'onStateChange')));
        $cidade_id->setChangeAction(new TAction(array($this, 'onCityChange')));
        $tipo_id->setChangeAction(new TAction(array($this, 'onTipoChange')));

        /*$this->form->appendPage('Skills');
        $skill_list = new TDBCheckGroup('skill_list', 'samples', 'Skill', 'id', 'name');
        $this->form->addFields( [ new TLabel('Skill') ],     [ $skill_list ] );
        
        $this->form->appendPage('Contacts');
        $contact_type = new TCombo('contact_type[]');
        $contact_type->setSize('100%');
        $contact_type->addItems( ['email' => 'E-mail',
                                  'phone' => 'Phone' ]);
        $contact_value = new TEntry('contact_value[]');
        $contact_value->setSize('100%');
        
        $this->contacts = new TFieldList;
        $this->contacts->addField( '<b>Type</b>', $contact_type, ['width' => '50%']);
        $this->contacts->addField( '<b>Value</b>', $contact_value, ['width' => '50%']);
        $this->form->addField($contact_type);
        $this->form->addField($contact_value);
        $this->contacts->enableSorting();
        
        $this->form->addContent( [ new TLabel('Contacts') ], [ $this->contacts ] );*/

        $cpf_cnpj->addValidation('CNPJ', new TCNPJValidator);
        $nome->addValidation('Nome Completo', new TRequiredValidator);
        $popular->addValidation('Nome Popular', new TRequiredValidator);
        $fone->addValidation('Fone', new TRequiredValidator);
        $email->addValidation('Email', new TRequiredValidator);
        $status_pessoa->addValidation('Status', new TRequiredValidator);

        $estado_id->addValidation('Estado', new TRequiredValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $tipo_id->addValidation('Tipo', new TRequiredValidator);
        $logradouro_id->addValidation('Logradouro', new TRequiredValidator);
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
