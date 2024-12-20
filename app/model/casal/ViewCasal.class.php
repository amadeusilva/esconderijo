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
    relacao.id AS relacao_id,
    parentesco.pessoa_id AS ele_id,
    pessoa1.nome AS ele_nome,
    fisica1.dt_nascimento AS ele_dt_nascimento,
    parentesco.pessoa_parente_id AS ela_id,
    pessoa2.nome AS ela_nome,
    fisica2.dt_nascimento AS ela_dt_nascimento,
    parentesco.parentesco_id,
    lista.item AS parentesco,
    CONCAT(pessoa1.popular, ' & ', pessoa2.popular) AS casal,
    relacao.dt_inicial,
    relacao.dt_final,
    relacao.tipo_vinculo,
    relacao.status_relacao_id
FROM 
    pessoas.pessoas_relacao AS relacao
INNER JOIN 
    pessoas.pessoa_parentesco AS parentesco 
    ON relacao.id = parentesco.relacao_id
INNER JOIN 
    pessoas.pessoa_fisica AS fisica1 
    ON parentesco.pessoa_id = fisica1.pessoa_id AND fisica1.genero = 'M'
INNER JOIN 
    pessoas.pessoa AS pessoa1 
    ON parentesco.pessoa_id = pessoa1.id
INNER JOIN 
    pessoas.pessoa AS pessoa2 
    ON parentesco.pessoa_parente_id = pessoa2.id
LEFT JOIN 
    pessoas.pessoa_fisica AS fisica2 
    ON parentesco.pessoa_parente_id = fisica2.pessoa_id
LEFT JOIN 
    globais.lista_itens AS lista 
    ON parentesco.parentesco_id = lista.id;
     */

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('ele_id');
        parent::addAttribute('ele_nome');
        parent::addAttribute('ele_dt_nascimento');
        parent::addAttribute('ela_id');
        parent::addAttribute('ela_nome');
        parent::addAttribute('ela_dt_nascimento');
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

    public function get_EleNascimento()
    {
        return TDate::date2br($this->ele_dt_nascimento);
    }

    public function get_ElaNascimento()
    {
        return TDate::date2br($this->ela_dt_nascimento);
    }

    public function get_Casamento()
    {
        return TDate::date2br($this->dt_inicial);
    }
}
