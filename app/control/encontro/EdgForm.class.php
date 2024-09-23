<?php

use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TFieldList;
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
class EdgForm extends TWindow
{
    protected $form; // form
    private $equipe;

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
        parent::setTitle('Equipe de Direção Geral');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Encontreiro');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_edg');
        $this->form->setClientValidation(true);

        //dados 
        $id = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');

        $casal_id = new TDBCombo('casal_id', 'adea', 'ViewEncontrista', 'casal_id', 'casal', 'casal_id');
        $casal_id->enableSearch();
        $casal_id->setSize('100%');
        $casal_id->setChangeAction(new TAction(array($this, 'onCirculo')));

        $filter = new TCriteria;
        $filter->add(new TFilter('evento_id', '=', '701'));
        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id', $filter);
        $encontro_id->enableSearch();
        $encontro_id->setSize('100%');
        $encontro_id->setChangeAction(new TAction(array($this, 'onEncontreiro')));

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

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'abrev', 'id', $filterCirculo);
        $circulo_id->setSize('100%');
        $circulo_id->setEditable(FALSE);

        // define some properties for the form fields

        $row = $this->form->addFields(
            [new TLabel('Cod.:'),    $id],
            [
                new TLabel('Casal'),
                $casal_id
            ]
        );
        $row->layout = ['col-sm-3', 'col-sm-9'];

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

        $label = new TLabel('<br>Camisas?', '#62a8ee', 14, 'b');
        $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
        $this->form->addContent([$label]);

        $row = $this->form->addFields(
            [
                new TLabel('Branca?')
            ],
            [

                $camisa_encontro_br
            ],
            [
                new TLabel('Círculo?')
            ],
            [

                $camisa_encontro_cor
            ]
        );
        $row->layout = ['col-sm-4', 'col-sm-8', 'col-sm-4', 'col-sm-8'];

        $label = new TLabel('<br>Outras Informações', '#62a8ee', 14, 'b');
        $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
        $this->form->addContent([$label]);

        $row = $this->form->addFields(
            [
                new TLabel('Noite (Livre)?')
            ],
            [

                $disponibilidade_nt
            ],
            [
                new TLabel('Coordenar?')
            ],
            [

                $coordenar_s_n
            ]
        );
        $row->layout = ['col-sm-4', 'col-sm-8', 'col-sm-4', 'col-sm-8'];

        $label = new TLabel('<br>Equipe(s)', '#62a8ee', 14, 'b');
        $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
        $this->form->addContent([$label]);

        $funcao_id = new TCombo('funcao_id[]');
        $funcao_id->setSize('100%');
        $funcao_id->addItems([1 => 'Titular', 2 => 'Apoio']);

        $filterPasta = new TCriteria;
        $filterPasta->add(new TFilter('lista_id', '=', '20'));
        $equipe_id = new TDBCombo('equipe_id[]', 'adea', 'ListaItens', 'id', 'item', 'id', $filterPasta);
        $equipe_id->enableSearch();
        $equipe_id->setSize('100%');

        $this->equipe = new TFieldList;
        $this->equipe->addField('<b>Função</b>', $funcao_id, ['width' => '50%']);
        $this->equipe->addField('<b>Pasta</b>', $equipe_id, ['width' => '50%']);
        $this->form->addField($funcao_id);
        $this->form->addField($equipe_id);
        $this->equipe->enableSorting();

        $row = $this->form->addFields(
            [
                $this->equipe
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
        $equipe_id->addValidation('Pasta', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    public static function onCirculo($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['casal_id'])) {
                $buscacirculo = CirculoHistorico::where('casal_id', '=', $param['casal_id'])->first();
                if ($buscacirculo) {
                    $nometela = new stdClass;
                    $nometela->circulo_id        = $buscacirculo->circulo_id;

                    TForm::sendData('form_edg', $nometela);
                }
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onEncontreiro($param)
    {

        echo '<pre>';
        print_r($param);
        echo '</pre>';
        exit;

        try {
            TTransaction::open('adea');
            if (!empty($param['casal_id']) and !empty($param['encontro_id'])) {
                $montagem = Montagem::where('casal_id', '=', $param['casal_id'])->where('encontro_id', '=', $param['encontro_id'])->first();
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
                $encontreiro = Encontreiro::where('montagem_id', '=', $montagem->id)->first();

                $montagem->camisa_encontro_br = $encontreiro->camisa_encontro_br;
                $montagem->camisa_encontro_cor = $encontreiro->camisa_encontro_cor;
                $montagem->disponibilidade_nt = $encontreiro->disponibilidade_nt;
                $montagem->coordenar_s_n = $encontreiro->coordenar_s_n;

                $encontreiro_equipe = EncontreiroEquipe::where('encontreiro_id', '=', $encontreiro->id)->load();

                if ($encontreiro_equipe) {
                    $this->equipe->addHeader();
                    foreach ($encontreiro_equipe as $enc_equip) {
                        $enc_equip_dados = new stdClass;
                        $enc_equip_dados->funcao_id  = $enc_equip->funcao_id;
                        $enc_equip_dados->equipe_id = $enc_equip->equipe_id;

                        $this->equipe->addDetail($enc_equip_dados);
                    }

                    $this->equipe->addCloneAction();
                } else {
                    $this->onClear($param);
                }

                $this->form->setData($montagem);   // fill the form with the active record data
                TTransaction::close();           // close the transaction
            } else {
                $this->form->clear(true);

                $this->equipe->addHeader();
                $this->equipe->addDetail(new stdClass);
                $this->equipe->addCloneAction();
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
    public function onSave($param)
    {

        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            $this->form->validate(); // run form validation

            $data = $this->form->getData(); // get form data as array

            //if (isset($param['id']) and !empty(isset($param['id']))) {
            //    $montagem = new Montagem($param['id']);
            //}

            $montagem = new Montagem();  // create an empty object
            $montagem->fromArray((array) $data); // load the object with data
            $montagem->tipo_id = 4;
            $montagem->conducao_propria_id = 0;
            $montagem->store(); // save the object

            $encontreiro = Encontreiro::where('montagem_id', '=', $montagem->id)->first();
            if ($encontreiro) {
                $salva_encontreiro_id = $encontreiro->id;
                $encontreiro->delete();
                $encontreiro_equipe = EncontreiroEquipe::where('encontreiro_id', '=', $salva_encontreiro_id)->delete();
            }

            $encontreiro = new Encontreiro();  // create an empty object
            $encontreiro->montagem_id = $montagem->id;
            $encontreiro->camisa_encontro_br = $data->camisa_encontro_br;
            $encontreiro->camisa_encontro_cor = $data->camisa_encontro_cor;
            $encontreiro->disponibilidade_nt = $data->disponibilidade_nt;
            $encontreiro->coordenar_s_n = $data->coordenar_s_n;
            $encontreiro->store(); // save the object

            if (!empty($param['funcao_id']) and is_array($param['funcao_id'])) {
                foreach ($param['funcao_id'] as $row => $funcao_id) {
                    if ($funcao_id) {
                        $encontreiro_equipe = new EncontreiroEquipe;
                        $encontreiro_equipe->encontreiro_id = $encontreiro->id;
                        $encontreiro_equipe->funcao_id  = $funcao_id;
                        $encontreiro_equipe->equipe_id = $param['equipe_id'][$row];

                        // add the contact to the customer
                        $encontreiro_equipe->store(); // save the object
                    }
                }
            }

            // fill the form with the active record data
            $this->form->setData($encontreiro);

            TTransaction::close();  // close the transaction

            // fill the form with the active record data
            $posAction = new TAction(array('EdgDataGrid', 'onReload'));

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
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear();

        $this->equipe->addHeader();
        $this->equipe->addDetail(new stdClass);
        $this->equipe->addCloneAction();
    }

    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
