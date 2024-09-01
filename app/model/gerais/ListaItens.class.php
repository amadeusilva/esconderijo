<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ListaItens extends TRecord
{
    const TABLENAME = 'globais.lista_itens';
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
        parent::addAttribute('lista_id');
        parent::addAttribute('item');
        parent::addAttribute('abrev');
        parent::addAttribute('obs');
        parent::addAttribute('ck');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }
    
    public function get_Lista()
    {
        return Lista::find($this->lista_id);
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;
        
        ListaItensSub::where('lista_itens_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
