<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Encontreiro extends TRecord
{
    const TABLENAME = 'ecc.encontreiro';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('montagem_id');
        parent::addAttribute('camisa_encontro_br');
        parent::addAttribute('camisa_encontro_cor');
        parent::addAttribute('disponibilidade_nt');
        parent::addAttribute('coordenador_s_n');
        parent::addAttribute('equipe_id');
    }

    public function get_Montagem()
    {
        return ListaItens::find($this->montagem_id);
    }
}
