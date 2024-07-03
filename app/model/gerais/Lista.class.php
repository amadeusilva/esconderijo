<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Lista extends TRecord
{
    const TABLENAME = 'lista';
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
        parent::addAttribute('lista');
        parent::addAttribute('obs');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;
        
        ListaItens::where('lista_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
