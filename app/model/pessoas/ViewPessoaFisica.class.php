<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewPessoaFisica extends TRecord
{
    const TABLENAME = 'view_pessoa_fisica';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW view_pessoa_fisica AS
SELECT
	pessoa.id,
    pessoa.cpf_cnpj AS cpf,
    pessoa.nome,
    pessoa.popular,
    pessoa_fisica.genero,
    pessoa_fisica.dt_nascimento,
    (SELECT item FROM lista_itens WHERE lista_itens.id = pessoa_fisica.estado_civil_id) AS estado_civil,
    (SELECT CONCAT(
        (SELECT CONCAT(
            (SELECT tipo_logradouro.abrev FROM tipo_logradouro WHERE tipo_logradouro.id = logradouro.tipo_id),
            ' ', logradouro.logradouro) FROM logradouro WHERE logradouro.id = endereco.logradouro_id),
        ', nº ', endereco.n, ', ', 
        (SELECT 
         CONCAT(bairro.bairro, ', ', 
                (SELECT 
                 CONCAT(cidade.cidade, '-', 
                        (SELECT estado.sigla FROM estado WHERE estado.id = cidade.estado_id)
                       )
                 FROM cidade WHERE cidade.id = bairro.cidade_id)
               )
         FROM bairro WHERE bairro.id = endereco.bairro_id), 
        ', ', endereco.ponto_referencia) FROM endereco WHERE endereco.id = pessoa.endereco_id) AS endereco,
    (SELECT item FROM lista_itens WHERE lista_itens.id = pessoa.status_pessoa) AS status_pessoa
FROM pessoa, pessoa_fisica WHERE pessoa.id = pessoa_fisica.pessoa_id
AND pessoa.tipo_pessoa = 1
     */

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cpf');
        parent::addAttribute('nome');
        parent::addAttribute('popular');
        parent::addAttribute('genero');
        parent::addAttribute('dt_nascimento');
        parent::addAttribute('estado_civil');
        parent::addAttribute('endereco');
        parent::addAttribute('status_pessoa');
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        PessoaFisica::where('pessoa_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
