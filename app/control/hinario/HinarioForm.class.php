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

        $hino      = new THtmlEditor('hino');
        $hino->setSize('100%', 170);

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

        $row = $this->form->addFields(
            [
                new TLabel('Hino'),
                $hino
            ]
        );
        $row->layout = ['col-sm-12'];

        // validations
        $ordem->addValidation('Ordem', new TRequiredValidator);
        $titulo->addValidation('Título', new TRequiredValidator);
        $hino->addValidation('Hino', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
