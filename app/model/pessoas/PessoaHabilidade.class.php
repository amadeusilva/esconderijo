<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaHabilidade extends TRecord
{
    const TABLENAME = 'pessoas.pessoa_habilidade';
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
        parent::addAttribute('tipo_hab_id');
        
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

    public function get_StatusCasal()
    {
        return ListaItens::find($this->status_pessoa);
    }
    
    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;
        
        PessoaPapel::where('pessoa_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
