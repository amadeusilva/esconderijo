<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewCasal extends TRecord
{
    const TABLENAME = 'pessoas.view_casal';
    const PRIMARYKEY = 'relacao_id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW pessoas.view_casal AS
        SELECT
            pessoas_relacao.id as relacao_id,
            pessoa_parentesco.pessoa_id AS ele_id,
            pessoa_parentesco.pessoa_parente_id AS ela_id,
            pessoa_parentesco.parentesco_id,
            (SELECT item FROM globais.lista_itens WHERE lista_itens.id = pessoa_parentesco.parentesco_id) AS parentesco,
            CONCAT((SELECT popular FROM pessoas.pessoa WHERE pessoa.id = pessoa_parentesco.pessoa_id), ' & ',
            (SELECT popular FROM pessoas.pessoa WHERE pessoa.id = pessoa_parentesco.pessoa_parente_id)) AS casal,
            pessoas_relacao.dt_inicial,
            pessoas_relacao.dt_final,
            pessoas_relacao.tipo_vinculo,
            pessoas_relacao.status_relacao_id
        FROM pessoas.pessoa_fisica, pessoas.pessoa_parentesco, pessoas.pessoas_relacao WHERE
            pessoas_relacao.id = pessoa_parentesco.relacao_id AND pessoa_fisica.pessoa_id = pessoa_parentesco.pessoa_id AND pessoa_fisica.genero = 'M';
     */

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('ele_id');
        parent::addAttribute('ela_id');
        parent::addAttribute('parentesco_id');
        parent::addAttribute('parentesco');
        parent::addAttribute('casal');
        parent::addAttribute('dt_inicial');
        parent::addAttribute('dt_final');
        parent::addAttribute('tipo_vinculo');
        parent::addAttribute('status_relacao_id');
    }

    public function get_Ele()
    {
        return ViewPessoaFisica::find($this->ele_id);
    }

    public function get_Ela()
    {
        return ViewPessoaFisica::find($this->ela_id);
    }
    
}
