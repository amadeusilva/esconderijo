<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PalestranteEdg extends TRecord
{
    const TABLENAME = 'ecc.palestrante_edg';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('montagem_id');
        parent::addAttribute('tipo_pales_edg');
        parent::addAttribute('pasta_id');
    }

    public function get_Montagem()
    {
        return Montagem::find($this->montagem_id);
    }

    public function get_Pasta()
    {
        return ListaItens::find($this->pasta_id);
    }
}
