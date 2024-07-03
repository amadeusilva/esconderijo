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
class EddPessoaContato extends TWindow
{
    protected $form; // form

    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();
        parent::setModal(true);
        parent::removePadding();
        parent::setSize(350, null);
        parent::setTitle('Contato');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('PessoaContato');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_PessoaContato');
        $this->form->setClientValidation(true);

        // create the form fields
        $id       = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $row = $this->form->addFields([new TLabel('Cod.:', 'red'), $id]);
        $row->layout = ['col-sm-12'];

        $pessoa_id       = new THidden('pessoa_id');
        $pessoa_id->setValue($param['pessoa_id']);
        $this->form->addFields([$pessoa_id]);

        $filter = new TCriteria;
        $filter->add(new TFilter('lista_id', '=', '16'));
        $tipo_contato_id = new TDBCombo('tipo_contato_id', 'adea', 'ListaItens', 'id', 'item', 'item', $filter);
        //$tipo_contato_id->enableSearch();
        $tipo_contato_id->setValue($param['tipo_contato_id']);
        $tipo_contato_id->setSize('100%');
        $tipo_contato_id->setEditable(FALSE);
        $row = $this->form->addFields([new TLabel('Tipo', 'red'), $tipo_contato_id]);
        $row->layout = ['col-sm-12'];
        $tipo_contato_id->addValidation('Tipo', new TRequiredValidator);

        if ($param['tipo_contato_id'] == 101) {
            $contato           = new TEntry('contato');
            $contato->setMask('(99) 99999-9999');
            $contato->setSize('100%');
            $row = $this->form->addFields([new TLabel('Fone', 'red'), $contato]);
            $row->layout = ['col-sm-12'];
            $contato->addValidation('Fone', new TRequiredValidator);
        } else if ($param['tipo_contato_id'] == 102) {
            $contato           = new TEntry('contato');
            $contato->forceLowerCase();
            $contato->setSize('100%');
            $row = $this->form->addFields([new TLabel('Email', 'red'), $contato]);
            $row->layout = ['col-sm-12'];
            $contato->addValidation('Email', new TRequiredValidator);
            $contato->addValidation('Email', new TEmailValidator);
        }

        $status_contato_id = new TCombo('status_contato_id');
        $itens_status_contato = [1 => 'Sim', 2 => 'Não '];
        $status_contato_id->addItems($itens_status_contato);
        $status_contato_id->setSize('100%');

        if (empty($param['id'])) {
            $status_contato_id->setEditable(FALSE);
            $status_contato_id->setValue(1);
        }
        $row = $this->form->addFields([new TLabel('Ativo?', 'red'), $status_contato_id]);
        $row->layout = ['col-sm-12'];
        $status_contato_id->addValidation('Ativo', new TRequiredValidator);

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

                $key = $param['id'];  // get the parameter
                $object = new PessoaContato($key);        // instantiates object City
                $this->form->setData($object);   // fill the form with the active record data

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

            $pessoa_painel = TSession::getValue('pessoa_painel');
            $data = $this->form->getData(); // get form data as array
            $data->pessoa_id = $pessoa_painel->id;

            $object = new PessoaContato();  // create an empty object
            $object->fromArray((array) $data); // load the object with data
            $object->store(); // save the object

            // fill the form with the active record data
            $this->form->setData($object);

            self::onClose();

            $posAction = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => $object->pessoa_id, 'register_state' => 'false']);

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
