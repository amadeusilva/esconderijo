<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class EncontroCirculos extends TRecord
{
    const TABLENAME = 'ecc.encontro_circulos';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('encontro_id');
        parent::addAttribute('circulo_id');
        parent::addAttribute('nome_circulo');
        parent::addAttribute('casal_coord_id');
        parent::addAttribute('casal_sec_id');
    }

    public function get_Encontro()
    {
        return ViewEncontro::find($this->encontro_id);
    }

    public function get_Circulo()
    {
        return ListaItens::find($this->circulo_id);
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

    public function get_CasalCoord()
    {
        return ViewCasal::find($this->casal_coord_id);
    }

    public function get_CasalSec()
    {
        return ViewCasal::find($this->casal_sec_id);
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        EncontreiroEquipe::where('encontreiro_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
