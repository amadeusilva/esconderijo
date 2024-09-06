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
class EncontreiroForm extends TWindow
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
        parent::setTitle('Encontreiro');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Encontreiro');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_encontreiro');
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

        $casal_id = new TDBCombo('casal_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal_id');
        $casal_id->enableSearch();
        $casal_id->setSize('100%');

        $options = [1 => 'Sim', 2 => 'Não'];

        $camisa_encontro_br = new TRadioGroup('camisa_encontro_br');
        $camisa_encontro_br->setUseButton();
        $camisa_encontro_br->addItems($options);
        $camisa_encontro_br->setLayout('horizontal');
        $camisa_encontro_br->setValue(2);
        $camisa_encontro_br->setSize('100%');

        $camisa_encontro_cor = new TRadioGroup('camisa_encontro_cor');
        $camisa_encontro_cor->setUseButton();
        $camisa_encontro_cor->addItems($options);
        $camisa_encontro_cor->setLayout('horizontal');
        $camisa_encontro_cor->setValue(2);
        $camisa_encontro_cor->setSize('100%');

        $disponibilidade_nt = new TRadioGroup('disponibilidade_nt');
        $disponibilidade_nt->setUseButton();
        $disponibilidade_nt->addItems($options);
        $disponibilidade_nt->setLayout('horizontal');
        $disponibilidade_nt->setValue(2);
        $disponibilidade_nt->setSize('100%');

        $coordenar_s_n = new TRadioGroup('coordenar_s_n');
        $coordenar_s_n->setUseButton();
        $coordenar_s_n->addItems($options);
        $coordenar_s_n->setLayout('horizontal');
        $coordenar_s_n->setValue(2);
        $coordenar_s_n->setSize('100%');

        $funcao_id = new TRadioGroup('funcao_id');
        $funcao_id->setUseButton();
        $funcao_id->addItems($options);
        $funcao_id->setLayout('horizontal');
        $funcao_id->setValue(2);
        $funcao_id->setSize('100%');

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'abrev', 'id', $filterCirculo);
        $circulo_id->setSize('100%');

        $equipe_id = new TDBCombo('equipe_id', 'adea', 'Equipe', 'id', 'equipe', 'id');
        $equipe_id->enableSearch();
        $equipe_id->setSize('100%');

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
                new TLabel('Camisa Branca?')
            ],
            [
                $camisa_encontro_br
            ]
        );
        $row->layout = ['col-sm-12', 'col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Camisa Círculo?')
            ],
            [
                $camisa_encontro_cor
            ]
        );
        $row->layout = ['col-sm-12', 'col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Disponibilidade à noite?')
            ],
            [
                $disponibilidade_nt
            ]
        );
        $row->layout = ['col-sm-12', 'col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Deseja Coordenar?')
            ],
            [
                $coordenar_s_n
            ]
        );
        $row->layout = ['col-sm-12', 'col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Função')
            ],
            [
                $funcao_id
            ]
        );
        $row->layout = ['col-sm-12', 'col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Equipe'),
                $equipe_id
            ]
        );
        $row->layout = ['col-sm-12'];

        // validations
        //id`, `encontro_id`, `casal_pessoa_id`, `funcao_id`, `circulo_id`, `casal_pessoa_convite_id
        $encontro_id->addValidation('Encontro', new TRequiredValidator);
        $casal_id->addValidation('Casal', new TRequiredValidator);
        $circulo_id->addValidation('Círculo', new TRequiredValidator);
        $camisa_encontro_br->addValidation('Camisa Branca', new TRequiredValidator);
        $camisa_encontro_cor->addValidation('Camisa Círculo', new TRequiredValidator);
        $disponibilidade_nt->addValidation('Disponibilidade à noite', new TRequiredValidator);
        $coordenar_s_n->addValidation('Deseja Coordenar?', new TRequiredValidator);
        $funcao_id->addValidation('Função', new TRequiredValidator);
        $equipe_id->addValidation('Equipe', new TRequiredValidator);

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
    function onEdit($param)
    {
        try {
            if (isset($param['id'])) {
                $key = $param['id'];  // get the parameter
                TTransaction::open('adea');   // open a transaction with database 'samples'
                $montagem = new Montagem($key);        // instantiates object City
                $encontreiro = Encontreiro::where('montagem_id', '=', $montagem->id)->first();

                $montagem->camisa_encontro_br = $encontreiro->camisa_encontro_br;
                $montagem->camisa_encontro_cor = $encontreiro->camisa_encontro_cor;
                $montagem->disponibilidade_nt = $encontreiro->disponibilidade_nt;
                $montagem->coordenador_s_n = $encontreiro->coordenador_s_n;
                $montagem->equipe_id = $encontreiro->equipe_id;

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
            $montagem->tipo_id = 2;
            $montagem->conducao_propria_id = 0;
            $montagem->store(); // save the object

            $encontreiro = Encontreiro::where('montagem_id', '=', $montagem->id)->first();

            if (!$encontreiro) {
                $encontreiro = new Encontreiro();  // create an empty object
                $encontreiro->montagem_id = $montagem->id;
            }

            $encontreiro->fromArray((array) $data); // load the object with data
            $encontreiro->store(); // save the object

            $encontreiro_equipe = EncontreiroEquipe::where('encontreiro_id', '=', $encontreiro->id)->first();

            if (!$encontreiro_equipe) {
                $encontreiro_equipe = new EncontreiroEquipe();  // create an empty object
                $encontreiro_equipe->encontreiro_id = $encontreiro->id;
            }

            $encontreiro_equipe->fromArray((array) $data); // load the object with data
            $encontreiro_equipe->store(); // save the object

            // fill the form with the active record data
            $this->form->setData($encontreiro);

            TTransaction::close();  // close the transaction

            // fill the form with the active record data
            $posAction = new TAction(array('EncontreiroDataGrid', 'onReload'));

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
