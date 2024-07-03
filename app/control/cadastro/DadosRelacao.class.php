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
class DadosRelacao extends TWindow
{
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
        $row = $this->form->addFields([new TLabel('Cod.:', 'red'), $id]);
        $row->layout = ['col-sm-12'];

        $estado_civil_id = new TDBCombo('estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id');
        $tipo_vinculo = new TEntry('tipo_vinculo');
        $dt_inicial                 = new TDate('dt_inicial');
        $dt_inicial->setMask('dd/mm/yyyy');
        $dt_inicial->setExitAction(new TAction(array($this, 'onCalculaTempo')));
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
        $tempo->setEditable(FALSE);
        $estado_civil_id->setSize('100%');
        $tipo_vinculo->setSize('100%');
        $dt_inicial->setSize('100%');
        $tempo->setSize('100%');
        $doc_imagem->setSize('100%');

        // create the form fields
        $row = $this->form->addFields([new TLabel('Estado Civil', 'red'), $estado_civil_id]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Vínculo', 'red'), $tipo_vinculo]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Data Ínicio', 'red'), $dt_inicial]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Contagem (tempo)', 'red'), $tempo]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields([new TLabel('Documento (Certidão ou declaração)', 'red'), $doc_imagem]);
        $row->layout = ['col-sm-12'];

        $estado_civil_id->addValidation('Estado Civil', new TRequiredValidator);
        $tipo_vinculo->addValidation('Tipo de vínculo', new TRequiredValidator);
        $dt_inicial->addValidation('Data Ínicio', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave'), ['param' => $param]), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    public static function onCalculaTempo($param)
    {
        if (isset($param) and !empty($param)) {
            if (isset($param['dt_inicial']) and !empty($param['dt_inicial'])) {
                $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_inicial']);
                $param['dt_inicial'] = $novadata->format('Y/m/d');
            } else {
                $novadata = DateTime::createFromFormat('d/m/Y', $param);
                $param = $novadata->format('Y/m/d');
            }

            $interval = $novadata->diff(new DateTime(date('Y-m-d')));
            $tempo_calculado = new stdClass;
            $tempo_calculado->tempo = $interval->format('%Y anos');

            TForm::sendData('form_dados_relacao', $tempo_calculado);
        }
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    public function onVerRelacao($param)
    {
        try {
            if ($param['id']) {

                TTransaction::open('adea');   // open a transaction with database 'samples'

                $key = $param['id'];  // get the parameter
                $object = PessoasRelacao::where('relacao_id', '=', $key)->first();        // instantiates object City
                $object->estado_civil_id = $object->PessoaParentesco->Pessoa->PessoaFisica->estado_civil_id;
                $object->tipo_vinculo = self::onVinculo($object->estado_civil_id);
                $object->dt_inicial =  TDate::date2br($object->dt_inicial);
                $object->tempo = self::onCalculaTempo($object->dt_inicial);

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

    public static function onVinculo($param)
    {
        $tipo_vinculo = '';

        if ($param == 805 or $param == 806) {
            $tipo_vinculo = 'Declaração de União Estável';
            TQuickForm::showField('form_dados_relacao', 'doc_imagem');
        } else if ($param == 803 or $param == 804) {
            $tipo_vinculo = 'Sem documento de registro em cartório';
            TQuickForm::hideField('form_dados_relacao', 'doc_imagem');
        } else if ($param == 807 or $param == 808) {
            $tipo_vinculo = 'Certidão de Casamento';
            TQuickForm::showField('form_dados_relacao', 'doc_imagem');
        }

        return $tipo_vinculo;
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

            if (isset($param['id']) and !empty($param['id'])) {
                TTransaction::open('adea');
                $object = new PessoasRelacao();  // create an empty object

                $novadatanapr = DateTime::createFromFormat('d/m/Y', $data->dt_inicial);
                $data->dt_inicial = $novadatanapr->format('Y/m/d');

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

                $pessoa_painel = TSession::getValue('pessoa_painel');
                $posAction = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => $pessoa_painel->id, 'register_state' => 'false']);
                // shows the success message
                new TMessage('info', 'Registro Salvo!', $posAction);
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
