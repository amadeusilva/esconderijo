<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Montagem extends TRecord
{
    const TABLENAME = 'montagem';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('encontro_id');
        parent::addAttribute('equipe_id');
        parent::addAttribute('casal_id');
        parent::addAttribute('funcao_id');
        parent::addAttribute('camisa_encontro');
        parent::addAttribute('tm_camisa');
        parent::addAttribute('disponibilidade_nt');
        parent::addAttribute('circulo_id'); // verificar criação de uma nova tabela de históricos para círculos
    }

    public function get_TmCamisa()
    {
        return ListaItens::find($this->tm_camisa);
    }
}
