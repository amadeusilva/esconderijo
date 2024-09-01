<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Encontro extends TRecord
{
    const TABLENAME = 'globais.encontro';
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
        parent::addAttribute('num');
        parent::addAttribute('evento_id');
        parent::addAttribute('local_id');
        parent::addAttribute('dt_inicial');
        parent::addAttribute('dt_final');
        parent::addAttribute('tema');
        parent::addAttribute('divisa');
        parent::addAttribute('cantico_id');
        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

    public function get_Evento()
    {
        return ListaItens::find($this->evento_id);
    }

    public function get_Local()
    {
        return Pessoa::find($this->local_id);
    }

    public function get_Cantico()
    {
        return Hinario::find($this->cantico_id);
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        PessoaPapel::where('pessoa_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
