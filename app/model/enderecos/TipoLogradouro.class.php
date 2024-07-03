<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class TipoLogradouro extends TRecord
{
    const TABLENAME = 'tipo_logradouro';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo');
        parent::addAttribute('abrev');
        
    }

    public function get_Pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }
    
}
