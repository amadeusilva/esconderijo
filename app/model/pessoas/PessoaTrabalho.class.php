<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaTrabalho extends TRecord
{
    const TABLENAME = 'pessoa_trabalho';
    const PRIMARYKEY = 'id';
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
        parent::addAttribute('profissao_id');
        parent::addAttribute('cargo_id');
        parent::addAttribute('local_id'); // pessoa_jur_id
        parent::addAttribute('obs_trabalho');

        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

    public function get_Profissao()
    {
        return Ocupacao::find($this->profissao_id);
    }

    public function get_Cargo()
    {
        return ListaItensSub::find($this->cargo_id);
    }

    public function get_Local()
    {
        return ListaItensSub::find($this->local_id);
    }
}
