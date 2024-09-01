<?php

use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;
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
class HinarioForm extends TWindow
{
    protected $form; // form
    private $contacts;

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
        parent::setTitle('Hinário');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Hinario');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_hinario');
        $this->form->setClientValidation(true);

        //dados
        $id       = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');

        $ordem                 = new TEntry('ordem');
        $ordem->setSize('100%');

        $titulo = new TEntry('titulo');
        $titulo->setSize('100%');

        $row = $this->form->addFields(
            [new TLabel('Cod.:'),    $id],
            [
                new TLabel('Ordem'),
                $ordem
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Título'),
                $titulo
            ]
        );
        $row->layout = ['col-sm-12'];

        $label = new TLabel('<br>Partes', '#62a8ee', 14, 'b');
        $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
        $this->form->addContent([$label]);

        $filter = new TCriteria;
        $filter->add(new TFilter('lista_id', '=', '13'));
        $tipo_parte = new TDBCombo('tipo_parte[]', 'adea', 'ListaItens', 'id', 'item', 'item', $filter);
        //$contact_type->enableSearch();
        $tipo_parte->setSize('100%');

        $parte = new TEntry('parte[]');
        $parte->setSize('100%');

        $this->contacts = new TFieldList;

        $this->contacts = new TFieldList;
        $this->contacts->addField('<b>Tipo</b>', $tipo_parte, ['width' => '50%']);
        $this->contacts->addField('<b>Parte</b>', $parte, ['width' => '50%']);
        $this->form->addField($tipo_parte);
        $this->form->addField($parte);
        $this->contacts->enableSorting();

        $row = $this->form->addFields(
            [
                //new TLabel('Partes'),
                $this->contacts
            ]
        );
        $row->layout = ['col-sm-12'];

        // validations
        $titulo->addValidation('Título', new TRequiredValidator);

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
    function onEdite2($param)
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

            echo '<pre>';
            print_r($data);
            echo '</pre>';
            exit;

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
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear();

        $this->contacts->addHeader();
        $this->contacts->addDetail(new stdClass);
        $this->contacts->addCloneAction();
    }

    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
