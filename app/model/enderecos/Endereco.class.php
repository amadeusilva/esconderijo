<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Endereco extends TRecord
{
    const TABLENAME = 'endereco';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cep');
        parent::addAttribute('logradouro_id');
        parent::addAttribute('n');
        parent::addAttribute('bairro_id');
        parent::addAttribute('ponto_referencia');
    }
    
    public function get_Logradouro()
    {
        return Logradouro::find($this->logradouro_id);
    }

    public function get_Bairro()
    {
        return Bairro::find($this->bairro_id);
    }
}
