<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Encontrista extends TRecord
{
    const TABLENAME = 'ecc.encontrista';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('montagem_id');
        parent::addAttribute('secretario_s_n');
        parent::addAttribute('casal_convite_id');
    }

    public function get_Montagem()
    {
        return ListaItens::find($this->montagem_id);
    }
}
