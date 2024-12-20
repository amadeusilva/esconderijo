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
SELECT     montagem.id,    montagem.encontro_id,    CONCAT(encontro.num, ' ', evento.abrev) AS encontro,    
montagem.casal_id,    casal.casal AS casal,    montagem.conducao_propria_id,    conducao_propria.placa AS conducao_propria,    
montagem.circulo_id,    circulo.item AS circulo,    encontreiro.camisa_encontro_br,    encontreiro.camisa_encontro_cor,    
encontreiro.disponibilidade_nt,    encontreiro.coordenar_s_n,    encontreiro_equipe.funcao_id,      encontreiro_equipe.equipe_id,                
equipe.equipe AS equipe
FROM     ecc.montagem
JOIN     ecc.encontreiro ON montagem.id = encontreiro.montagem_id
JOIN     ecc.encontreiro_equipe ON encontreiro.id = encontreiro_equipe.encontreiro_id
JOIN     globais.encontro ON encontro.id = montagem.encontro_id
JOIN     globais.lista_itens AS evento ON evento.id = encontro.evento_id
JOIN     pessoas.view_casal AS casal ON casal.relacao_id = montagem.casal_id
LEFT JOIN     globais.conducao_propria ON conducao_propria.id = montagem.conducao_propria_id
LEFT JOIN     globais.lista_itens AS circulo ON circulo.id = montagem.circulo_id
LEFT JOIN     globais.equipe ON equipe.id = encontreiro_equipe.equipe_id
WHERE     montagem.tipo_id = 2     AND encontreiro_equipe.tipo_enc_id = 1;
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

    public function get_DadosCasal()
    {
        return new ViewCasal($this->casal_id);
    }

    public function get_AnoEcc()
    {
        $encontro = new Encontro($this->encontro_id);

        $date = new DateTime($encontro->dt_inicial);
        return $date->format('Y');
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
            $div->add('Coordenador');
            return $div;
        } else if ($this->funcao_id == 2) {
            $div = new TElement('span');
            $div->class = "label label-warning";
            $div->style = "text-shadow:none; font-size:12px";
            $div->add('Adjunto');
            return $div;
        } else if ($this->funcao_id == 3) {
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
