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
class EddEndereco extends TWindow
{
    use ControleEndereco;
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
        parent::setTitle('Endereço');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Endereco');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_endereco');
        $this->form->setClientValidation(true);

        //dados de endereço
        $cep                 = new TEntry('cep');
        $cep  = new TDBEntry('cep', 'adea', 'Endereco', 'cep');
        $cep->setMask('99.999-999');
        $cep->setExitAction(new TAction(array($this, 'onCEPAction')));

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

        // define some properties for the form fields
        $tipo_id->setEditable(FALSE);
        $cep->setSize('100%');
        $estado_id->setSize('100%');
        $cidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $logradouro_id->setSize('100%');
        $bairro_id->setSize('100%');
        $n->setSize('100%');
        $ponto_referencia->setSize('100%');

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

        $estado_id->setChangeAction(new TAction(array($this, 'onStateChange')));
        $cidade_id->setChangeAction(new TAction(array($this, 'onCityChange')));
        $tipo_id->setChangeAction(new TAction(array($this, 'onTipoChange')));

        // validations
        $estado_id->addValidation('Estado', new TRequiredValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $tipo_id->addValidation('Tipo', new TRequiredValidator);
        $logradouro_id->addValidation('Logradouro', new TRequiredValidator);
        $bairro_id->addValidation('Bairro', new TRequiredValidator);
        $n->addValidation('Nº', new TRequiredValidator);

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

                $object = Endereco::where('id', '=', $pessoa->endereco_id)->first();        // instantiates object City

                if ($object) {
                    $this->form->setData($object);   // fill the form with the active record data

                    // force fire events
                    $data = new stdClass;
                    $data->estado_id            = $object->Bairro->Cidade->Estado->id;
                    $data->cidade_id            = $object->Bairro->Cidade->id;
                    $data->tipo_id              = $object->Logradouro->Tipo->id;
                    $data->logradouro_id        = $object->logradouro_id;
                    $data->bairro_id            = $object->bairro_id;
                    //TDBCombo::disableField('form_endereco', 'tipo_id');
                    TForm::sendData('form_endereco', $data);
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
