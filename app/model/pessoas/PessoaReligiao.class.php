<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaReligiao extends TRecord
{
    const TABLENAME = 'pessoa_religiao';
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
        parent::addAttribute('religiao_id');
        parent::addAttribute('igreja_id'); // pessoa_jur_id
        parent::addAttribute('obs_religiao');
        
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }
    
    public function get_Religiao()
    {
        return ListaItensSub::find($this->religiao_id);
    }

    public function get_Igreja()
    {
        return ListaItensSub::find($this->igreja_id);
    }
    
}
