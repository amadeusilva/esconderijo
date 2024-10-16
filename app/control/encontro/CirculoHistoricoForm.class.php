<?php

use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TNumeric;
use Adianti\Widget\Form\TText;
use Svg\Tag\Circle;

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
class CirculoHistoricoForm extends TWindow
{
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
        parent::setTitle('Histórico de Círculo');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('CirculoHistorico');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_circulo_historico');
        $this->form->setClientValidation(true);

        //dados
        $id = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');

        $user_sessao_id = new TDBCombo('user_sessao_id', 'adea', 'SystemUser', 'id', 'name', 'id');
        //$casal_id->enableSearch();
        $user_sessao_id->setEditable(FALSE);
        $user_sessao_id->setSize('100%');

        $casal_id = new TDBCombo('casal_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal_id');
        //$casal_id->enableSearch();
        $casal_id->setEditable(FALSE);
        $casal_id->setSize('100%');

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterCirculo);
        $circulo_id->setSize('100%');

        $filterMotivo = new TCriteria;
        $filterMotivo->add(new TFilter('lista_id', '=', '21'));
        $motivo_id = new TDBCombo('motivo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterMotivo);
        $motivo_id->setChangeAction(new TAction(array($this, 'onMotivo')));
        $motivo_id->setSize('100%');

        $dt_historico                 = new TDate('dt_historico');
        $dt_historico->setMask('dd/mm/yyyy');
        $dt_historico->setDatabaseMask('yyyy-mm-dd');
        $dt_historico->setSize('100%');

        $obs_motivo                 = new TText('obs_motivo');
        $obs_motivo->forceUpperCase();
        $obs_motivo->placeholder = 'Descreva o motivo...';
        $obs_motivo->setSize('100%');

        // define some properties for the form fields

        $row = $this->form->addFields(
            [new TLabel('Cod.:'),    $id],
            [
                new TLabel('Cadastrado por:'),
                $user_sessao_id
            ]
        );
        $row->layout = ['col-sm-3', 'col-sm-9'];

        $row = $this->form->addFields(
            [
                new TLabel('Casal'),
                $casal_id
            ],
            [
                new TLabel('Círculo'),
                $circulo_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Motivo'),
                $motivo_id
            ],
            [
                new TLabel('Data'),
                $dt_historico
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Detalhes do Motivo'),
                $obs_motivo
            ]
        );
        $row->layout = ['col-sm-12'];

        // validations
        $user_sessao_id->addValidation('Registrado por:', new TRequiredValidator);
        $casal_id->addValidation('Casal', new TRequiredValidator);
        $circulo_id->addValidation('Círculo', new TRequiredValidator);
        $motivo_id->addValidation('Motivo', new TRequiredValidator);
        $dt_historico->addValidation('Data', new TRequiredValidator);
        $obs_motivo->addValidation('Destalhes do Motivo', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    public static function onMotivo($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['motivo_id'])) {
                $buscamotivo = ListaItens::find($param['motivo_id']);
                if ($buscamotivo) {
                    $nometela = new stdClass;
                    $nometela->obs_motivo        = $buscamotivo->obs;

                    TForm::sendData('form_circulo_historico', $nometela);
                }
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try {
            if (isset($param['id']) and !empty($param['id'])) {

                TTransaction::open('adea');   // open a transaction with database 'samples'

                $circulo_historico = new CirculoHistorico($param['id']);

                if ($circulo_historico) {
                    $this->form->setData($circulo_historico);   // fill the form with the active record data
                }

                TTransaction::close();           // close the transaction
            } else {
                $this->onClear($param);
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
    function onSave()
    {
        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            $this->form->validate(); // run form validation

            $data = $this->form->getData(); // get form data as array

            $object = new CirculoHistorico();  // create an empty object
            $object->fromArray((array) $data); // load the object with data
            $object->store(); // save the object

            // fill the form with the active record data
            $this->form->setData($object);

            TTransaction::close();  // close the transaction

            self::onClose();

            $pos_action = new TDataGridAction(['CasalPanel', 'onView'],   ['relacao_id' => $object->casal_id, 'register_state' => 'false']);

            new TMessage('info', 'Registro Salvo!', $pos_action); // success message
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData($this->form->getData()); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form
     */
    public function onClear($param)
    {

        $this->form->clear();

        //$form_vazio = new stdClass;
        //$form_vazio->tipo_enc_id = $param['tipo_enc_id'];
        TForm::sendData('form_circulo_historico', $param);
    }

    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
