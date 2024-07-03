<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Casal extends TRecord
{
    const TABLENAME = 'casal';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    //const CREATEDAT = 'created_at';
    //const UPDATEDAT = 'updated_at';

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('ele_id');
        parent::addAttribute('ela_id');
        parent::addAttribute('dt_casamento');
        parent::addAttribute('cartorio_id');
        parent::addAttribute('conducao_propria');
        parent::addAttribute('status_casal');
        parent::addAttribute('ck_casal');

        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

    public function get_Ele()
    {
        return Pessoa::find($this->ele_id);
    }

    public function get_Ela()
    {
        return Pessoa::find($this->ela_id);
    }

    public function get_Cartorio()
    {
        return Pessoa::find($this->cartorio_id);
    }

    public function get_ConducaoPropria()
    {
        if ($this->conducao_propria == 0) {
            return 'Não';
        } else {
            return ConducaoPropria::find($this->conducao_propria)->placa . ' (' . ConducaoPropria::find($this->conducao_propria)->detalhes_conducao . ')';
        }
    }

    public function get_StatusCasal()
    {
        return ListaItens::find($this->status_casal);
    }

}
