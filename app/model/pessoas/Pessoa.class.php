<?php

/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Pessoa extends TRecord
{
    const TABLENAME = 'pessoa';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    //const CREATEDAT = 'created_at';
    //const UPDATEDAT = 'updated_at';

    private $genero;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_pessoa'); //fisica = 77; juridica = 78; igreja = 3;
        parent::addAttribute('cpf_cnpj');
        parent::addAttribute('nome'); //nome completo ou razaão social
        parent::addAttribute('popular'); //apelido ou nome fantansia
        parent::addAttribute('endereco_id');
        parent::addAttribute('status_pessoa');
        parent::addAttribute('ck_pessoa');

        //parent::addAttribute('created_at');
        //parent::addAttribute('updated_at');
    }

    public function get_PessoaFisica()
    {
        return PessoaFisica::find($this->id);
    }

    public function get_TipoPessoa()
    {
        return ListaItens::find($this->tipo_pessoa);
    }

    public function get_Endereco()
    {
        return Endereco::find($this->endereco_id);
    }

    public function get_StatusPessoa()
    {
        return ListaItens::find($this->status_pessoa);
    }

    public function get_EnderecoCompleto()
    {
        $tipo = self::get_Endereco()->Logradouro->Tipo->item;
        $logradouro = self::get_Endereco()->Logradouro->logradouro;
        $n = self::get_Endereco()->n;
        $bairro = self::get_Endereco()->Bairro->bairro;
        $cidade = self::get_Endereco()->Bairro->Cidade->cidade;
        $estado = self::get_Endereco()->Bairro->Cidade->Estado->sigla;

        return $tipo . ' ' . $logradouro . ', Nº ' . $n . ', ' . $bairro . ', ' . $cidade . '-' . $estado;
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;

        PessoaFisica::where('pessoa_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
