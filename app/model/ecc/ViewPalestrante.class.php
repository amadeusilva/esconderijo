<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewPalestrante extends TRecord
{
    const TABLENAME = 'ecc.view_palestrante';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW ecc.view_palestrante AS
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
            palestrante.palestra_id,
            (SELECT item FROM globais.lista_itens WHERE lista_itens.id = palestrante.palestra_id) AS palestra
FROM ecc.montagem, ecc.encontreiro, ecc.palestrante WHERE montagem.id = encontreiro.montagem_id AND encontreiro.id = palestrante.encontreiro_id
AND montagem.tipo_id = 3
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
        parent::addAttribute('palestra_id');
        parent::addAttribute('palestra');
    }

    public function get_DadosCasal()
    {
        return new ViewCasal($this->casal_id);
    }

    public function get_CirculoCor()
    {
        $circulo_cor = ListaItens::where('item', '=', $this->circulo)->first();
        $div = new TElement('span');
        $div->class = "label";
        $div->style = "text-shadow:none; font-size:12px; color: black; background-color: $circulo_cor->obs;";
        $div->add($this->circulo);
        return $div;
    }
}
