<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaConducao extends TRecord
{
    const TABLENAME = 'pessoa_conducao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('conducao_id');
        parent::addAttribute('status_conducao_id');
    }

    public function get_Pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }    
}
