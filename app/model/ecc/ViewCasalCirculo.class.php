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
            ch.id,
            ch.casal_id,
            vc.casal,
            vc.dt_inicial AS dt_casamento,    
            ch.circulo_id
        FROM    ecc.circulo_historico ch
            JOIN     pessoas.view_casal vc ON ch.casal_id = vc.relacao_id
            JOIN (    SELECT         casal_id,         MAX(id) AS max_id    FROM         ecc.circulo_historico   
                GROUP BY         casal_id) AS max_ch ON ch.casal_id = max_ch.casal_id AND ch.id = max_ch.max_id
                ORDER BY     ch.id ASC;
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
        //$casal = new ViewCasal($this->casal_id);
        return TDate::date2br($this->dt_casamento);
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
