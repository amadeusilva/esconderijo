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
class EquipesUnicasForm extends TWindow
{
    protected $form; // form
    private $equipe;

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
        parent::setSize(600, null);

        if ($param['tipo_enc_id'] == 1) {
            parent::setTitle('Equipes');
        } else if ($param['tipo_enc_id'] == 2) {
            parent::setTitle('Palestras');
        } else if ($param['tipo_enc_id'] == 3) {
            parent::setTitle('Pastas');
        } else {
            parent::setTitle('Sem Título');
        }

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Encontreiro');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_equipes_unicas');
        $this->form->setClientValidation(true);

        //dados 
        $id = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');

        //dados 
        $tipo_enc_id = new TEntry('tipo_enc_id');
        $tipo_enc_id->setEditable(FALSE);
        $tipo_enc_id->setSize('100%');

        $filter = new TCriteria;
        $filter->add(new TFilter('evento_id', '=', '701'));
        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id', $filter);
        $encontro_id->enableSearch();
        $encontro_id->setSize('100%');

        // define some properties for the form fields

        $row = $this->form->addFields(
            [new TLabel('Cod.:'),    $id],
            [
                new TLabel('Tipo'),
                $tipo_enc_id
            ],
            [
                new TLabel('Encontro'),
                $encontro_id
            ]
        );
        $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-6'];

        if ($param['tipo_enc_id'] == 1) {

            $label = new TLabel('<br>Equipe(s)', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
            $this->form->addContent([$label]);

            $equipe_id = new TDBCombo('equipe_id[]', 'adea', 'Equipe', 'id', 'equipe', 'id');
            $equipe_id->enableSearch();
            $equipe_id->setSize('100%');

            $casal_id = new TDBCombo('casal_id[]', 'adea', 'ViewEncontrista', 'casal_id', '{casal} ({Casamento})', 'casal_id');
            $casal_id->enableSearch();
            $casal_id->setSize('100%');

            $this->equipe = new TFieldList;
            $this->equipe->addField('<b>Equipe</b>', $equipe_id, ['width' => '50%']);
            $this->equipe->addField('<b>Coordenador</b>', $casal_id, ['width' => '50%']);
        } else if ($param['tipo_enc_id'] == 2) {
            $label = new TLabel('<br>Palestra(s)', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
            $this->form->addContent([$label]);

            $filterPalestra = new TCriteria;
            $filterPalestra->add(new TFilter('lista_id', '=', '19'));
            $equipe_id = new TDBCombo('equipe_id[]', 'adea', 'ListaItens', 'id', 'item', 'id', $filterPalestra);
            $equipe_id->enableSearch();
            $equipe_id->setSize('100%');

            $casal_id = new TDBCombo('casal_id[]', 'adea', 'ViewEncontrista', 'casal_id', '{casal} ({Casamento})', 'casal_id');
            $casal_id->enableSearch();
            $casal_id->setSize('100%');

            $this->equipe = new TFieldList;
            $this->equipe->addField('<b>Palestra</b>', $equipe_id, ['width' => '50%']);
            $this->equipe->addField('<b>Coordenador</b>', $casal_id, ['width' => '50%']);
        } else if ($param['tipo_enc_id'] == 3) {
            $label = new TLabel('<br>Pasta(s)', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
            $this->form->addContent([$label]);

            $filterPasta = new TCriteria;
            $filterPasta->add(new TFilter('lista_id', '=', '20'));
            $equipe_id = new TDBCombo('equipe_id[]', 'adea', 'ListaItens', 'id', 'item', 'id', $filterPasta);
            $equipe_id->enableSearch();
            $equipe_id->setSize('100%');

            $casal_id = new TDBCombo('casal_id[]', 'adea', 'ViewEncontrista', 'casal_id', '{casal} ({Casamento})', 'casal_id');
            $casal_id->enableSearch();
            $casal_id->setSize('100%');

            $this->equipe = new TFieldList;
            $this->equipe->addField('<b>Pasta</b>', $equipe_id, ['width' => '50%']);
            $this->equipe->addField('<b>Coordenador</b>', $casal_id, ['width' => '50%']);
        }

        $this->form->addField($equipe_id);
        $this->form->addField($casal_id);
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

                $montagem->ele_nome        = $montagem->DadosCasal->Ele->nome;
                $montagem->ele_dn        = TDate::date2br($montagem->DadosCasal->Ele->dt_nascimento);

                $montagem->ela_nome        = $montagem->DadosCasal->Ela->nome;
                $montagem->ela_dn        = TDate::date2br($montagem->DadosCasal->Ela->dt_nascimento);

                $montagem->camisa_encontro_br = $encontreiro->camisa_encontro_br;
                $montagem->camisa_encontro_cor = $encontreiro->camisa_encontro_cor;
                $montagem->disponibilidade_nt = $encontreiro->disponibilidade_nt;
                $montagem->coordenar_s_n = $encontreiro->coordenar_s_n;

                $encontreiro_equipe = EncontreiroEquipe::where('encontreiro_id', '=', $encontreiro->id)->where('tipo_enc_id', '=', $param['tipo_enc_id'])->load();

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
                    $this->onLoad($param);
                }

                $this->form->setData($montagem);   // fill the form with the active record data
                $this->onLoad($param);

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

    public function onLoad($param)
    {
        if (isset($param)) {
            TForm::sendData('form_equipes_unicas', $param);
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

            if (!empty($data->casal_id) and !empty($data->encontro_id)) {

                foreach ($param['casal_id'] as $row => $casal_id) {

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
                    $encontreiro_equipe->funcao_id  = 1; // coordenador
                    $encontreiro_equipe->equipe_id = $param['equipe_id'][$row];
                    $encontreiro_equipe->tipo_enc_id  = $data->tipo_enc_id;

                    // add the contact to the customer
                    $encontreiro_equipe->store(); // save the object
                }
            }

            TTransaction::close();  // close the transaction

            // fill the form with the active record data
            if ($data->tipo_enc_id == 1) {
                $posAction = new TAction(array('EncontreiroDataGrid', 'onReload'));
            } else if ($data->tipo_enc_id == 2) {
                $posAction = new TAction(array('PalestranteDataGrid', 'onReload'));
            } else if ($data->tipo_enc_id == 3) {
                $posAction = new TAction(array('EdgDataGrid', 'onReload'));
            }

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

        $form_vazio = new stdClass;
        $form_vazio->tipo_enc_id = $param['tipo_enc_id'];
        //$form_vazio->encontro_id = 4;
        TForm::sendData('form_equipes_unicas', $form_vazio);

        $this->equipe->addHeader();
        $dados = new stdClass();
        //$dados->equipe_id = 1;
        $this->equipe->addDetail($dados);
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
