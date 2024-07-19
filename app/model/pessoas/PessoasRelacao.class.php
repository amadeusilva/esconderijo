<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoasRelacao extends TRecord
{
    const TABLENAME = 'pessoas_relacao';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('relacao_id'); // tabela parentesco
        parent::addAttribute('dt_inicial');
        parent::addAttribute('dt_final');
        parent::addAttribute('doc_imagem');
        parent::addAttribute('status_relacao_id');
    }

    public function get_PessoaParentesco()
    {
        return PessoaParentesco::find($this->relacao_id);
    }
}
