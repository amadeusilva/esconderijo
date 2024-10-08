<?php

use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TFile;
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
class DadosRelacao extends TWindow
{
    use ControlePessoas;
    protected $form; // form

    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;

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
        parent::setTitle('Dados da Relação');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('PessoasRelacao');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_dados_relacao');
        $this->form->setClientValidation(true);
        self::onVinculo($param);

        // create the form fields
        $id       = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $pessoa_parentesco_id       = new TEntry('pessoa_parentesco_id');
        $pessoa_parentesco_id->setEditable(FALSE);
        $pessoa_parentesco_id->setSize('100%');
        $row = $this->form->addFields([new TLabel('Cod.:', 'red'), $id], [new TLabel('Ref.:', 'red'), $pessoa_parentesco_id]);
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $estado_civil_id = new TDBCombo('estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id');
        $tipo_vinculo = new TEntry('tipo_vinculo');
        $dt_inicial                 = new TDate('dt_inicial');
        $dt_inicial->setMask('dd/mm/yyyy');
        $dt_inicial->setExitAction(new TAction(array($this, 'onCalculaDieferencaTempo')));
        $dt_final                 = new TDate('dt_final');
        $dt_final->setMask('dd/mm/yyyy');
        $tempo = new TEntry('tempo');
        $doc_imagem  = new TFile('doc_imagem');
        // allow just these extensions
        $doc_imagem->setAllowedExtensions(['gif', 'png', 'jpg', 'jpeg']);
        // enable progress bar, preview
        $doc_imagem->enableFileHandling();
        $doc_imagem->enableImageGallery();
        $doc_imagem->enablePopover();

        // define some properties for the form fields
        $estado_civil_id->setEditable(FALSE);
        $tipo_vinculo->setEditable(FALSE);
        $dt_final->setEditable(FALSE);
        $tempo->setEditable(FALSE);
        $estado_civil_id->setSize('100%');
        $tipo_vinculo->setSize('100%');
        $dt_inicial->setSize('100%');
        $dt_final->setSize('100%');
        $tempo->setSize('100%');
        $doc_imagem->setSize('100%');

        $options = ['s' => 'Sim', 'n' => 'Não'];
        $atualizacao = new TRadioGroup('atualizacao');
        $atualizacao->setUseButton();
        $atualizacao->addItems($options);
        $atualizacao->setLayout('horizontal');
        $atualizacao->setValue('s');
        $atualizacao->setEditable(FALSE);
        $atualizacao->setSize('100%');

        // create the form fields
        $row = $this->form->addFields([new TLabel('Estado Civil', 'red'), $estado_civil_id]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Vínculo', 'red'), $tipo_vinculo]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Data Ínicio', 'red'), $dt_inicial]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Data Final (Contagem)', 'red'), $dt_final]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Contagem (tempo)', 'red'), $tempo]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Documento (Certidão ou declaração)', 'red'), $doc_imagem]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Verificado?', 'red')], [$atualizacao]);
        $row->layout = ['col-sm-5', 'col-sm-7'];

        $estado_civil_id->addValidation('Estado Civil', new TRequiredValidator);
        $tipo_vinculo->addValidation('Tipo de vínculo', new TRequiredValidator);
        $dt_inicial->addValidation('Data Ínicio', new TRequiredValidator);
        $dt_final->addValidation('Data Ínicio', new TRequiredValidator);
        $atualizacao->addValidation('Dados Verificados?', new TRequiredValidator);


        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave'), ['param' => $param]), 'fa:save green');
        //$this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    public function onVerRelacao($param)
    {
        try {
            if (TSession::getValue('dados_relacao')) {

                $dados_relacao =   TSession::getValue('dados_relacao');

                $object = new stdClass;  // create an empty object
                //$object->id = $dados_relacao['id'];
                $object->estado_civil_id = $dados_relacao['estado_civil_id'];
                $object->tipo_vinculo = self::onVinculo($dados_relacao['estado_civil_id']);
                $object->dt_inicial = $dados_relacao['dt_inicial'];
                if (isset($dados_relacao['dt_final']) and !empty($dados_relacao['dt_final'])) {
                    $object->dt_final =  $dados_relacao['dt_final'];
                } else {
                    $object->dt_final = date('d/m/Y');
                }
                $object->tempo = $dados_relacao['tempo'];
                if ($dados_relacao['doc_imagem']) {
                    $object->doc_imagem = $dados_relacao['doc_imagem'];
                }
                $this->form->setData($object);
            } else
            if (isset($param['pessoa_parentesco_id']) and !empty($param['pessoa_parentesco_id'])) {
                TTransaction::open('adea');   // open a transaction with database 'samples'

                $pessoa_parente = new PessoaParentesco($param['pessoa_parentesco_id']);
                $object = new PessoasRelacao($pessoa_parente->relacao_id);        // instantiates object City

                $object->pessoa_parentesco_id = $pessoa_parente->id;

                $object->estado_civil_id = $pessoa_parente->Pessoa->estado_civil_id;

                $object->tipo_vinculo = $object->tipo_vinculo;

                if ($object->status_relacao_id == 2) {
                    TQuickForm::hideField('form_dados_relacao', 'estado_civil_id');
                    TDate::disableField('form_dados_relacao', 'dt_inicial');
                    TFile::disableField('form_dados_relacao', 'doc_imagem');
                    TButton::disableField('form_dados_relacao', 'salvar');
                }

                if ($object->tipo_vinculo == 'Sem documento de registro em cartório') {
                    TQuickForm::hideField('form_dados_relacao', 'doc_imagem');
                }
                //$object->tipo_vinculo = self::onVinculo($object->tipo_vinculo);
                $object->dt_inicial =  TDate::date2br($object->dt_inicial);
                if ($object->dt_final != '0000-00-00') {
                    $object->dt_final = TDate::date2br($object->dt_final);
                } else {
                    $object->dt_final = date('d/m/Y');
                }

                $object->tempo = self::onCalculaDieferencaTempo($object);

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

    public function onEdit($param)
    {

        $param['estado_civil_id'] = $param['param']['estado_civil_id'];
        $param['tipo_vinculo'] = self::onVinculo($param['estado_civil_id']);

        TForm::sendData('form_dados_relacao', $param);
    }

    /**
     * Clear form
     */
    public function onClear($param)
    {

        $this->form->clear(TRUE);
        $param['dt_inicial'] = '';
        $param['dt_final'] = '';
        $param['tempo'] = '';
        TForm::sendData('form_dados_relacao', $param);
    }


    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave($param)
    {

        try {

            if (!isset($param['param']['param']['param'])) {
                if (isset($param['param']['param']) and !empty($param['param']['param'])) {
                    TSession::setValue('dados_iniciais_pf', (array) $param['param']['param']);
                }
            }

            $this->form->validate();
            $data = $this->form->getData();

            if ($param['tipo_vinculo'] != 'Sem documento de registro em cartório') {
                if (isset($param['doc_imagem']) and empty($param['doc_imagem'])) {
                    throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Documento (Certidão ou declaração)'));
                }
            }

            $pessoa_painel = TSession::getValue('pessoa_painel');

            if (isset($param['id']) and !empty($param['id'])) {

                if (isset($param['param']['atualiza_cadastro']) and !empty($param['param']['atualiza_cadastro'])) {
                    TSession::setValue('dados_relacao', (array) $data);

                    // fill the form with the active record data
                    $this->form->setData($data);

                    self::onClose();

                    $posAction = new TDataGridAction(['DadosIniciaisPF', 'onEdit'],   ['id' => $pessoa_painel->id, 'register_state' => 'false']);
                    // shows the success message
                    new TMessage('info', 'Registro Salvo!', $posAction);
                } else {

                    TTransaction::open('adea');
                    $object = new PessoasRelacao();  // create an empty object

                    $novadatanaprdt_inicial = DateTime::createFromFormat('d/m/Y', $data->dt_inicial);
                    $data->dt_inicial = $novadatanaprdt_inicial->format('Y/m/d');

                    //$novadatanaprdt_final = DateTime::createFromFormat('d/m/Y', $data->dt_final);
                    //$data->dt_final = $novadatanaprdt_final->format('Y/m/d');

                    $object->fromArray((array) $data); // load the object with data
                    $object->store(); // save the object

                    // copy file to target folder
                    $this->saveFile($object, $data, 'doc_imagem', 'app/images/dadosderelacao');

                    // send id back to the form
                    $data->id = $object->id;
                    $this->form->setData($data);

                    TTransaction::close();

                    // fill the form with the active record data
                    $this->form->setData($object);

                    self::onClose();

                    $posAction = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => $pessoa_painel->id, 'register_state' => 'false']);
                    // shows the success message
                    new TMessage('info', 'Registro Salvo!', $posAction);
                }
            } else {
                TSession::setValue('dados_relacao', (array) $data);

                AdiantiCoreApplication::loadPage('DadosIniciaisPF', 'onEdit');
                // show the message dialog
                new TMessage('info', 'Dados armazenados com sucesso!');
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
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
