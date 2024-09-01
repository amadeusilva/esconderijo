<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Logradouro extends TRecord
{
    const TABLENAME = 'enderecos.logradouro';
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
        parent::addAttribute('cidade_id');
        parent::addAttribute('tipo_id');
        parent::addAttribute('logradouro');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }
    
    public function get_Cidade()
    {
        return Cidade::find($this->cidade_id);
    }

    public function get_Tipo()
    {
        return ListaItens::find($this->tipo_id);
    }
    
    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;
        
        PessoaPapel::where('pessoa_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
