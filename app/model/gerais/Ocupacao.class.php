<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Ocupacao extends TRecord
{
    const TABLENAME = 'globais.ocupacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    //const CREATEDAT = 'created_at';
    //const UPDATEDAT = 'updated_at';
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('codigo');
        parent::addAttribute('titulo');
        parent::addAttribute('ck_ocupacao');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

}
