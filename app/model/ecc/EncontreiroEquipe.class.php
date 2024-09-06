<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class EncontreiroEquipe extends TRecord
{
    const TABLENAME = 'ecc.encontreiro_equipe';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('encontreiro_id');
        parent::addAttribute('funcao_id');
        parent::addAttribute('equipe_id');
    }

    public function get_Encontreiro()
    {
        return Encontreiro::find($this->encontreiro_id);
    }

    public function get_Equipe()
    {
        return Equipe::find($this->equipe_id);
    }
}
