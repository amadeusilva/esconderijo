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
AND montagem.tipo_id = 1
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

    public function get_Casamento()
    {
        $casal = new ViewCasal($this->casal_id);
        return TDate::date2br($casal->dt_inicial);
    }

    public function get_DadosCasal()
    {
        return new ViewCasal($this->casal_id);
    }

    public function get_Secretario()
    {
        if ($this->secretario_s_n == 1) {
            $div = new TElement('span');
            $div->class = "label label-success";
            $div->style = "text-shadow:none; font-size:12px";
            $div->add($this->casal);
            return $div;
        } else {
            return $this->casal;
        }
    }

    public function get_Secretario2()
    {
        if ($this->secretario_s_n == 1) {
            $div = new TElement('span');
            $div->class = "label label-success";
            $div->style = "text-shadow:none; font-size:12px";
            $div->add('Sim');
            return $div;
        } else {
            $div = new TElement('span');
            $div->class = "label label-danger";
            $div->style = "text-shadow:none; font-size:12px";
            $div->add('Não');
            return $div;
        }
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
