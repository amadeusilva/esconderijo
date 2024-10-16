<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class CirculoHistorico extends TRecord
{
    const TABLENAME = 'ecc.circulo_historico';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('user_sessao_id');
        parent::addAttribute('casal_id');
        parent::addAttribute('circulo_id');
        parent::addAttribute('motivo_id');
        parent::addAttribute('obs_motivo');
        parent::addAttribute('dt_historico');
    }

    public function get_DadosCasal()
    {
        return new ViewCasal($this->casal_id);
    }

    public function get_CirculoMotivo()
    {
        return ListaItens::find($this->motivo_id);
    }

    public function get_CirculoCor()
    {
        $circulo_cor = ListaItens::where('id', '=', $this->circulo_id)->first();
        $div = new TElement('span');
        $div->class = "label";
        $div->style = "text-shadow:none; font-size:12px; color: black; background-color: $circulo_cor->obs;";
        $div->add($circulo_cor->item);
        return $div;
    }
}
