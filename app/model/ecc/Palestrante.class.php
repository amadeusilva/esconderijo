<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Palestrante extends TRecord
{
    const TABLENAME = 'ecc.palestrante';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('encontreiro_id');
        parent::addAttribute('palestra_id');
        parent::addAttribute('funcao_id');
    }

    public function get_Encontreiro()
    {
        return Encontreiro::find($this->encontreiro_id);
    }

    public function get_Palestra()
    {
        return ListaItens::find($this->palestra_id);
    }
}
