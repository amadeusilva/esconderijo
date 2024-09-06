<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Equipe extends TRecord
{
    const TABLENAME = 'globais.equipe';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    //const CREATEDAT = 'created_at';
    //const UPDATEDAT = 'updated_at';

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('equipe');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }
}
