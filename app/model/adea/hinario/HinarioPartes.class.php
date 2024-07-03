<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Hinario extends TRecord
{
    const TABLENAME = 'hinario_partes';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('hinario_id');
        parent::addAttribute('tipo_parte');
        parent::addAttribute('parte');
    }

}
