<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Encontro extends TRecord
{
    const TABLENAME = 'encontro';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('num');
        parent::addAttribute('evento_id');
        parent::addAttribute('local_id');
        parent::addAttribute('dt_inicial');
        parent::addAttribute('dt_final');
        parent::addAttribute('tema');
        parent::addAttribute('divisa');
        parent::addAttribute('cantico_id');
    }
    
    public function get_Evento()
    {
        return ListaItens::find($this->evento_id);
    }
}
