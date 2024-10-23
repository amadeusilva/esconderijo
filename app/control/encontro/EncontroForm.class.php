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
class EncontroForm extends TWindow
{
    use ControleEndereco;
    protected $form; // form

    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;

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
        parent::setTitle('Encontro');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Encontro');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_encontro');
        $this->form->setClientValidation(true);

        //dados
        $id = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');

        //$num = new TNumeric('num', 0, '', '', true);
        $num = new TEntry('num');
        $num->setSize('100%');

        $filter = new TCriteria;
        $filter->add(new TFilter('lista_id', '=', '4'));
        $evento_id = new TDBCombo('evento_id', 'adea', 'ListaItens', 'id', 'abrev', 'item', $filter);
        $evento_id->setSize('100%');

        $filterLocal = new TCriteria;
        $filterLocal->add(new TFilter('tipo_pessoa', '=', '2'));
        $local_id = new TDBCombo('local_id', 'adea', 'Pessoa', 'id', '{nome} ({popular})', 'id', $filterLocal);
        $local_id->enableSearch();
        $local_id->setSize('100%');
        $local_id->placeholder = 'Buscar Instituição/Escola...)';

        $dt_inicial                 = new TDate('dt_inicial');
        $dt_inicial->setMask('dd/mm/yyyy');
        $dt_inicial->setDatabaseMask('yyyy-mm-dd');
        $dt_inicial->setSize('100%');

        $dt_final                 = new TDate('dt_final');
        $dt_final->setMask('dd/mm/yyyy');
        $dt_final->setDatabaseMask('yyyy-mm-dd');
        $dt_final->setSize('100%');

        $tema                 = new TEntry('tema');
        $tema->setSize('100%');
        $tema->forceUpperCase();

        $divisa                 = new TText('divisa');
        $divisa->setSize('100%');

        $cantico_id = new TDBCombo('cantico_id', 'adea', 'Hinario', 'id', 'titulo', 'titulo');
        $cantico_id->enableSearch();
        $cantico_id->setSize('calc(100% - 30px)');
        //$cantico_id->setSize('100%');

        $button = new TActionLink('', new TAction(['HinarioForm', 'onClear']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = _t('New');
        $cantico_id->after($button);

        $livrao_pdf      = new TFile('livrao_pdf');
        $livrao_pdf->setAllowedExtensions(['pdf']);
        $livrao_pdf->setSize('100%');

        // enable progress bar, preview
        $livrao_pdf->enableFileHandling();
        $livrao_pdf->enablePopover();

        // define some properties for the form fields

        $row = $this->form->addFields(
            [new TLabel('Cod.:'),    $id],
            [
                new TLabel('Número'),
                $num
            ],
            [
                new TLabel('Evento'),
                $evento_id
            ]
        );
        $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Data Inicial'),
                $dt_inicial
            ],
            [
                new TLabel('Data Final'),
                $dt_final
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Local'),
                $local_id
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Tema'),
                $tema
            ],
            [
                new TLabel('Cântico'),
                $cantico_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Divisa'),
                $divisa
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Digitalização (PDF)'),
                $livrao_pdf
            ]
        );
        $row->layout = ['col-sm-12'];

        // validations
        // encontro`(`id`, `num`, `evento_id`, `local_id`, `dt_inicial`, `dt_final`, `tema`, `divisa`, `cantico_id
        $num->addValidation('Número', new TRequiredValidator);
        $evento_id->addValidation('Evento', new TRequiredValidator);
        $local_id->addValidation('Local', new TRequiredValidator);
        $dt_inicial->addValidation('Data inicial', new TRequiredValidator);
        $dt_final->addValidation('Data Final', new TRequiredValidator);
        $tema->addValidation('Tema', new TRequiredValidator);
        $divisa->addValidation('Divisa', new TRequiredValidator);
        $cantico_id->addValidation('Cântico', new TRequiredValidator);
        //$livrao_pdf->addValidation('Digitalização (PDF)', new TRequiredValidator);

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

            if (isset($data->livrao_pdf) and empty($data->livrao_pdf)) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Digitalização (PDF)'));
            }

            $object = new Encontro();  // create an empty object
            $object->fromArray((array) $data); // load the object with data
            $object->store(); // save the object

            // copy file to target folder
            $this->saveFile($object, $data, 'livrao_pdf', 'files/documents/livrao');

            // fill the form with the active record data
            $this->form->setData($object);

            TTransaction::close();  // close the transaction

            // fill the form with the active record data
            $posAction = new TAction(array('EncontroDataGrid', 'onReload'));

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
