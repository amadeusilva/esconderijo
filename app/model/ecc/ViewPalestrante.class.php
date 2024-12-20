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
SELECT     m.id,    m.encontro_id,    CONCAT(e.num, ' ', li.abrev) AS encontro,    m.casal_id,    vc.casal AS casal,    
m.conducao_propria_id,    cp.placa AS conducao_propria,    m.circulo_id,    li_c.item AS circulo,    
e_cam.camisa_encontro_br,    e_cam.camisa_encontro_cor,    e_cam.disponibilidade_nt,    e_cam.coordenar_s_n,    
ee.funcao_id,      ee.equipe_id AS palestra_id,                li_e.item AS palestra
FROM     ecc.montagem m
JOIN     ecc.encontreiro e_cam ON m.id = e_cam.montagem_id
JOIN     ecc.encontreiro_equipe ee ON e_cam.id = ee.encontreiro_id
JOIN     globais.encontro e ON e.id = m.encontro_id
JOIN     globais.lista_itens li ON li.id = e.evento_id
JOIN     pessoas.view_casal vc ON vc.relacao_id = m.casal_id
LEFT JOIN     globais.conducao_propria cp ON cp.id = m.conducao_propria_id
LEFT JOIN     globais.lista_itens li_c ON li_c.id = m.circulo_id
LEFT JOIN     globais.lista_itens li_e ON li_e.id = ee.equipe_id
WHERE     m.tipo_id = 2     AND ee.tipo_enc_id = 2;
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
        parent::addAttribute('palestra_id');
        parent::addAttribute('palestra');
    }

    public function get_DadosCasal()
    {
        return new ViewCasal($this->casal_id);
    }

    public function get_CamisaEncontroBr()
    {
        if ($this->camisa_encontro_br == 1) {
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

    public function get_Coordenar()
    {
        if ($this->coordenar_s_n == 1) {
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

    public function get_Funcao()
    {
        if ($this->funcao_id == 1) {
            $div = new TElement('span');
            $div->class = "label label-success";
            $div->style = "text-shadow:none; font-size:12px";
            $div->add('Palestrante');
            return $div;
        } else if ($this->funcao_id == 2) {
            $div = new TElement('span');
            $div->class = "label label-warning";
            $div->style = "text-shadow:none; font-size:12px";
            $div->add('Apoio');
            return $div;
        } else {
            $div = new TElement('span');
            $div->class = "label label-info";
            $div->style = "text-shadow:none; font-size:12px";
            $div->add('Membro');
            return $div;
        }
    }
}
