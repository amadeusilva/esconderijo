<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Hinario extends TRecord
{
    const TABLENAME = 'globais.hinario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('ordem');
        parent::addAttribute('titulo');
        parent::addAttribute('hino');
    }

}
