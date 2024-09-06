<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewEncontreiro extends TRecord
{
    const TABLENAME = 'ecc.view_encontreiro';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW ecc.view_encontreiro AS
SELECT 
            montagem.id,
            montagem.encontro_id,
            CONCAT((SELECT num FROM globais.encontro WHERE encontro.id = montagem.encontro_id), ' ',
            (SELECT (SELECT abrev FROM globais.lista_itens WHERE lista_itens.id = encontro.evento_id) FROM globais.encontro WHERE encontro.id = montagem.encontro_id)) AS encontro,
            montagem.casal_id,
            (SELECT casal FROM pessoas.view_casal WHERE view_casal.relacao_id = montagem.casal_id) AS casal,
            montagem.conducao_propria_id,
            (SELECT placa FROM globais.conducao_propria WHERE conducao_propria.id = montagem.conducao_propria_id) AS conducao_propria,
            montagem.circulo_id,
            (SELECT item FROM globais.lista_itens WHERE lista_itens.id = montagem.circulo_id) AS circulo,
            encontreiro.camisa_encontro_br,
            encontreiro.camisa_encontro_cor,
            encontreiro.disponibilidade_nt,
            encontreiro.coordenar_s_n,
            encontreiro_equipe.funcao_id,  
            encontreiro_equipe.equipe_id,            
            (SELECT equipe FROM globais.equipe WHERE equipe.id = encontreiro_equipe.equipe_id) AS equipe
FROM ecc.montagem, ecc.encontreiro, ecc.encontreiro_equipe WHERE montagem.id = encontreiro.montagem_id AND encontreiro.id = encontreiro_equipe.encontreiro_id
AND montagem.tipo_id = 2
     */

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('encontro_id');
        parent::addAttribute('encontro');
        parent::addAttribute('casal_id');
        parent::addAttribute('casal');
        parent::addAttribute('conducao_propria_id');
        parent::addAttribute('conducao_propria');
        parent::addAttribute('circulo_id');
        parent::addAttribute('circulo');
        parent::addAttribute('camisa_encontro_br');
        parent::addAttribute('camisa_encontro_cor');
        parent::addAttribute('disponibilidade_nt');
        parent::addAttribute('coordenar_s_n');
        parent::addAttribute('funcao_id');
        parent::addAttribute('equipe_id');
        parent::addAttribute('equipe');
    }

    /*
    public function get_VinculoBanco()
    {
        return ListaItens::find($this->estado_civil_id);
    }
        */
}
