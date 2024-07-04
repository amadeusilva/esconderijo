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
class EddPessoaParentesco extends TWindow
{
    protected $form; // form

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
        parent::setTitle('Parentesco');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('PessoaParentesco');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_parentesco');
        $this->form->setClientValidation(true);

        // create the form fields
        $id       = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $row = $this->form->addFields([new TLabel('Cod.:', 'red'), $id]);
        $row->layout = ['col-sm-12'];

        $pessoa_id = new TDBCombo('pessoa_id', 'adea', 'Pessoa', 'id', 'nome');
        $pessoa_id->setEditable(FALSE);
        $pessoa_id->setSize('100%');
        $row = $this->form->addFields([new TLabel('Pessoa:', 'red'), $pessoa_id]);
        $row->layout = ['col-sm-12'];

        $filterPa = new TCriteria;
        $filterPa->add(new TFilter('lista_id', '=', '12'));
        $parentesco_id = new TDBCombo('parentesco_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterPa);
        $parentesco_id->enableSearch();
        $parentesco_id->setSize('100%');
        $row = $this->form->addFields([new TLabel('Parentesco:', 'red'), $parentesco_id]);
        $row->layout = ['col-sm-12'];

        $pessoa_parente_id = new TDBCombo('pessoa_parente_id', 'adea', 'Pessoa', 'id', 'nome');
        $pessoa_parente_id->setEditable(FALSE);
        $pessoa_parente_id->setSize('100%');
        $row = $this->form->addFields([new TLabel('Parente:', 'red'), $pessoa_parente_id]);
        $row->layout = ['col-sm-12'];

        $obs_parentesco = new TText('obs_parentesco');
        $obs_parentesco->setSize('100%');
        $row = $this->form->addFields([new TLabel('Observação:', 'red'), $obs_parentesco]);
        $row->layout = ['col-sm-12'];

        $pessoa_id->addValidation('Pessoa', new TRequiredValidator);
        $parentesco_id->addValidation('Parentesco', new TRequiredValidator);
        $pessoa_parente_id->addValidation('Parente', new TRequiredValidator);
        $obs_parentesco->addValidation('Observação', new TRequiredValidator);
        
        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    public function onClear()
    {
        $this->form->clear( TRUE );
    }

    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
