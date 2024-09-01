<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaContato extends TRecord
{
    const TABLENAME = 'pessoas.pessoa_contato';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('tipo_contato_id');
        parent::addAttribute('contato');
        parent::addAttribute('status_contato_id');
    }

    public function get_TipoContato()
    {
        return ListaItens::find($this->tipo_contato_id);
    }

}