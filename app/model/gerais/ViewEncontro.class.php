<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewEncontro extends TRecord
{
    const TABLENAME = 'globais.view_encontro';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW globais.view_encontro AS
SELECT *,
(SELECT abrev FROM globais.lista_itens WHERE lista_itens.id = encontro.evento_id) AS evento,
CONCAT(encontro.num, ' ', (SELECT abrev FROM globais.lista_itens WHERE lista_itens.id = encontro.evento_id)) AS sigla,
(SELECT nome FROM pessoas.pessoa WHERE pessoa.id = encontro.local_id) AS local,
(SELECT
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
        ', ', endereco.ponto_referencia) FROM enderecos.endereco WHERE endereco.id = pessoa.endereco_id)
 FROM pessoas.pessoa WHERE pessoa.id = encontro.local_id) AS endereco,
(SELECT titulo FROM globais.hinario WHERE hinario.id = encontro.cantico_id) AS cantico
FROM globais.encontro;
     */

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('num');
        parent::addAttribute('evento_id');
        parent::addAttribute('evento');
        parent::addAttribute('sigla');
        parent::addAttribute('local_id');
        parent::addAttribute('local');
        parent::addAttribute('endereco');
        parent::addAttribute('dt_inicial');
        parent::addAttribute('dt_final');
        parent::addAttribute('tema');
        parent::addAttribute('divisa');
        parent::addAttribute('cantico_id');
        parent::addAttribute('cantico');
    }

    public function get_NomePopular()
    {
        return Pessoa::find($this->local_id);
    }
}
