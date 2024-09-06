<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Montagem extends TRecord
{
    const TABLENAME = 'ecc.montagem';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_id');
        parent::addAttribute('encontro_id');
        parent::addAttribute('casal_id');
        parent::addAttribute('conducao_propria_id');
        parent::addAttribute('circulo_id');
    }

    public function get_Circulo()
    {
        return ListaItens::find($this->circulo_id);
    }

    public function get_Encontro()
    {
        return Encontro::find($this->encontro_id);
    }
}
