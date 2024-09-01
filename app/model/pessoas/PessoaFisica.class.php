<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class PessoaFisica extends TRecord
{
    const TABLENAME = 'pessoas.pessoa_fisica';
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
        parent::addAttribute('pessoa_id'); //fisica = 1; juridica = 2
        parent::addAttribute('genero'); //m ou f
        parent::addAttribute('dt_nascimento');
        parent::addAttribute('estado_civil_id');
        parent::addAttribute('tm_camisa');

        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

    public function get_Pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }

    public function get_EstadoCivil()
    {
        return ListaItens::find($this->estado_civil_id);
    }

    public function get_TmCamisa()
    {
        return ListaItens::find($this->tm_camisa);
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        Pessoa::where('id', '=', $this->pessoa_id)->delete();
        parent::delete($id);
    }
}
