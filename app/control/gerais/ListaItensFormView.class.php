<?php

use Adianti\Widget\Form\TText;

/**
 * CustomerFormView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ListaItensFormView extends TPage
{
    private $form; // form
    private $embedded;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param, $embedded = false)
    {
        parent::__construct();

        $this->embedded = $embedded;

        if (!$this->embedded) {
            parent::setTargetContainer('adianti_right_panel');
        }

        // creates the form
        $this->form = new BootstrapFormBuilder('form_lista_itens');
        if (!$this->embedded) {
            $this->form->setFormTitle('Lista de Itens');
        }
        $this->form->setClientValidation(true);

        // create the form fields
        $id          = new TEntry('id');
        $lista_id    = new TDBCombo('lista_id', 'adea', 'Lista', 'id', '{lista} ({obs})', 'id');
        $lista_id->enableSearch();
        $item        = new TEntry('item');
        $abrev       = new TEntry('abrev');
        $obs         = new TText('obs');

        // define some properties for the form fields
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $lista_id->setSize('100%');
        $item->setSize('100%');
        $abrev->setSize('100%');
        $obs->setSize('100%');

        $this->form->appendPage('Dados');

        $this->form->addFields([new TLabel('Cod.')],        [$id]);
        $this->form->addFields([new TLabel('Lista')],   [$lista_id]);
        $this->form->addFields([new TLabel('Item')],   [$item]);
        $this->form->addFields([new TLabel('Abrev.')],   [$abrev]);
        $this->form->addFields([new TLabel('Observação')],  [$obs]);

        /*$this->form->appendPage('Skills');
        $skill_list = new TDBCheckGroup('skill_list', 'samples', 'Skill', 'id', 'name');
        $this->form->addFields( [ new TLabel('Skill') ],     [ $skill_list ] );
        
        $this->form->appendPage('Contacts');
        $contact_type = new TCombo('contact_type[]');
        $contact_type->setSize('100%');
        $contact_type->addItems( ['email' => 'E-mail',
                                  'phone' => 'Phone' ]);
        $contact_value = new TEntry('contact_value[]');
        $contact_value->setSize('100%');
        
        $this->contacts = new TFieldList;
        $this->contacts->addField( '<b>Type</b>', $contact_type, ['width' => '50%']);
        $this->contacts->addField( '<b>Value</b>', $contact_value, ['width' => '50%']);
        $this->form->addField($contact_type);
        $this->form->addField($contact_value);
        $this->contacts->enableSorting();
        
        $this->form->addContent( [ new TLabel('Contacts') ], [ $this->contacts ] );*/

        $lista_id->addValidation('Lista', new TRequiredValidator);
        $item->addValidation('Item', new TRequiredValidator);
        $abrev->addValidation('Abrev.', new TRequiredValidator);
        //$obs->addValidation('Observação', new TRequiredValidator);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave'], ['embedded' => $embedded ? '1' : '0']), 'fa:save green');

        if (!$this->embedded) {
            $this->form->addActionLink('Clear', new TAction([$this, 'onClear']), 'fa:eraser red');
            $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        }

        // add the form inside the page
        parent::add($this->form);
    }

    /**
     * method onSave
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {

        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            if (empty($param['item'])) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Item'));
            }

            // read the form data and instantiates an Active Record

            $listaitens = new ListaItens();
            $listaitens->fromArray($param);

            /*if( !empty($param['contact_type']) AND is_array($param['contact_type']) )
            {
                foreach( $param['contact_type'] as $row => $contact_type)
                {
                    if ($contact_type)
                    {
                        $contact = new Contact;
                        $contact->type  = $contact_type;
                        $contact->value = $param['contact_value'][$row];
                        
                        // add the contact to the customer
                        $customer->addContact($contact);
                    }
                }
            }
            
            if ( !empty($param['skill_list']) )
            {
                foreach ($param['skill_list'] as $skill_id)
                {
                    // add the skill to the customer
                    $customer->addSkill(new Skill($skill_id));
                }
            }*/

            // stores the object in the database

            $listaitens->store();

            $data = new stdClass;
            $data->id = $listaitens->id;
            TForm::sendData('form_lista_itens', $data);

            if (!$param['embedded']) {
                TScript::create("Template.closeRightPanel()");

                $posAction = new TAction(array('ListaItensDataGridView', 'onReload'));
                $posAction->setParameter('target_container', 'adianti_div_content');

                // shows the success message
                new TMessage('info', 'Record saved', $posAction);
            } else {
                TWindow::closeWindowByName('ListaItensFormView');
            }

            TTransaction::close(); // close the transaction
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * method onEdit
     * Edit a record data
     */
    function onEdit($param)
    {
        try {
            if (isset($param['id'])) {
                // open a transaction with database 'samples'
                TTransaction::open('adea');

                // load the Active Record according to its ID
                $listaitens = new ListaItens($param['id']);

                //id, lista_id, item, abrev, obs

                /* load the contacts (composition)
                $contacts = $customer->getContacts();
                
                if ($contacts)
                {
                    $this->contacts->addHeader();
                    foreach ($contacts as $contact)
                    {
                        $contact_detail = new stdClass;
                        $contact_detail->contact_type  = $contact->type;
                        $contact_detail->contact_value = $contact->value;
                        
                        $this->contacts->addDetail($contact_detail);
                    }
                    
                    $this->contacts->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                // load the skills (aggregation)
                $skills = $customer->getSkills();
                $skill_list = array();
                if ($skills)
                {
                    foreach ($skills as $skill)
                    {
                        $skill_list[] = $skill->id;
                    }
                }
                $customer->skill_list = $skill_list;
                
                // fill the form with the active record data*/
                $this->form->setData($listaitens);

                // close the transaction
                TTransaction::close();
            } else {
                $this->onClear($param);
            }
        } catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear();

        //$this->contacts->addHeader();
        //$this->contacts->addDetail( new stdClass );
        //$this->contacts->addCloneAction();
    }

    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}