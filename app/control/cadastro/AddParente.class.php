<?php

use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TRadioGroup;

/**
 * CityWindow Registration
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class AddParente extends TWindow
{

    use ControlePessoas;
    protected $form; // form

    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();
        parent::setModal(true);
        parent::removePadding();
        parent::setSize(350, null);
        parent::setTitle('Vincular Pessoa');

        $this->setDatabase('adea');    // defines the database
        $this->setActiveRecord('Pessoa');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_PessoaParente');
        $this->form->setClientValidation(true);

        $pessoa_painel = TSession::getValue('pessoa_painel');

        // create the form fields
        $filter = new TCriteria;
        $filter->add(new TFilter('lista_id', '=', 12));

        if (isset($param['vinculo']) and $param['vinculo'] == 3) {
            $filter->add(new TFilter('id', '=', $param['parentesco_id']));
        } else {
            $filter->add(new TFilter('ck', '=', 1));
        }

        if (isset($pessoa_painel->id) and !empty($pessoa_painel->id) and isset($param['vinculo']) and $param['vinculo'] != 3) {

            try {
                TTransaction::open('adea');

                //verifica parentes no banco
                $pessoa_parente_painel = PessoaParentesco::where('pessoa_id', '=', $pessoa_painel->id)->load();

                //bloco comentado porque nao aparecia esposa para o separado
                if ($param['vinculo'] == 2) {
                    $filter->add(new TFilter('id', '!=', 921));
                    $filter->add(new TFilter('id', '!=', 923));
                    $filter->add(new TFilter('id', '!=', 925));
                    $filter->add(new TFilter('id', '!=', 922));
                    $filter->add(new TFilter('id', '!=', 924));
                    $filter->add(new TFilter('id', '!=', 926));
                }

                foreach ($pessoa_parente_painel as $pes_par_pai) {
                    if ($pes_par_pai->parentesco_id == 901 or $pes_par_pai->parentesco_id == 902) {
                        $filter->add(new TFilter('id', '!=', $pes_par_pai->parentesco_id));
                    }
                    if ($pes_par_pai->parentesco_id >= 921 and $pes_par_pai->parentesco_id <= 926) {
                        $filter->add(new TFilter('id', '!=', 921));
                        $filter->add(new TFilter('id', '!=', 922));
                        $filter->add(new TFilter('id', '!=', 923));
                        $filter->add(new TFilter('id', '!=', 924));
                        $filter->add(new TFilter('id', '!=', 925));
                        $filter->add(new TFilter('id', '!=', 926));
                    }
                }
                TTransaction::close();
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage());
            }
        }
        //estado civil: 803-804: convivent / 805-806: ue / 807-808: casad
        //parentesco: 921-922: espos / 923-924: companheir / 925-926: convivente
        if (TSession::getValue('dados_iniciais_pf') and isset($param['vinculo']) and $param['vinculo'] != 3) {

            $filtroini = TSession::getValue('dados_iniciais_pf');
            if ($filtroini['genero'] == 'M') {
                if ($filtroini['estado_civil_id'] == 807) {
                    $filter->add(new TFilter('id', '!=', 924));
                    $filter->add(new TFilter('id', '!=', 926));
                } else if ($filtroini['estado_civil_id'] == 805) {
                    $filter->add(new TFilter('id', '!=', 922));
                    $filter->add(new TFilter('id', '!=', 926));
                } else if ($filtroini['estado_civil_id'] == 803) {
                    $filter->add(new TFilter('id', '!=', 924));
                    $filter->add(new TFilter('id', '!=', 922));
                } else {
                    $filter->add(new TFilter('id', '!=', 922));
                    $filter->add(new TFilter('id', '!=', 924));
                    $filter->add(new TFilter('id', '!=', 926));
                }
                $filter->add(new TFilter('id', '!=', 921));
                $filter->add(new TFilter('id', '!=', 923));
                $filter->add(new TFilter('id', '!=', 925));
            } else {
                if ($filtroini['estado_civil_id'] == 808) {
                    $filter->add(new TFilter('id', '!=', 923));
                    $filter->add(new TFilter('id', '!=', 925));
                } else if ($filtroini['estado_civil_id'] == 806) {
                    $filter->add(new TFilter('id', '!=', 921));
                    $filter->add(new TFilter('id', '!=', 925));
                } else if ($filtroini['estado_civil_id'] == 804) {
                    $filter->add(new TFilter('id', '!=', 923));
                    $filter->add(new TFilter('id', '!=', 921));
                } else {
                    $filter->add(new TFilter('id', '!=', 921));
                    $filter->add(new TFilter('id', '!=', 923));
                    $filter->add(new TFilter('id', '!=', 925));
                }
                $filter->add(new TFilter('id', '!=', 922));
                $filter->add(new TFilter('id', '!=', 924));
                $filter->add(new TFilter('id', '!=', 926));
            }
        }
        if (TSession::getValue('dados_parentes_pf') and isset($param['vinculo']) and $param['vinculo'] != 3) {
            $filtropar = TSession::getValue('dados_parentes_pf');
            foreach ($filtropar as $filtro) {
                if ($filtro->parentesco_id == 901 or $filtro->parentesco_id == 902) {
                    $filter->add(new TFilter('id', '!=', $filtro->parentesco_id));
                }
                if ($filtro->parentesco_id >= 921 and $filtro->parentesco_id <= 926) {
                    $filter->add(new TFilter('id', '!=', $filtro->parentesco_id));
                }
            }
        }

        // create the form fields
        $id       = new TEntry('id');
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $vinculo       = new TEntry('vinculo');
        $vinculo->setEditable(FALSE);
        $vinculo->setSize('100%');
        $row = $this->form->addFields([new TLabel('Cod.:', 'red'), $id], [new TLabel('Ref.:', 'red'), $vinculo]);
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $parentesco_id = new TDBCombo('parentesco_id', 'adea', 'ListaItens', 'id', '{item} {abrev}', 'id', $filter);
        $parentesco_id->enableSearch();

        $cpf       = new TEntry('cpf');
        $acaocpf = new TAction(array($this, 'onConsultaCPF'));
        $cpf->setExitAction($acaocpf);
        $cpf->setMask('999.999.999-99');
        $nome  = new TDBEntry('nome', 'adea', 'Pessoa', 'nome');
        $nome->setInnerIcon(new TImage('fa:user blue'), 'left');
        $nome->placeholder = ' Nome Completo';
        $nome->forceUpperCase();
        $popular  = new TDBEntry('popular', 'adea', 'Pessoa', 'popular');
        $popular->setInnerIcon(new TImage('fa:user blue'), 'left');
        $popular->placeholder = ' Nome pelo qual é conhecido ou gosta de ser chamado';
        $popular->forceUpperCase();
        $genero         = new TCombo('genero');
        $genero->addItems(['M' => 'Masculino', 'F' => 'Feminino']);
        $dt_nascimento  = new TDate('dt_nascimento');
        $dt_nascimento->setMask('dd/mm/yyyy');
        $acaodtnascimento = new TAction(array($this, 'verificaNomeDtnascimento'));
        $dt_nascimento->setExitAction($acaodtnascimento);
        $idade = new TEntry('idade');

        $options = ['s' => 'Sim', 'n' => 'Não'];

        $moracomigo = new TRadioGroup('moracomigo');
        $moracomigo->setUseButton();
        $moracomigo->addItems($options);
        $moracomigo->setLayout('horizontal');
        $moracomigo->setValue('s');
        $moracomigo->setEditable(FALSE);
        $moracomigo->setSize('100%');

        $atualizacao = new TRadioGroup('atualizacao');
        $atualizacao->setUseButton();
        $atualizacao->addItems($options);
        $atualizacao->setLayout('horizontal');
        $atualizacao->setValue('s');
        $atualizacao->setEditable(FALSE);
        $atualizacao->setSize('100%');

        $cpf->setEditable(FALSE);
        $nome->setEditable(FALSE);
        $popular->setEditable(FALSE);
        $genero->setEditable(FALSE);
        $dt_nascimento->setEditable(FALSE);
        $idade->setEditable(FALSE);
        $parentesco_id->setSize('100%');
        $cpf->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $genero->setSize('100%');
        $dt_nascimento->setSize('100%');
        $idade->setSize('100%');

        // add the form fields
        $row = $this->form->addFields([new TLabel('Grau', 'red'), $parentesco_id]);
        $row->layout = ['col-sm-12'];
        $row = $this->form->addFields([new TLabel('CPF', 'red'), $cpf]);
        $row->layout = ['col-sm-12'];
        $row = $this->form->addFields([new TLabel('Nome Completo', 'red'), $nome]);
        $row->layout = ['col-sm-12'];
        $row = $this->form->addFields([new TLabel('Nome Popular', 'red'), $popular]);
        $row->layout = ['col-sm-12'];
        $row = $this->form->addFields([new TLabel('Gênero', 'red'), $genero]);
        $row->layout = ['col-sm-12'];
        $row = $this->form->addFields([new TLabel('Nascimento', 'red'), $dt_nascimento]);
        $row->layout = ['col-sm-12'];
        $row = $this->form->addFields([new TLabel('Idade', 'red'), $idade]);
        $row->layout = ['col-sm-12'];
        $row = $this->form->addFields([new TLabel('Mora comigo?', 'red')], [$moracomigo]);
        $row->layout = ['col-sm-5', 'col-sm-7'];
        $row = $this->form->addFields([new TLabel('Verificado?', 'red')], [$atualizacao]);
        $row->layout = ['col-sm-5', 'col-sm-7'];

        // set exit action for input_exit
        $change_action = new TAction(array($this, 'onChangeAction'));
        $parentesco_id->setChangeAction($change_action);
        self::onChangeAction($param);

        $parentesco_id->addValidation('Grau', new TRequiredValidator);
        $cpf->addValidation('CPF', new TCPFValidator);
        $nome->addValidation('Nome', new TRequiredValidator);
        $popular->addValidation('Nome Popular', new TRequiredValidator);
        $genero->addValidation('Gênero', new TRequiredValidator);
        $dt_nascimento->addValidation('Nascimento', new TRequiredValidator);
        $idade->addValidation('Idade', new TRequiredValidator);
        $moracomigo->addValidation('Mora comigo?', new TRequiredValidator);
        $atualizacao->addValidation('Dados Verificados?', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Inserir', new TAction(array($this, 'onSave'), ['vinculo' => $param['vinculo']]), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    public function onEditParente($param)
    {

        try {
            if (isset($param['id'])) {
                $key = $param['id'];  // get the parameter
                TTransaction::open('adea');   // open a transaction with database 'samples'

                $pessoa_painel = TSession::getValue('pessoa_painel');

                if ($param['atualizacao'] == 'n') {
                    $object = new ViewPessoaFisica($key);        // instantiates object City
                    $object->dt_nascimento =  TDate::date2br($object->dt_nascimento);
                    $object->idade = self::onCalculaIdadeParente($object->dt_nascimento);
                    $object->moracomigo = ($object->endereco_id == $pessoa_painel->endereco_id) ? 's' : 'n';
                } else {
                    $dados_parentes_pf = TSession::getValue('dados_parentes_pf');
                    foreach ($dados_parentes_pf as $pessoa_procurada) {
                        if ($pessoa_procurada->id == $param['id']) {
                            $object = $pessoa_procurada;
                        }
                    }
                }


                if (isset($object->cpf) and !empty($object->cpf)) {
                    $object->parentesco_id = $param['parentesco_id'];
                    TButton::enableField('form_PessoaParente', 'inserir');
                } else {
                    $object->dt_nascimento =  '';
                    TEntry::enableField('form_PessoaParente', 'cpf');
                }

                $object->vinculo = $param['vinculo'];


                $this->form->setData($object);   // fill the form with the active record data
                TTransaction::close();           // close the transaction
            } else {
                $this->form->clear(true);
                $this->onLoad($param);
            }
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public static function onCalculaIdadeParente($param)
    {
        if (isset($param) and !empty($param)) {


            //converte a data static BR para Americana
            $novadata = DateTime::createFromFormat('d/m/Y', $param);
            $param = $novadata->format('Y/m/d');
            $interval = $novadata->diff(new DateTime(date('Y-m-d')));

            $idade_cauculada = new stdClass;
            $idade_cauculada->idade = $interval->format('%Y anos');

            TForm::sendData('form_PessoaParente', $idade_cauculada);
        }
    }

    public static function verificaNomeDtnascimento($param)
    {
        try {
            TTransaction::open('adea');
            if ($param['cpf'] and $param['nome'] and $param['dt_nascimento']) {

                //self::onCalculaTempo(TDate::date2br($param['dt_nascimento']));
                self::onCalculaIdadeParente($param['dt_nascimento']);

                $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_nascimento']);
                $param['dt_nascimento'] = $novadata->format('Y/m/d');

                $pf = ViewPessoaFisica::where('nome', '=', $param['nome'])->where('dt_nascimento', '=', $param['dt_nascimento'])->first();

                if ($pf) {
                    if (!empty($pf->cpf) and $pf->cpf != $param['cpf']) {
                        throw new Exception('<b>Atenção:</b> Você não pode vincular<b> ' . $pf->nome . ' (' . $novadata->format('d/m/Y') . ')</b>.<br>Pessoa EXISTENTE em outro CPF. <br> Se acreditar que estes dados estão incorretos, entre em contato com o Administrador do sistema!');
                    } else {
                        TButton::enableField('form_PessoaParente', 'inserir');
                    }
                } else {
                    TButton::enableField('form_PessoaParente', 'inserir');
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $pfvazia = new stdClass;
            $pfvazia->cpf = '';
            $pfvazia->nome = '';
            $pfvazia->popular = '';
            $pfvazia->dt_nascimento = '';
            TForm::sendData('form_PessoaParente', $pfvazia);
        }
    }

    /**
     * on ChangeRadio change
     * @param $param Action parameters
     */

    public function onLoad($param)
    {
        if (isset($param)) {
            TForm::sendData('form_PessoaParente', $param);
        }
    }

    public function onSave($param)
    {

        try {

            TTransaction::open('adea');

            //$this->form->validate();
            $data = $this->form->getData();

            $this->form->setData($data); // put the data back to the form

            //verificar mudança de nome e dn do banco
            if (isset($data->id) and !empty($data->id)) {
                $pessoa = ViewPessoaFisica::find($data->id);
                $pessoa->dt_nascimento =  TDate::date2br($pessoa->dt_nascimento);
                if ($pessoa->ck_pessoa != 0) {
                    if ($pessoa->nome != $data->nome or $pessoa->popular != $data->popular or $pessoa->dt_nascimento != $data->dt_nascimento) {
                        throw new Exception('<b>Atenção!</b> Encontramos a pessoa: <b>' . $pessoa->nome . ' (' . $pessoa->dt_nascimento . ')</b> REGISTRADO com o código <b>' . $pessoa->id .  '</b>. Se acreditar que estes dados estão incorretos, entre em contato com o Administrador do sistema!');
                    }
                }
            }


            if ($param['vinculo'] == 2) {

                $pessoa_painel = TSession::getValue('pessoa_painel');

                $data->pessoa_id = $pessoa_painel->id;
                $pessoa_parente_banco = ViewPessoaFisica::where('cpf', '=', $data->cpf)->first();
                if ($pessoa_parente_banco) {
                    $data->pessoa_parente_id = $pessoa_parente_banco->id;
                } else {
                    $pessoa_nova = new Pessoa();
                    $pessoa_nova->tipo_pessoa = 1;
                    $pessoa_nova->status_pessoa = 21;
                    $pessoa_nova->cpf_cnpj = $data->cpf;
                    $pessoa_nova->nome = $data->nome;
                    $pessoa_nova->popular = $data->popular;
                    if ($data->moracomigo == 's') {
                        $pessoa_nova->endereco_id = Pessoa::find($pessoa_painel->id)->endereco_id;
                    }
                    $pessoa_nova->store();

                    PessoaFisica::where('pessoa_id', '=', $pessoa_nova->id)->delete();
                    $pessoa_fisica_nova = new PessoaFisica();
                    $pessoa_fisica_nova->pessoa_id = $pessoa_nova->id;
                    $pessoa_fisica_nova->genero = $data->genero;
                    $novadatanatpn = DateTime::createFromFormat('d/m/Y', $data->dt_nascimento);
                    $data->dt_nascimento = $novadatanatpn->format('Y/m/d');
                    $pessoa_fisica_nova->dt_nascimento = $data->dt_nascimento;
                    if ($pessoa_fisica_nova->genero == 'M') {
                        $pessoa_fisica_nova->estado_civil_id = 801;
                    } else {
                        $pessoa_fisica_nova->estado_civil_id = 802;
                    }
                    $pessoa_fisica_nova->store();

                    $data->pessoa_parente_id = $pessoa_nova->id;
                }

                $this->onSalvaParente($data);

                self::onClose();

                $posAction = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => $pessoa_painel->id, 'register_state' => 'false']);

                new TMessage('info', 'Pessoa vinculada com sucesso!', $posAction);
            } else if ($param['vinculo'] == 1 or $param['vinculo'] == 3) {

                $dados_iniciais_pf = TSession::getValue('dados_iniciais_pf');
                //verificar se tem outro conjugue
                if ($data->parentesco_id >= 921 and $data->parentesco_id <= 926 and isset($dados_iniciais_pf['id']) and !empty($dados_iniciais_pf['id'])) {

                    $conjugue_vinculado = PessoaParentesco::where('pessoa_id', '=', $dados_iniciais_pf['id'])->where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->first();

                    //$conjugue_vinculado = array();

                    if ($conjugue_vinculado) {
                        if ($data->id != $conjugue_vinculado->pessoa_parente_id) {
                            if ($conjugue_vinculado->PessoaParente->cpf_cnpj) {
                                if ($data->cpf != $conjugue_vinculado->PessoaParente->cpf_cnpj) {
                                    throw new Exception('<b>Atenção!</b> Você já possui este vínculo com <b>' . $conjugue_vinculado->PessoaParente->nome . '</b>!');
                                }
                            }
                            throw new Exception('<b>Atenção!</b> Você já possui este vínculo com <b>' . $conjugue_vinculado->PessoaParente->nome . '</b>!');
                        }
                    }
                }

                $dadosparentespf = TSession::getValue('dados_parentes_pf');

                if (isset($dadosparentespf)) {

                    foreach ($dadosparentespf as $pps) {

                        if ($pps->parentesco_id >= 921 and $pps->parentesco_id <= 926 and $data->parentesco_id >= 921 and $data->parentesco_id <= 926 and $param['vinculo'] != 3) {
                            throw new Exception('<b>Atenção!</b> Você já possui este vínculo com <b>' . $pps->nome . '</b>!');
                        }

                        if ($data->cpf == $pps->cpf and $param['vinculo'] != 3) {
                            throw new Exception('<b>Atenção!</b> Você já vinculou <b>' . $pps->nome . '</b> à sua lista!');
                        }
                    }
                }

                $data->vinculo = $param['vinculo'];

                $num = 0;

                if (isset($data->id) and !empty($data->id) and $param['vinculo'] == 3) {
                    foreach ($dadosparentespf as $pessoa_datagrid) {
                        if ($data->id == $pessoa_datagrid->id) {
                            //$parte_string = substr($pessoa_datagrid->cpf, 0, 5);
                            //if ($parte_string == 'CPFde') {
                            if ($pessoa_datagrid->cpf != $data->cpf) {
                                $num = $pessoa_datagrid->cpf;
                            }
                        }
                    }
                }

                $dadosparentespf[$data->cpf] = $data;

                TSession::setValue('dados_parentes_pf', (array) $dadosparentespf);

                if ($num != 0) {
                    $posAction = new TDataGridAction(['DadosParentes', 'onDelete'],   ['cpf' => $num, 'register_state' => 'false']);
                    new TMessage('info', 'Pessoa vinculada com sucesso!', $posAction);
                } else {
                    // show the message
                    new TMessage('info', 'Pessoa vinculada com sucesso!', new TAction(array('DadosParentes', 'onReload')));
                }
            }

            TTransaction::close();  // close the transaction

        } catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    public static function verificaParentescoCpfGenero($param)
    {

        $parentesco_id = $param['parentesco_id'];
        $cpf = $param['cpf'];
        $genero = $param['genero'];

        if ($parentesco_id and $cpf and $genero) {
            $pessoa_painel = TSession::getValue('pessoa_painel');
            $pessoa_painel_vinculos = TSession::getValue('pessoa_painel_vinculos');
            $pessoa_sessao = TSession::getValue('dados_iniciais_pf');
            $pf = ViewPessoaFisica::where('cpf', '=', $cpf)->first();

            if (isset($pessoa_painel)) {
                if ($pessoa_painel->cpf == $cpf) {
                    throw new Exception('<b>Atenção!</b> Você não pode vincular-se à sua própria lista!');
                }
            }
            if (!empty($pessoa_sessao)) {
                if ($pessoa_sessao['cpf_cnpj'] == $cpf) {
                    throw new Exception('<b>Atenção!</b> Você não pode vincular-se à sua própria lista!');
                }
            }

            if (isset($pessoa_painel_vinculos)) {
                foreach ($pessoa_painel_vinculos as $ppv) {
                    if ($cpf == $ppv->PessoaParente->cpf_cnpj and $param['vinculo'] != 3) {
                        throw new Exception('<b>Atenção!</b> Você já vinculou <b>' . $ppv->PessoaParente->nome . '</b> à sua lista!');
                    }
                }
            }
            if ($pf) {
                if ($pf->genero != $genero) {
                    throw new Exception('<b>Atenção!</b> Gênero divergente da pessoa <b>' . $pf->nome . '</b>!');
                } else if ($parentesco_id >= 921 and $parentesco_id <= 926) {
                    if (isset($pessoa_painel)) {
                        $pp = PessoaParentesco::where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->where('pessoa_parente_id', '=', $pf->id)->where('pessoa_id', '!=', $pessoa_painel->id)->first();
                    } else {
                        $pp = PessoaParentesco::where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->where('pessoa_parente_id', '=', $pf->id)->first();
                    }
                    if ($pp) {
                        $buscagrau = ListaItens::where('id', '=', $parentesco_id)->first();
                        throw new Exception('Você não pode vincular <b>' . $pp->PessoaParente->nome . '</b> como <b>' . $buscagrau->item . '</b>!<br>Vínculo EXISTENTE: <b>' . $pp->Parentesco->item . '</b> de <b>' . $pp->Pessoa->nome . '</b>. <br> Se acreditar que este vínculo está incorreto, entre em contato com o Administrador do sistema!');
                    } else {
                        $pf->dt_nascimento =  TDate::date2br($pf->dt_nascimento);
                        if ($param['vinculo'] != 2) {
                            $pf->vinculo = 1;
                        }
                        TForm::sendData('form_PessoaParente', $pf);
                        TButton::enableField('form_PessoaParente', 'inserir');
                    }
                } else if ($parentesco_id == 903 or $parentesco_id == 904) {
                    $generopessoa = '';

                    if (isset($pessoa_painel)) {
                        $pp = PessoaParentesco::where('parentesco_id', '=', $parentesco_id)->where('pessoa_parente_id', '=', $pf->id)->where('pessoa_id', '!=', $pessoa_painel->id)->load();
                        $generopessoa = $pessoa_painel->genero == 'Masculino' ? 'M' : 'F';
                    } else {
                        $pp = PessoaParentesco::where('parentesco_id', '=', $parentesco_id)->where('pessoa_parente_id', '=', $pf->id)->load();
                        $generopessoa = $pessoa_sessao['genero'];
                    }

                    if ($pp) {
                        $buscagrau = ListaItens::where('id', '=', $parentesco_id)->first();
                        $nome1 = '';
                        $grau = '';
                        $item = '';
                        $nome2 = '';
                        $tem_pai_mae = 0;
                        foreach ($pp as $p) {
                            if ($p->Pessoa->PessoaFisica->genero == $generopessoa) {
                                $nome1 = $p->PessoaParente->nome;
                                $grau = $buscagrau->item;
                                $item = $p->Parentesco->item;
                                $nome2 = $p->Pessoa->nome;
                                $tem_pai_mae = 1;
                            }
                        }
                        if ($tem_pai_mae > 0) {
                            throw new Exception('Você não pode vincular <b>' . $nome1 . '</b> como <b>' . $grau . '</b>!<br>Vínculo EXISTENTE: <b>' . $item . '</b> de <b>' . $nome2 . '</b>. <br> Use o vínculo de <b>Enteado(a)</b>, mas se acreditar que este vínculo está incorreto, entre em contato com o Administrador do sistema!');
                        } else {
                            $pf->dt_nascimento =  TDate::date2br($pf->dt_nascimento);
                            if ($param['vinculo'] != 2) {
                                $pf->vinculo = 1;
                            }
                            TForm::sendData('form_PessoaParente', $pf);
                            TButton::enableField('form_PessoaParente', 'inserir');
                        }
                    } else {
                        $pf->dt_nascimento =  TDate::date2br($pf->dt_nascimento);
                        if ($param['vinculo'] != 2) {
                            $pf->vinculo = 1;
                        }
                        TForm::sendData('form_PessoaParente', $pf);
                        TButton::enableField('form_PessoaParente', 'inserir');
                    }
                } else {
                    $pf->dt_nascimento =  TDate::date2br($pf->dt_nascimento);
                    if ($param['vinculo'] != 2) {
                        $pf->vinculo = 1;
                    }
                    TForm::sendData('form_PessoaParente', $pf);
                    TButton::enableField('form_PessoaParente', 'inserir');
                }
            } else {
                TEntry::enableField('form_PessoaParente', 'nome');
                TEntry::enableField('form_PessoaParente', 'popular');
                TDate::enableField('form_PessoaParente', 'dt_nascimento');
            }
        }
    }

    public static function onConsultaCPF($param)
    {

        try {
            TTransaction::open('adea');
            if (!empty($param['parentesco_id'])) {
                self::onChangeAction($param);
                if (!empty($param['cpf']) and !empty($param['genero'])) {
                    self::verificaParentescoCpfGenero($param);
                }
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $pfvazia = new stdClass;
            //$pfvazia->parentesco_id = '';
            $pfvazia->cpf = '';
            $pfvazia->nome = '';
            $pfvazia->popular = '';
            $pfvazia->dt_nascimento = '';
            //TEntry::disableField('form_PessoaParente', 'cpf');
            TForm::sendData('form_PessoaParente', $pfvazia);
        }
    }

    public static function onChangeAction($param)
    {

        try {
            TTransaction::open('adea');
            if (isset($param['parentesco_id']) and !empty($param['parentesco_id'])) {
                $buscagenero = ListaItens::where('id', '=', $param['parentesco_id'])->first();
                $generoparente = new stdClass;
                $generoparente->genero = $buscagenero->obs;
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TEntry::enableField('form_PessoaParente', 'cpf');

                if ($param['parentesco_id'] >= 921 and $param['parentesco_id'] <= 926) {
                    TRadioGroup::disableField('form_PessoaParente', 'moracomigo');
                    $generoparente->moracomigo = 's';
                } else {
                    TRadioGroup::enableField('form_PessoaParente', 'moracomigo');
                }
                //TEntry::enableField('form_PessoaParente', 'nome');
                //TDate::enableField('form_PessoaParente', 'dt_nascimento');

                //TQuickForm::showField('form_show_hide', 'units');
                //TQuickForm::hideField('form_PessoaParente', 'filho_casal');

                TForm::sendData('form_PessoaParente', $generoparente);
            } else {
                TEntry::disableField('form_PessoaParente', 'cpf');
                TEntry::disableField('form_PessoaParente', 'nome');
                TEntry::disableField('form_PessoaParente', 'popular');
                TDate::disableField('form_PessoaParente', 'dt_nascimento');
                TRadioGroup::disableField('form_PessoaParente', 'moracomigo');
            }
            TButton::disableField('form_PessoaParente', 'inserir');

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear(true);
        $pfvazia = new stdClass;
        $pfvazia->vinculo = 1;
        TForm::sendData('form_PessoaParente', $pfvazia);
    }


    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
