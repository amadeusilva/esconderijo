<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class ViewCasalCirculo extends TRecord
{
    const TABLENAME = 'ecc.view_casal_circulo';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
	CREATE VIEW ecc.view_casal_circulo AS
SELECT
circulo_historico.id,
circulo_historico.casal_id,
view_casal.casal,
view_casal.dt_inicial as dt_casamento,
circulo_historico.circulo_id
FROM ecc.circulo_historico, pessoas.view_casal
WHERE circulo_historico.casal_id = view_casal.relacao_id AND id IN (
	SELECT MAX(id) 
	FROM ecc.circulo_historico 
	GROUP BY casal_id
) order by id asc;
     */

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('casal_id');
        parent::addAttribute('casal');
        parent::addAttribute('dt_casamento');
        parent::addAttribute('circulo_id');
    }

    public function get_Casamento()
    {
        $casal = new ViewCasal($this->casal_id);
        return TDate::date2br($casal->dt_casamento);
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
