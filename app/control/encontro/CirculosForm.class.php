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
class CirculosForm extends TWindow
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
        parent::setTitle('Encontristas - Círculos');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Encontrista');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_CirculosForm');
        $this->form->setClientValidation(true);

        $filter = new TCriteria;
        $filter->add(new TFilter('evento_id', '=', '701'));
        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id', $filter);
        $encontro_id->enableSearch();
        $encontro_id->setSize('100%');

        $filterCasais = new TCriteria;
        $filterCasais->add(new TFilter('circulo_id', '=', 17));
        $casal_id  = new TDBMultiSearch('casal_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal', $filterCasais);
        $casal_id->setMask('{casal} ({Casamento})');
        $casal_id->setMinLength(1);
        $casal_id->setSize('100%');

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterCirculo);
        $circulo_id->setSize('100%');
        $circulo_id->setChangeAction(new TAction(array($this, 'onCoordenador')));

        $nome_circulo  = new TDBEntry('nome_circulo', 'adea', 'EncontroCirculos', 'nome_circulo');
        $nome_circulo->placeholder = 'digite o nome do Círculo';
        $nome_circulo->forceUpperCase();
        $nome_circulo->setSize('100%');

        $filterCoordenador = new TCriteria;
        $filterCoordenador->add(new TFilter('id', '<', '0'));
        $casal_coord_id = new TDBCombo('casal_coord_id', 'adea', 'ViewCasalCirculo', 'casal_id', '{casal} ({Casamento})', 'casal_id', $filterCoordenador);
        $casal_coord_id->enableSearch();
        $casal_coord_id->setSize('100%');

        $casal_sec_id = new TDBCombo('casal_sec_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal_id');
        $casal_sec_id->enableSearch();
        $casal_sec_id->setSize('100%');

        $row = $this->form->addFields(
            [
                new TLabel('Encontro'),
                $encontro_id
            ],
            [
                new TLabel('Círculo'),
                $circulo_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

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

        $row = $this->form->addFields(
            [
                new TLabel('Casais Membros'),
                $casal_id
            ]
        );
        $row->layout = ['col-sm-12'];

        /*
        $row = $this->form->addFields(
            [
                new TLabel('Secretário?')
            ],
            [
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
        */

        // validations
        //id`, `encontro_id`, `casal_pessoa_id`, `funcao_id`, `circulo_id`, `casal_pessoa_convite_id
        $encontro_id->addValidation('Encontro', new TRequiredValidator);
        $casal_id->addValidation('Casal', new TRequiredValidator);
        //$secretario_s_n->addValidation('Secretário', new TRequiredValidator);
        $circulo_id->addValidation('Círculo', new TRequiredValidator);
        //$casal_convite_id->addValidation('Convite', new TRequiredValidator);

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
                TDBCombo::reloadFromModel('form_CirculosForm', 'casal_coord_id', 'adea', 'ViewCasalCirculo', 'casal_id', '{casal} ({Casamento})', 'casal_id', $criteria, TRUE);
            } else {
                TCombo::clearField('form_CirculosForm', 'casal_coord_id');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onEncontrista($param)
    {
        try {
            TTransaction::open('adea');
            //if (!empty($param['casal_id']) and !empty($param['encontro_id'])) {
            if (!empty($param['casal_id'])) {
                //$montagem = Montagem::where('casal_id', '=', $param['casal_id'])->where('encontro_id', '=', $param['encontro_id'])->where('tipo_id', '=', 1)->first();
                $montagem = Montagem::where('casal_id', '=', $param['casal_id'])->where('tipo_id', '=', 1)->first();
                if ($montagem) {
                    AdiantiCoreApplication::loadPage(__CLASS__, 'onEdit', ['id' => $montagem->id]);
                }
            } else {
                $this->form->clear(true);
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

            $encontro_circulos = new EncontroCirculos;
            //$encontro_circulos->encontro_id = $data->encontro_id;
            //$encontro_circulos->circulo_id = $data->circulo_id;
            //$encontro_circulos->nome_circulo = $data->nome_circulo;
            //$encontro_circulos->casal_coord_id = $data->casal_coord_id;
            //$encontro_circulos->casal_sec_id = $data->casal_sec_id;
            $encontro_circulos->fromArray((array) $data); // load the object with data
            $encontro_circulos->store(); // save the object

            foreach ($data->casal_id as $casal_id) {
                $busca_montagem = Montagem::where('tipo_id', '=', 1)->where('encontro_id', '=', $data->encontro_id)->where('casal_id', '=', $casal_id)->first();
                $busca_montagem->circulo_id = $data->circulo_id;
                $busca_montagem->store(); // save the object

                if ($data->casal_sec_id == $casal_id) {
                    $atualiza_secre = Encontrista::where('montagem_id', '=', $busca_montagem->id)->where('casal_id', '=', $casal_id)->first();
                    $atualiza_secre->secretario_s_n = 1;
                    $atualiza_secre->store(); // save the object
                }

                $historico_circulo = CirculoHistorico::where(
                    'casal_id',
                    '=',
                    $casal_id
                )->orderBy('id', 'asc')->first();

                $historico_circulo->user_sessao_id = TSession::getValue('userid');
                $historico_circulo->circulo_id = $data->circulo_id;
                $historico_circulo->store(); // save the object
            }

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
