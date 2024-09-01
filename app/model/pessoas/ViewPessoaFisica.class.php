<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewPessoaFisica extends TRecord
{
    const TABLENAME = 'pessoas.view_pessoa_fisica';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW pessoas.view_pessoa_fisica AS
SELECT
	pessoa.id,
    pessoa.cpf_cnpj AS cpf,
    pessoa.nome,
    pessoa.popular,
    pessoa_fisica.genero,
    pessoa_fisica.dt_nascimento,
    pessoa_fisica.estado_civil_id,
    (SELECT item FROM globais.lista_itens WHERE lista_itens.id = pessoa_fisica.estado_civil_id) AS estado_civil,
    pessoa.endereco_id,
    (SELECT CONCAT(
        (SELECT CONCAT(
            (SELECT tipo_logradouro.abrev FROM enderecos.tipo_logradouro WHERE tipo_logradouro.id = logradouro.tipo_id),
            ' ', logradouro.logradouro) FROM enderecos.logradouro WHERE logradouro.id = endereco.logradouro_id),
        ', nº ', endereco.n, ', ', 
        (SELECT 
         CONCAT(bairro.bairro, ', ', 
                (SELECT 
                 CONCAT(cidade.cidade, '-', 
                        (SELECT estado.sigla FROM enderecos.estado WHERE estado.id = cidade.estado_id)
                       )
                 FROM enderecos.cidade WHERE cidade.id = bairro.cidade_id)
               )
         FROM enderecos.bairro WHERE bairro.id = endereco.bairro_id), 
        ', ', endereco.ponto_referencia) FROM enderecos.endereco WHERE endereco.id = pessoa.endereco_id) AS endereco,
        pessoa.status_pessoa AS status_pessoa_id,
    (SELECT item FROM globais.lista_itens WHERE lista_itens.id = pessoa.status_pessoa) AS status_pessoa,
    pessoa.ck_pessoa
FROM pessoas.pessoa, pessoas.pessoa_fisica WHERE pessoa.id = pessoa_fisica.pessoa_id
AND pessoa.tipo_pessoa = 1
     */

    private $fone;

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
        parent::addAttribute('estado_civil_id');
        parent::addAttribute('estado_civil');
        parent::addAttribute('endereco_id');
        parent::addAttribute('endereco');
        parent::addAttribute('status_pessoa_id');
        parent::addAttribute('status_pessoa');
        parent::addAttribute('ck_pessoa');
    }

    public function get_Fone()
    {
        if (empty($this->fone)) {
            $this->fone = PessoaContato::where('pessoa_id', '=', $this->id)->where('tipo_contato_id', '=', 101)->first();
        }

        return $this->fone;
    }

    public function get_VinculoBanco()
    {
        return ListaItens::find($this->estado_civil_id);
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        PessoaFisica::where('pessoa_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
