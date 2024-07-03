<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ListaItensSub extends TRecord
{
    const TABLENAME = 'lista_itens_sub';
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
        parent::addAttribute('lista_itens_id');
        parent::addAttribute('item');
        parent::addAttribute('abrev');
        parent::addAttribute('obs');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }
    
    public function get_ListaItens()
    {
        return ListaItens::find($this->lista_itens_id);
    }
}
