<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class CirculoHistorico extends TRecord
{
    const TABLENAME = 'ecc.circulo_historico';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('user_sessao_id');
        parent::addAttribute('casal_id');
        parent::addAttribute('circulo_id');
        parent::addAttribute('motivo_id');
        parent::addAttribute('obs_motivo');
        parent::addAttribute('casal_id');
        parent::addAttribute('dt_historico');
    }

    public function get_Circulo()
    {
        return ListaItens::find($this->circulo_id);
    }
}
