<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaRestrincao extends TRecord
{
    const TABLENAME = 'pessoa_restrincao';
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
        parent::addAttribute('pessoa_id');
        parent::addAttribute('tipo_restrincao');
        parent::addAttribute('nome_restrincao');
        
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }
    
    public function get_TipoRestrincao()
    {
        return ListaItensSub::find($this->tipo_restrincao);
    }
    
}
