<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ConducaoPropria extends TRecord
{
    const TABLENAME = 'globais.conducao_propria';
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
        parent::addAttribute('placa');
        parent::addAttribute('detalhes_conducao');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }
}
