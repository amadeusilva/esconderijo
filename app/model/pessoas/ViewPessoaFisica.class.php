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
SELECT    p.id,    p.cpf_cnpj AS cpf,    
p.nome,    p.popular,    pf.genero,    pf.dt_nascimento,    pf.estado_civil_id,    li_estado.item AS estado_civil,    
p.endereco_id,    CONCAT(        CONCAT(            tl.abrev, ' ', l.logradouro        ),        ', nº ', e.n, ', ',       
CONCAT(            b.bairro, ', ',            CONCAT(                c.cidade, '-',                
es.sigla            )        ),        ', ', e.ponto_referencia    ) AS endereco,    p.status_pessoa AS status_pessoa_id,    
li_status.item AS status_pessoa,    p.ck_pessoa
FROM    pessoas.pessoa p
JOIN    pessoas.pessoa_fisica pf ON p.id = pf.pessoa_id 
LEFT JOIN    globais.lista_itens li_estado ON li_estado.id = pf.estado_civil_id
JOIN    enderecos.endereco e ON e.id = p.endereco_id
JOIN    enderecos.logradouro l ON l.id = e.logradouro_id
JOIN    enderecos.bairro b ON b.id = e.bairro_id
JOIN    enderecos.cidade c ON c.id = b.cidade_id
JOIN    enderecos.estado es ON es.id = c.estado_id
JOIN    enderecos.tipo_logradouro tl ON tl.id = l.tipo_id 
LEFT JOIN    globais.lista_itens li_status ON li_status.id = p.status_pessoa
WHERE    p.tipo_pessoa = 1;
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

    public function get_Nascimento()
    {
        return TDate::date2br($this->dt_nascimento);
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
