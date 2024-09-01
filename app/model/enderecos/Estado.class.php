<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Estado extends TRecord
{
    const TABLENAME = 'enderecos.estado';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('estado');
        parent::addAttribute('sigla');
    }

}
