<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewEncontrista extends TRecord
{
    const TABLENAME = 'ecc.view_encontrista';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW ecc.view_encontrista AS
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
            encontrista.secretario_s_n,
            encontrista.casal_convite_id,
            (SELECT casal FROM pessoas.view_casal WHERE view_casal.relacao_id = encontrista.casal_convite_id) AS casal_convite
FROM ecc.montagem, ecc.encontrista WHERE montagem.id = encontrista.montagem_id
AND montagem.tipo_encontr_ista_eiro = 1
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
        parent::addAttribute('secretario_s_n');
        parent::addAttribute('casal_convite_id');
        parent::addAttribute('casal_convite');
    }

    /*
    public function get_VinculoBanco()
    {
        return ListaItens::find($this->estado_civil_id);
    }
        */
}
