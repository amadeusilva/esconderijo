<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaParentesco extends TRecord
{
    const TABLENAME = 'pessoas.pessoa_parentesco';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('parentesco_id'); // tabela de lista
        parent::addAttribute('pessoa_parente_id');
        parent::addAttribute('relacao_id');
        parent::addAttribute('obs_parentesco');
    }

    public function get_Pessoa()
    {
        return ViewPessoaFisica::find($this->pessoa_id);
    }

    public function get_Parentesco()
    {
        return ListaItens::find($this->parentesco_id);
    }

    public function get_PessoaParente()
    {
        return ViewPessoaFisica::find($this->pessoa_parente_id);
    }

    public function delete2($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        PessoasRelacao::where('id', '=', $this->relacao_id)->delete();
        parent::delete($id);
    }
}
