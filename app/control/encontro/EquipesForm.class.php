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
class EquipesForm extends TWindow
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
        parent::setTitle('Encontreiros - Equipes');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Encontreiro');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_encontreiro');
        $this->form->setClientValidation(true);

        //dados 
        //$id = new TEntry('id');
        //$id->setEditable(FALSE);
        //$id->setSize('100%');

        $filter = new TCriteria;
        $filter->add(new TFilter('evento_id', '=', '701'));
        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id', $filter);
        $encontro_id->enableSearch();
        $encontro_id->setSize('100%');
        $encontro_id->setValue(7);

        $coordenador_id = new TDBCombo('coordenador_id', 'adea', 'ViewEncontrista', 'casal_id', '{casal} ({Casamento})', 'casal');
        $coordenador_id->enableSearch();
        $coordenador_id->setSize('100%');
        //$casal_id->setChangeAction(new TAction(array($this, 'onEncontrista')));

        //$filterCasais = new TCriteria;
        //$filterCasais->add(new TFilter('circulo_id', '=', 17));
        $casal_id  = new TDBMultiSearch('casal_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal');
        $casal_id->setMask('{casal} ({Casamento})');
        $casal_id->setMinLength(1);
        $casal_id->setSize('100%');

        //$options = [1 => 'Sim', 2 => 'Não'];
        //$secretario_s_n = new TRadioGroup('secretario_s_n');
        //$secretario_s_n->setUseButton();
        //$secretario_s_n->addItems($options);
        //$secretario_s_n->setLayout('horizontal');
        //$secretario_s_n->setValue(2);
        //$secretario_s_n->setSize('100%');

        //$filterCirculo = new TCriteria;
        //$filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $equipe_id = new TDBCombo('equipe_id', 'adea', 'Equipe', 'id', 'equipe', 'id');
        $equipe_id->setSize('100%');
        $equipe_id->enableSearch();
        //$circulo_id->setValue(16);

        //$casal_convite_id = new TDBCombo('casal_convite_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal_id');
        //$casal_convite_id->enableSearch();
        //$casal_convite_id->setSize('100%');

        // define some properties for the form fields

        //$row = $this->form->addFields(
        //    [new TLabel('Cod.:'),    $id]
        //);
        //$row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Encontro'),
                $encontro_id
            ]
        );
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Equipe'),
                $equipe_id
            ]
        );

        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Coordenador'),
                $coordenador_id
            ]
        );

        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Casais (Membros)'),
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
        //$coordenador_id->addValidation('Coordenador', new TRequiredValidator);
        $equipe_id->addValidation('Equipe', new TRequiredValidator);
        //$casal_convite_id->addValidation('Convite', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
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

            if (!empty($data->coordenador_id) and !empty($data->encontro_id)) {

                $busca_montagem = Montagem::where('casal_id', '=', $data->coordenador_id)->where('encontro_id', '=', $data->encontro_id)->where('tipo_id', '=', 2)->first();
                if ($busca_montagem) {
                    $montagem = Montagem::find($busca_montagem->id);
                } else {
                    $montagem = new Montagem();  // create an empty object
                    $montagem->tipo_id = 2;
                    $montagem->encontro_id = $data->encontro_id;
                    $montagem->casal_id = $data->coordenador_id;
                    $montagem->conducao_propria_id = 0;

                    $buscacirculo = CirculoHistorico::where('casal_id', '=', $data->coordenador_id)->orderby('id', 'desc')->first();
                    if ($buscacirculo) {
                        $montagem->circulo_id = $buscacirculo->circulo_id;
                    } else {
                        $montagem->circulo_id = 17;
                    }
                }

                $montagem->store(); // save the object

                $busca_encontreiro = Encontreiro::where('montagem_id', '=', $montagem->id)->first();
                if ($busca_encontreiro) {
                    $encontreiro = Encontreiro::find($busca_encontreiro->id);
                } else {
                    $encontreiro = new Encontreiro();  // create an empty object
                    $encontreiro->montagem_id = $montagem->id;
                    $encontreiro->camisa_encontro_br = 2;
                    $encontreiro->camisa_encontro_cor = 2;
                    $encontreiro->disponibilidade_nt = 2;
                    $encontreiro->coordenar_s_n = 2;

                    $encontreiro->store(); // save the object
                }

                $encontreiro_equipe = new EncontreiroEquipe;
                $encontreiro_equipe->encontreiro_id = $encontreiro->id;
                $encontreiro_equipe->funcao_id  = 1;
                $encontreiro_equipe->equipe_id = $data->equipe_id;
                $encontreiro_equipe->tipo_enc_id  = 1;

                // add the contact to the customer
                $encontreiro_equipe->store(); // save the object

            }

            if (!empty($data->casal_id) and !empty($data->encontro_id)) {

                foreach ($data->casal_id as $casal_id) {

                    $busca_montagem = Montagem::where('casal_id', '=', $casal_id)->where('encontro_id', '=', $data->encontro_id)->where('tipo_id', '=', 2)->first();
                    if ($busca_montagem) {
                        $montagem = Montagem::find($busca_montagem->id);
                    } else {
                        $montagem = new Montagem();  // create an empty object
                        $montagem->tipo_id = 2;
                        $montagem->encontro_id = $data->encontro_id;
                        $montagem->casal_id = $casal_id;
                        $montagem->conducao_propria_id = 0;

                        $buscacirculo = CirculoHistorico::where('casal_id', '=', $casal_id)->orderby('id', 'desc')->first();
                        if ($buscacirculo) {
                            $montagem->circulo_id = $buscacirculo->circulo_id;
                        } else {
                            $montagem->circulo_id = 17;
                        }
                    }

                    $montagem->store(); // save the object

                    $busca_encontreiro = Encontreiro::where('montagem_id', '=', $montagem->id)->first();
                    if ($busca_encontreiro) {
                        $encontreiro = Encontreiro::find($busca_encontreiro->id);
                    } else {
                        $encontreiro = new Encontreiro();  // create an empty object
                        $encontreiro->montagem_id = $montagem->id;
                        $encontreiro->camisa_encontro_br = 2;
                        $encontreiro->camisa_encontro_cor = 2;
                        $encontreiro->disponibilidade_nt = 2;
                        $encontreiro->coordenar_s_n = 2;

                        $encontreiro->store(); // save the object
                    }

                    $encontreiro_equipe = new EncontreiroEquipe;
                    $encontreiro_equipe->encontreiro_id = $encontreiro->id;
                    $encontreiro_equipe->funcao_id  = 4;
                    $encontreiro_equipe->equipe_id = $data->equipe_id;
                    $encontreiro_equipe->tipo_enc_id  = 1;

                    // add the contact to the customer
                    $encontreiro_equipe->store(); // save the object
                }
            }

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
