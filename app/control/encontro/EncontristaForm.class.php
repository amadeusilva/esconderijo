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
class EncontristaForm extends TWindow
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
        parent::setTitle('Encontrista');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Encontrista');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_encontrista');
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

        $casal_id = new TDBCombo('casal_id', 'adea', 'ViewCasal', 'relacao_id', 'casal', 'relacao_id');
        $casal_id->enableSearch();
        $casal_id->setSize('100%');
        $casal_id->setChangeAction(new TAction(array($this, 'onEncontrista')));

        $options = [1 => 'Sim', 2 => 'Não'];
        $secretario_s_n = new TRadioGroup('secretario_s_n');
        $secretario_s_n->setUseButton();
        $secretario_s_n->addItems($options);
        $secretario_s_n->setLayout('horizontal');
        $secretario_s_n->setValue(2);
        $secretario_s_n->setSize('100%');

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterCirculo);
        $circulo_id->setSize('100%');

        $casal_convite_id = new TDBCombo('casal_convite_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal_id');
        $casal_convite_id->enableSearch();
        $casal_convite_id->setSize('100%');

        // define some properties for the form fields

        $row = $this->form->addFields(
            [new TLabel('Cod.:'),    $id]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Encontro'),
                $encontro_id
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Casal'),
                $casal_id
            ]
        );

        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Círculo'),
                $circulo_id
            ]
        );

        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Secretário?')],[
                $secretario_s_n
            ]
        );

        $row->layout = ['col-sm-12', 'col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Convite'),
                $casal_convite_id
            ]
        );

        $row->layout = ['col-sm-12'];

        // validations
        //id`, `encontro_id`, `casal_pessoa_id`, `funcao_id`, `circulo_id`, `casal_pessoa_convite_id
        $encontro_id->addValidation('Encontro', new TRequiredValidator);
        $casal_id->addValidation('Casal', new TRequiredValidator);
        $secretario_s_n->addValidation('Secretário', new TRequiredValidator);
        $circulo_id->addValidation('Círculo', new TRequiredValidator);
        //$casal_convite_id->addValidation('Convite', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    public static function onEncontrista($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['casal_id']) and !empty($param['encontro_id'])) {
                $montagem = Montagem::where('casal_id', '=', $param['casal_id'])->where('encontro_id', '=', $param['encontro_id'])->where('tipo_id', '=', 1)->first();
                if ($montagem) {
                    AdiantiCoreApplication::loadPage(__CLASS__, 'onEdit', ['id' => $montagem->id]);
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
            if (isset($param['id'])) {
                $key = $param['id'];  // get the parameter
                TTransaction::open('adea');   // open a transaction with database 'samples'
                $montagem = new Montagem($key);        // instantiates object City
                $encontrista = Encontrista::where('montagem_id', '=', $montagem->id)->first();

                $montagem->secretario_s_n = $encontrista->secretario_s_n;
                $montagem->casal_convite_id = $encontrista->casal_convite_id;

                $this->form->setData($montagem);   // fill the form with the active record data
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

            $montagem = new Montagem();  // create an empty object
            $montagem->fromArray((array) $data); // load the object with data
            $montagem->tipo_encontr_ista_eiro = 1;
            $montagem->conducao_propria_id = 0;
            $montagem->store(); // save the object

            Encontrista::where('montagem_id', '=', $montagem->id)->delete();

            $encontrista = new Encontrista();  // create an empty object
            $encontrista->montagem_id = $montagem->id;
            $encontrista->fromArray((array) $data); // load the object with data
            $encontrista->store(); // save the object

            // fill the form with the active record data
            $this->form->setData($encontrista);

            TTransaction::close();  // close the transaction

            // fill the form with the active record data
            $posAction = new TAction(array('EncontristaDataGrid', 'onReload'));

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
