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
        parent::addAttribute('coordenar_s_n');
    }

    public function get_Montagem()
    {
        return Montagem::find($this->montagem_id);
    }

    public function get_EncontreiroEquipe()
    {
        return EncontreiroEquipe::where('encontreiro_id', '=', $this->id)->load();
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        EncontreiroEquipe::where('encontreiro_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
