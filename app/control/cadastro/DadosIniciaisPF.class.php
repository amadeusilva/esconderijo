<?php

use Adianti\Database\TTransaction;
use Adianti\Validator\TCPFValidator;
use Adianti\Validator\TEmailValidator;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;

/**
 * Multi Step 3
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class DadosIniciaisPF extends TPage
{
    use ControlePessoas;
    protected $form; // form
    protected $num; // form
    protected $num_try; // form

    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_pf');
        $this->form->setFormTitle('Dados Gerais da Pessoa');
        $this->form->setClientValidation(true);

        //dados da pessoa
        $id             = new TEntry('id');
        $cpf_cnpj       = new TEntry('cpf_cnpj');
        $cpf_cnpj->setExitAction(new TAction(array($this, 'onConsultaCPF')));
        $cpf_cnpj->setMask('999.999.999-99');
        $nome  = new TDBEntry('nome', 'adea', 'Pessoa', 'nome');
        $nome->setInnerIcon(new TImage('fa:user blue'), 'left');
        $nome->placeholder = ' Nome Completo';
        $nome->forceUpperCase();
        $popular  = new TDBEntry('popular', 'adea', 'Pessoa', 'popular');
        $popular->setInnerIcon(new TImage('fa:user blue'), 'left');
        $popular->placeholder = ' Nome pelo qual é conhecido ou gosta de ser chamado';
        $popular->forceUpperCase();

        //$filterEC = new TCriteria;
        //$filterEC->add(new TFilter('id', '<', '0'));
        //$estado_civil_id = new TDBCombo('estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterEC);
        //$estado_civil_id->setChangeAction(new TAction(array($this, 'onEstadocivilChange')));

        $estado_civil_id = new TCombo('estado_civil_id');
        $estado_civil_id->setChangeAction(new TAction(array($this, 'onEstadocivilChange')));

        $fone           = new TEntry('fone');
        $fone->setMask('(99) 99999-9999');
        $email          = new TEntry('email');
        $email->forceLowerCase();
        $dt_nascimento  = new TDate('dt_nascimento');
        //$dt_nascimento->setDatabaseMask('yyyy-mm-dd');
        $dt_nascimento->setMask('dd/mm/yyyy');
        $dt_nascimento->setExitAction(new TAction(array($this, 'onCalculaIdade')));
        $idade = new TEntry('idade');
        $genero         = new TCombo('genero');

        if (TSession::getValue('pessoa_painel')) {
            $pessoa_painel = TSession::getValue('pessoa_painel');

            if ($pessoa_painel->genero == 'Masculino' or $pessoa_painel->genero == 'M') {
                $genero->addItems(['M' => 'Masculino']);
            } else {
                $genero->addItems(['F' => 'Feminino']);
            }
        } else {
            $genero->addItems(['M' => 'Masculino', 'F' => 'Feminino']);
        }

        $genero->setChangeAction(new TAction(array($this, 'onGeneroChange')));

        // define some properties for the form fields
        $id->setEditable(FALSE);
        $idade->setEditable(FALSE);
        $id->setSize('100%');
        $cpf_cnpj->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $estado_civil_id->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $genero->setSize('100%');
        $dt_nascimento->setSize('100%');
        $idade->setSize('100%');

        // add the fields
        $row = $this->form->addFields(
            [
                new TLabel('Cod.'),
                $id
            ],
            [
                new TLabel('CPF'),
                $cpf_cnpj
            ]
        );
        $row->layout = ['col-sm-4', 'col-sm-8'];

        $row = $this->form->addFields(
            [
                new TLabel('Nome'),
                $nome
            ]
        );
        $row->layout = ['col-sm-12'];

        // add the fields
        $row = $this->form->addFields(
            [
                new TLabel('Nome Popular'),
                $popular
            ],
            [
                new TLabel('Gênero'),
                $genero
            ],
        );
        $row->layout = ['col-sm-8', 'col-sm-4'];

        $row = $this->form->addFields(
            [
                new TLabel('Nascimento'),
                $dt_nascimento
            ],
            [
                new TLabel('Idade'),
                $idade
            ],
            [
                new TLabel('Estado Civil'),
                $estado_civil_id
            ]
        );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        //if (!TSession::getValue('pessoa_painel')) {

        $label = new TLabel('<br>Contatos', '#62a8ee', 14, 'b');
        $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
        $this->form->addContent([$label]);

        $row = $this->form->addFields(
            [
                new TLabel('Fone'),
                $fone
            ],
            [
                new TLabel('Email'),
                $email
            ]
        );
        $row->layout = ['col-sm-5', 'col-sm-7'];

        $fone->addValidation('Fone', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $email->addValidation('Email', new TRequiredValidator);
        //}

        if (TSession::getValue('dados_relacao') or TSession::getValue('pessoa_painel')) {

            if (TSession::getValue('dados_relacao')) {
                $dados_relacao = (object) TSession::getValue('dados_relacao');
            } else {
                $pessoa_painel = TSession::getValue('pessoa_painel');
                $dados_relacao = self::onDadosRelacao($pessoa_painel->id);
            }

            if ($dados_relacao) {
                $label = new TLabel('<br>Dados da Relação', '#62a8ee', 14, 'b');
                $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';

                $action = new TAction(['DadosRelacao', 'onVerRelacao']);
                if (isset($dados_relacao->id) and !empty($dados_relacao->id)) {
                    $action->setParameter('id', $dados_relacao->id);
                    if (isset($dados_relacao->pessoa_parentesco_id) and !empty($dados_relacao->pessoa_parentesco_id)) {
                        $action->setParameter('pessoa_parentesco_id', $dados_relacao->pessoa_parentesco_id);
                    }
                    $action->setParameter('atualiza_cadastro', true);
                }

                if ($dados_relacao->atualizacao == 's') {
                    $b2 = new TActionLink('Editar Relação', $action, 'white', 10, '', 'far:edit white');
                    $b2->class = 'btn btn-info';
                } else {
                    $b2 = new TActionLink('Atualizar Relação', $action, 'white', 10, '', 'far:edit white');
                    $b2->class = 'btn btn-danger';
                }

                if (!empty($dados_relacao->doc_imagem) and !TSession::getValue('pessoa_painel')) {
                    $dados_relacao->doc_imagem = substr((json_decode(urldecode($dados_relacao->doc_imagem))->fileName), 4); // aqui foi a solução
                }
                //else{
                //    $dados_relacao->doc_imagem = substr('tmp/'.(json_decode(urldecode($dados_relacao->doc_imagem))->fileName), 4);
                //}

                if ($dados_relacao->doc_imagem) {
                    if ($dados_relacao->doc_imagem and !TSession::getValue('pessoa_painel')) {
                        $c = new THyperLink('(Documento Anexado)', 'download.php?file=tmp/' . $dados_relacao->doc_imagem, 'blue', 12, 'biu');
                    } else if (TSession::getValue('pessoa_painel')) {
                        if (preg_match('/\b%\b/', $dados_relacao->doc_imagem)) { // verifica se o caracter '%' contém na string
                            $dados_relacao->doc_imagem = json_decode(urldecode($dados_relacao->doc_imagem))->fileName;
                        }
                        $c = new THyperLink('(Documento Anexado)', 'download.php?file=' . $dados_relacao->doc_imagem, 'blue', 12, 'biu');
                    }
                } else {
                    $c = '';
                }

                $labeldadosrelacao = new TLabel('Tipo de Vínculo: ' . $dados_relacao->tipo_vinculo . ' - (' . $dados_relacao->dt_inicial . ') - Há ' . $dados_relacao->tempo . '. ' . $c, '#555555', 12, 'b');
                $this->form->addContent([$label]);
                $this->form->addContent([$labeldadosrelacao]);
                $this->form->addContent([$b2]);
            }
        }

        // validations
        $cpf_cnpj->addValidation('CPF', new TCPFValidator);
        $cpf_cnpj->addValidation('CPF', new TRequiredValidator);
        $nome->addValidation('Nome', new TRequiredValidator);
        $popular->addValidation('Popular', new TRequiredValidator);
        $estado_civil_id->addValidation('Estado Civil', new TRequiredValidator);
        $dt_nascimento->addValidation('Nascimento', new TRequiredValidator);
        //$profissao_id->addValidation('Profissão', new TRequiredValidator);

        $genero->addValidation('Gênero', new TRequiredValidator);

        // add a form action
        $this->form->addActionLink('Lista de Pessoas',  new TAction(array(__CLASS__, 'onDecisao')), 'fa:table blue');
        //$this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addAction('Avançar', new TAction(array($this, 'onAvanca')), 'far:check-circle green');

        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'PessoaFisicaDataGrid'));
        $vbox->add($this->form);
        parent::add($vbox);
    }

    public function onDecisao()
    {
        $pessoa_painel = TSession::getValue('pessoa_painel');
        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');
        if ($dadosiniciaispf) {
            TForm::sendData('form_pf', $dadosiniciaispf);
        } else if ($pessoa_painel) {
            TForm::sendData('form_pf', $pessoa_painel);
        }

        // create two actions
        //$action1 = new TAction(array($this, 'onReinicia'));
        $posAction = new TAction(array('PessoaFisicaDataGrid', 'onReload'));
        // define os parâmetros de cada ação

        // shows the question dialog
        new TQuestion('Caso volte para a lista de pessoas <b>você perderá os dados já preenchidos</b>, Deseja prosseguir?', $posAction);
    }

    public function onEdit($param)
    {

        TButton::disableField('form_pf', 'avançar');

        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');

        if ($dadosiniciaispf) {
            TForm::sendData('form_pf', $dadosiniciaispf);
        } else if (TSession::getValue('pessoa_painel')) {
            $pessoa_painel = TSession::getValue('pessoa_painel');
            try {
                if (isset($pessoa_painel->id)) {

                    $pessoa_painel->cpf_cnpj = $pessoa_painel->cpf;
                    //if ($pessoa_painel->cpf_cnpj) {
                    //    TEntry::disableField('form_pf', 'cpf_cnpj');
                    //}
                    if ($pessoa_painel->genero == 'Masculino') {
                        $pessoa_painel->genero = 'M';
                    } else if ($pessoa_painel->genero == 'Feminino') {
                        $pessoa_painel->genero = 'F';
                    }
                    //$pessoa_painel->genero = $pessoa_painel->genero == 'Masculino' ? 'M' : 'F';
                    $pessoa_painel->dt_nascimento =  $pessoa_painel->dt_nascimento;
                    $pessoa_painel->idade = self::onCalculaIdade($pessoa_painel->dt_nascimento);

                    TTransaction::open('adea');

                    $fone = PessoaContato::where('pessoa_id', '=', $pessoa_painel->id)->where('tipo_contato_id', '=', 101)->first();
                    if ($fone) {
                        $pessoa_painel->fone = $fone->contato;
                    }
                    $email = PessoaContato::where('pessoa_id', '=', $pessoa_painel->id)->where('tipo_contato_id', '=', 102)->first();
                    if ($email) {
                        $pessoa_painel->email = $email->contato;
                    }

                    TTransaction::close();


                    $this->form->setData($pessoa_painel);   // fill the form with the active record data.

                    $param['dt_nascimento'] = $pessoa_painel->dt_nascimento;
                    $param['genero'] = $pessoa_painel->genero;
                    $param['estado_civil_id'] = $pessoa_painel->estado_civil_id;

                    TForm::sendData('form_pf', $param);
                } else {
                    $this->form->clear(true);
                }
            } catch (Exception $e) // in case of exception
            {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        }
    }

    public function onMudancaEstadoCivilFilhoFilha($data)
    {
        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');
        $dados_parentes_pf = TSession::getValue('dados_parentes_pf');

        //estado civil: 803-804: convivent / 805-806: ue / 807-808: casad
        //parentesco: 921-922: espos / 923-924: companheir / 925-926: convivente

        if (TSession::getValue('dados_parentes_pf')) {

            $pessoa_vinculada = [];
            $tem_pessoa_vinculada = 0;

            foreach ($dados_parentes_pf as $key => $d) {
                if ($d->parentesco_id >= 921 and $d->parentesco_id <= 926) {
                    $pessoa_vinculada[$key]['nome'] = $d->nome;
                    $pessoa_vinculada[$key]['cpf'] = $d->cpf;
                    $pessoa_vinculada[$key]['parentesco_id'] = $d->parentesco_id;

                    if ($d->parentesco_id == 921) {
                        $pessoa_vinculada[$key]['genero'] = 'Esposo';
                    } else if ($d->parentesco_id == 922) {
                        $pessoa_vinculada[$key]['genero'] = 'Esposa';
                    } else if ($d->parentesco_id == 923) {
                        $pessoa_vinculada[$key]['genero'] = 'Companheiro';
                    } else if ($d->parentesco_id == 924) {
                        $pessoa_vinculada[$key]['genero'] = 'Companheira';
                    } else if ($d->parentesco_id == 925) {
                        $pessoa_vinculada[$key]['genero'] = 'Convivente';
                    } else if ($d->parentesco_id == 926) {
                        $pessoa_vinculada[$key]['genero'] = 'Convivente';
                    }

                    if ($dadosiniciaispf['estado_civil_id'] == 807 and $d->parentesco_id == 922) {
                        $pessoa_vinculada[$key]['tem_vinculo'] = 0;
                    } else if ($dadosiniciaispf['estado_civil_id'] == 808 and $d->parentesco_id == 921) {
                        $pessoa_vinculada[$key]['tem_vinculo'] = 0;
                    } else if ($dadosiniciaispf['estado_civil_id'] == 805 and $d->parentesco_id == 924) {
                        $pessoa_vinculada[$key]['tem_vinculo'] = 0;
                    } else if ($dadosiniciaispf['estado_civil_id'] == 806 and $d->parentesco_id == 923) {
                        $pessoa_vinculada[$key]['tem_vinculo'] = 0;
                    } else if ($dadosiniciaispf['estado_civil_id'] == 803 and $d->parentesco_id == 926) {
                        $pessoa_vinculada[$key]['tem_vinculo'] = 0;
                    } else if ($dadosiniciaispf['estado_civil_id'] == 804 and $d->parentesco_id == 925) {
                        $pessoa_vinculada[$key]['tem_vinculo'] = 0;
                    } else {
                        $pessoa_vinculada[$key]['tem_vinculo'] = 1;
                        $tem_pessoa_vinculada = 1;
                    }
                } else if ($d->parentesco_id == 903 or $d->parentesco_id == 904) {
                    $pessoa_vinculada[$key]['nome'] = $d->nome;
                    $pessoa_vinculada[$key]['cpf'] = $d->cpf;
                    $pessoa_vinculada[$key]['parentesco_id'] = $d->parentesco_id;
                    if ($d->parentesco_id == 903) {
                        $pessoa_vinculada[$key]['genero'] = 'Filho';
                    } else {
                        $pessoa_vinculada[$key]['genero'] = 'Filha';
                    }

                    TTransaction::open('adea');

                    $pf_filho_filha = ViewPessoaFisica::where('cpf', '=', $d->cpf)->first();

                    if (isset($pf_filho_filha->id) and !empty($pf_filho_filha->id)) {

                        $tem_vinculo = PessoaParentesco::where('parentesco_id', '=', $d->parentesco_id)->where('pessoa_parente_id', '=', $pf_filho_filha->id)->load();

                        $pessoa_vinculada[$key]['tem_vinculo'] = 0;

                        if (isset($tem_vinculo) and !empty($tem_vinculo)) {
                            foreach ($tem_vinculo as $pessoagenero) {

                                if ($pessoagenero->Pessoa->genero == $dadosiniciaispf['genero'] and $pessoagenero->pessoa_id != $dadosiniciaispf['id']) {
                                    $pessoa_vinculada[$key]['tem_vinculo'] = 1;
                                    $tem_pessoa_vinculada = 1;
                                }
                            }
                        }
                    }
                    TTransaction::close();
                }
            }

            if ($tem_pessoa_vinculada > 0) {

                $posAction = new TAction([$this, 'onAvanca3']);
                $posAction->setParameter('data', $data);
                $posAction->setParameter('pessoa_vinculada', $pessoa_vinculada);

                $param['genero'] = '';
                $param['estado_civil_id'] = '';
                TForm::sendData('form_pf', $param);

                $table = new TTable;
                $table->border = 1;
                $table->style = 'border-collapse:collapse; text-align:center;';
                $table->width = '100%';
                $table->addRowSet('<b>CPF</b>', '<b>Nome</b>', '<b>Vínculo</b>');

                foreach ($pessoa_vinculada as $pessoa) {

                    if (isset($pessoa['tem_vinculo']) and $pessoa['tem_vinculo'] == 1) {
                        $table->addRowSet($pessoa['cpf'], $pessoa['nome'], $pessoa['genero']);
                    }
                }

                new TQuestion('<b>Atenção!</b> As mudanças realizadas afetam o(s) seguinte(s) vínculo(s): <br><br>' .
                    $table
                    . '</b><br>Os vínculos de parentescos são baseados nos dados de <b>gênero</b> e <b>estado civil</b>, 
                 se prosseguir perderá o vínculo e será necessário inserir novamente, Deseja prosseguir?', $posAction);
            } else {
                return true;
            }
        } else {
            return true;
        }
    }


    /**
     * confirmation screen
     */
    public function onAvanca($param)
    {

        //para não correr o risco de passar pro prórimo formulário sem o estado civil
        if (isset($param['estado_civil_id']) and !empty($param['estado_civil_id'])) {

            try {

                $this->num_try = 0;

                $this->form->validate();
                $data = $this->form->getData();

                TTransaction::open('adea');

                $cpfsimnao = self::CPFCadastrado($data->cpf_cnpj);

                if (isset($cpfsimnao) and !empty($cpfsimnao) and $cpfsimnao->id != $data->id) {
                    throw new Exception('CPF já cadastrado para: <b>' . $cpfsimnao->nome . '</b>');
                } else

                    //verificar mudança de nome e dn do banco
                    if (isset($data->id) and !empty($data->id)) {
                        $pessoa = ViewPessoaFisica::find($data->id);
                        $pessoa->dt_nascimento =  TDate::date2br($pessoa->dt_nascimento);
                        if ($pessoa->ck_pessoa != 0) {
                            if ($pessoa->nome != $data->nome or $pessoa->popular != $data->popular or $pessoa->dt_nascimento != $data->dt_nascimento) {
                                $this->num_try = 1;
                                throw new Exception('<b>Atenção!</b> Encontramos a pessoa: <b>' . $pessoa->nome . ' (' . $pessoa->dt_nascimento . ')</b> REGISTRADO com o código <b>' . $pessoa->id .  '</b>. Se acreditar que estes dados estão incorretos, entre em contato com o Administrador do sistema!');
                            } else if (isset($pessoa->cpf) and !empty($pessoa->cpf) and $pessoa->cpf != $data->cpf_cnpj) {
                                $this->num_try = 1;
                                throw new Exception('<b>Atenção!</b> Encontramos a pessoa: <b>' . $pessoa->nome . ' (' . $pessoa->dt_nascimento . ')</b> REGISTRADO com o código <b>' . $pessoa->id .  '</b>. Se acreditar que estes dados estão incorretos, entre em contato com o Administrador do sistema!');
                            } else {
                                TSession::setValue('dados_iniciais_pf', (array) $data);
                            }
                        } else {
                            TSession::setValue('dados_iniciais_pf', (array) $data);
                        }
                    } else {
                        TSession::setValue('dados_iniciais_pf', (array) $data);
                    }

                $dados_iniciais_pf = TSession::getValue('dados_iniciais_pf');

                // salva parente na sessão se houver
                if (TSession::getValue('pessoa_painel_vinculos')) {

                    $pessoa_painel = TSession::getValue('pessoa_painel');

                    $pessoa_painel_vinculos = TSession::getValue('pessoa_painel_vinculos');

                    foreach ($pessoa_painel_vinculos as $objeto) {
                        $pessoa = ViewPessoaFisica::find($objeto->pessoa_parente_id);

                        if (empty($pessoa->cpf)) {
                            $pessoa->cpf = 'CPFde' . $pessoa->popular;
                        }

                        $pessoa->dt_nascimento =  TDate::date2br($pessoa->dt_nascimento);

                        if ($objeto->parentesco_id >= 921 and $objeto->parentesco_id <= 926) {
                            if ($dados_iniciais_pf['estado_civil_id'] == 803) { //803 - Convivente - M
                                $pessoa->parentesco_id = 926; //926 - CONVIVENTE - F
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 804) { //804 - Convivente - F
                                $pessoa->parentesco_id = 925; //925 - CONVIVENTE - M
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 805) { //805 - União Estável - M
                                $pessoa->parentesco_id = 924; //924 - COMPANHEIRA - F
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 806) { //806 - União Estável - F
                                $pessoa->parentesco_id = 923; //923 - COMPANHEIRO - M
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 807) { //807 - Casado - M
                                $pessoa->parentesco_id = 922; //922 - ESPOSA - F
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 808) { //808 - Casada - F
                                $pessoa->parentesco_id = 921; //921 - ESPOSO - M
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 809) { //809 - Separado - M
                                if ($objeto->parentesco_id == 924) { //924 - COMPANHEIRA - F
                                    $pessoa->parentesco_id = 930; //930 - EX-COMPANHEIRA - F
                                } else if ($objeto->parentesco_id == 926) { //926 - CONVIVENTE - F
                                    $pessoa->parentesco_id = 932; //932 - EX-CONVIVENTE - F
                                }
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 810) { //810 - Separada - F
                                if ($objeto->parentesco_id == 923) { //923 - COMPANHEIRO - M
                                    $pessoa->parentesco_id = 929; //929 - EX-COMPANHEIRO - M
                                } else if ($objeto->parentesco_id == 925) { //925 - CONVIVENTE - M
                                    $pessoa->parentesco_id = 931; //931 - EX-CONVIVENTE - M
                                }
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 811) { //811 - Divorciado - M
                                $pessoa->parentesco_id = 928; //928 - EX-ESPOSA - F
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 812) { //812 - Divorciada - F
                                $pessoa->parentesco_id = 927; //927 - EX-ESPOSO - M
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 813) { //813 - Viúvo - M
                                $pessoa->parentesco_id = 928; //928 - EX-ESPOSA - F
                            } else if ($dados_iniciais_pf['estado_civil_id'] == 814) { //814 - Viúva - F
                                $pessoa->parentesco_id = 927; //927 - EX-ESPOSO - M
                            }
                        } else {
                            $pessoa->parentesco_id = $objeto->parentesco_id;
                        }

                        $pessoa->vinculo = 3; //vem do banco

                        $pessoa->moracomigo = ($pessoa->endereco_id == $pessoa_painel->endereco_id) ? 's' : 'n';
                        $pessoa->atualizacao = 'n';

                        $dadosparentespf = TSession::getValue('dados_parentes_pf');

                        $add_parente = 0;

                        foreach ((object) $dadosparentespf as $addparente) {

                            if ($pessoa->id == $addparente->id) {
                                //$parte_string = substr($pessoa_datagrid->cpf, 0, 5);
                                //if ($parte_string == 'CPFde') {
                                if ($addparente->cpf != $pessoa->cpf and $pessoa->parentesco_id >= 921 and $pessoa->parentesco_id <= 932  and $pessoa->parentesco_id != $addparente->parentesco_id) {
                                    $this->num = $addparente->cpf;
                                }
                            }



                            if ($pessoa->id == $addparente->id and $pessoa->parentesco_id == $addparente->parentesco_id) {
                                $add_parente = 1;
                            }
                        }

                        if ($add_parente == 0) {
                            $dadosparentespf[$pessoa->cpf] = $pessoa;
                        }

                        TSession::setValue('dados_parentes_pf', (array) $dadosparentespf);
                    }
                }

                TTransaction::close();  // close the transaction

                $retorno1 = $this->onMudancaEstadoCivilFilhoFilha($data);

                if ($retorno1 == true) {
                    $retorno2 = self::verificaNomeDtnascimento((array)$data);
                    if ($retorno2 == true) {
                        //avança 2
                        $atualiza_relacao = 0;

                        if (TSession::getValue('dados_relacao') or TSession::getValue('pessoa_painel')) {

                            if (TSession::getValue('dados_relacao')) {
                                $dados_relacao = (object) TSession::getValue('dados_relacao');
                            } else {
                                $pessoa_painel = TSession::getValue('pessoa_painel');
                                $dados_relacao = self::onDadosRelacao($pessoa_painel->id);
                            }

                            if ($dados_relacao) {
                                if ($dados_relacao->atualizacao == 's') {
                                    $atualiza_relacao = 0;
                                } else {
                                    $atualiza_relacao = 1;
                                }
                            }
                        }

                        if ($atualiza_relacao != 0) {
                            $this->form->add(new TAlert('danger', '<b>Atenção!</b>. Você precisa verificar se seus <b>Dados de Relação</b> estão atualizados e depois <b>Salvar</b>!'));
                            if (isset($dados_relacao->id) and !empty($dados_relacao->id)) {
                                AdiantiCoreApplication::loadPage('DadosRelacao', 'onVerRelacao', ['pessoa_parentesco_id' => $dados_relacao->pessoa_parentesco_id, 'atualiza_cadastro' => true]);
                            } else {
                                AdiantiCoreApplication::loadPage('DadosRelacao', 'onVerRelacao');
                            }
                            $pf = new stdClass;
                            $pf->genero = $data->genero;
                            $pf->estado_civil_id = $data->estado_civil_id;
                            TForm::sendData('form_pf', $pf);
                        } else {
                            if ($this->num != 0) {
                                AdiantiCoreApplication::loadPage('DadosParentes', 'onDelete', ['cpf' => $this->num]);
                            } else {
                                AdiantiCoreApplication::loadPage('DadosParentes', 'onReload');
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage());
                $pfvazia = new stdClass;
                if ($this->num_try == 0) {
                    $pfvazia->cpf_cnpj = '';
                    $pfvazia->genero = '';
                } else {
                    $pfvazia->genero = '';
                }
                TForm::sendData('form_pf', $pfvazia);
            }
        } else {
            //$posAction = new TDataGridAction(['DadosIniciaisPF', 'onEdit'],   ['param' => '', 'register_state' => 'false']);
            //new TMessage('danger', '<b>Atenção!</b> Preencha o campo <b>Estado Civil</b>!', $posAction);
            $this->form->add(new TAlert('danger', '<b>Atenção!</b> Preencha o campo <b>Estado Civil</b>!'));
            $this->onEdit($param);
        }
    }

    public function onAvanca3($data)
    {

        $dados_parentes_pf = TSession::getValue('dados_parentes_pf');

        foreach ($data['pessoa_vinculada'] as $key => $pessoa) {
            if (isset($pessoa['tem_vinculo']) and $pessoa['tem_vinculo'] == 1) {
                unset($dados_parentes_pf[$pessoa['cpf']]);
                TSession::setValue('dados_parentes_pf', $dados_parentes_pf);
            }
        }

        TSession::setValue('dados_iniciais_pf', (array) $data['data']);
        AdiantiCoreApplication::loadPage('DadosParentes', 'onReload');
    }
}
