<?php

use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TNumeric;
use Adianti\Widget\Form\TText;

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
class EncontroCirculosForm extends TWindow
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
        parent::setSize(400, null);
        parent::setTitle('Círculos');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('EncontroCirculos');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_EncontroCirculos');
        $this->form->setClientValidation(true);

        //dados
        $id = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');

        $filter = new TCriteria;
        $filter->add(new TFilter('evento_id', '=', '701'));
        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id', $filter);
        $encontro_id->enableSearch();
        $encontro_id->setSize('100%');

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterCirculo);
        $circulo_id->setSize('100%');
        $circulo_id->setChangeAction(new TAction(array($this, 'onCoordenador'))); //$circulo_id->setValue(16);

        $nome_circulo  = new TDBEntry('nome_circulo', 'adea', 'EncontroCirculos', 'nome_circulo');
        $nome_circulo->placeholder = 'digite o nome do Círculo';
        $nome_circulo->forceUpperCase();
        $nome_circulo->setSize('100%');

        $filterCoordenador = new TCriteria;
        $filterCoordenador->add(new TFilter('id', '<', '0'));
        $casal_coord_id = new TDBCombo('casal_coord_id', 'adea', 'ViewCasalCirculo', 'casal_id', '{casal} ({Casamento})', 'casal_id', $filterCoordenador);
        $casal_coord_id->enableSearch();
        $casal_coord_id->setSize('100%');

        $casal_sec_id = new TDBCombo('casal_sec_id', 'adea', 'ViewEncontrista', 'casal_id', '{casal} ({Casamento})', 'casal_id');
        $casal_sec_id->enableSearch();
        $casal_sec_id->setSize('100%');

        $row = $this->form->addFields(
            [
                new TLabel('Cod.:'),
                $id
            ],
            [
                new TLabel('Encontro'),
                $encontro_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Círculo'),
                $circulo_id
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Nome do Círculo'),
                $nome_circulo
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Casal Coordenador'),
                $casal_coord_id
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Casal Secretaria'),
                $casal_sec_id
            ]
        );
        $row->layout = ['col-sm-12'];

        // validations
        $encontro_id->addValidation('Encontro', new TRequiredValidator);
        $circulo_id->addValidation('Círculo', new TRequiredValidator);
        $nome_circulo->addValidation('Nome', new TRequiredValidator);
        $casal_coord_id->addValidation('Coordenador', new TRequiredValidator);
        $casal_sec_id->addValidation('Secretário', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    /**
     * Action to be executed when the user changes the state
     * @param $param Action parameters
     */
    public static function onCoordenador($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['circulo_id'])) {
                $criteria = TCriteria::create(['circulo_id' => $param['circulo_id']]);

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_EncontroCirculos', 'casal_coord_id', 'adea', 'ViewCasalCirculo', 'casal_id', '{casal} ({Casamento})', 'casal_id', $criteria, TRUE);
            } else {
                TCombo::clearField('form_EncontroCirculos', 'casal_coord_id');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit($param)
    {
        try {
            if (isset($param['key'])) {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('adea'); // open a transaction
                $object = new EncontroCirculos($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form

                // force fire events
                TForm::sendData('form_EncontroCirculos', $object);

                TTransaction::close(); // close the transaction

            } else {
                $this->form->clear();
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

            $object = new EncontroCirculos();  // create an empty object
            $object->fromArray((array) $data); // load the object with data
            $object->store(); // save the object

            // fill the form with the active record data
            $this->form->setData($object);

            TTransaction::close();  // close the transaction

            // fill the form with the active record data
            $posAction = new TAction(array('EncontroCirculosDataGridView', 'onReload'));

            // show the message dialog
            new TMessage('info', 'Registro Salvo com Sucesso!', $posAction);
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
