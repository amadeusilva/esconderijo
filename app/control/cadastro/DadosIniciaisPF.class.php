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

        $filterEC = new TCriteria;
        $filterEC->add(new TFilter('lista_id', '=', '17'));
        $filterEC->add(new TFilter('id', '<', '0'));
        $estado_civil_id = new TDBCombo('estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterEC);
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
        $genero->addItems(['M' => 'Masculino', 'F' => 'Feminino']);
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

        if (TSession::getValue('dados_relacao') or TSession::getValue('pessoa_painel')) {

            $label = new TLabel('<br>Dados da Relação', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';

            if (TSession::getValue('dados_relacao')) {
                $dados_relacao = (object) TSession::getValue('dados_relacao');
            } else {
                $pessoa_painel = TSession::getValue('pessoa_painel');
                $dados_relacao = self::onDadosRelacao($pessoa_painel->id);
            }


            if (!empty($dados_relacao->doc_imagem) and !TSession::getValue('pessoa_painel')) {
                $dados_relacao->doc_imagem = substr((json_decode(urldecode($dados_relacao->doc_imagem))->fileName), 4); // aqui foi a solução
            }

            if ($dados_relacao->doc_imagem and !TSession::getValue('pessoa_painel')) {
                $c = new THyperLink('(Documento Anexado)', 'download.php?file=tmp/' . $dados_relacao->doc_imagem, 'blue', 12, 'biu');
            } else if (TSession::getValue('pessoa_painel')) {
                $c = new THyperLink('(Documento Anexado)', 'download.php?file=' . $dados_relacao->doc_imagem, 'blue', 12, 'biu');
            } else {
                $c = '';
            }

            $labeldadosrelacao = new TLabel('Tipo de Vínculo: ' . $dados_relacao->tipo_vinculo . ' - (' . $dados_relacao->dt_inicial . ') - Há ' . $dados_relacao->tempo . '. ' . $c, '#555555', 12, 'b');
            $this->form->addContent([$label]);
            $this->form->addContent([$labeldadosrelacao]);
        }

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

        // validations
        $cpf_cnpj->addValidation('CPF', new TCPFValidator);
        $cpf_cnpj->addValidation('CPF', new TRequiredValidator);
        $nome->addValidation('Nome', new TRequiredValidator);
        $popular->addValidation('Popular', new TRequiredValidator);
        $estado_civil_id->addValidation('Estado Civil', new TRequiredValidator);
        $dt_nascimento->addValidation('Nascimento', new TRequiredValidator);
        //$profissao_id->addValidation('Profissão', new TRequiredValidator);
        $fone->addValidation('Fone', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $email->addValidation('Email', new TRequiredValidator);
        $genero->addValidation('Gênero', new TRequiredValidator);

        // add a form action
        $this->form->addActionLink('Lista de Pessoas',  new TAction(array(__CLASS__, 'onDecisao')), 'fa:table blue');
        $this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addAction('Avançar', new TAction(array($this, 'onAvanca')), 'far:check-circle green');

        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'PessoaFisicaDataGrid'));
        $vbox->add($this->form);
        parent::add($vbox);
    }

    public static function onDadosRelacao($param)
    {

        try {

            TTransaction::open('adea');   // open a transaction with database 'samples'

            $pessoabanda = PessoaParentesco::where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->where('pessoa_id', '=', $param)->first();

            $object = PessoasRelacao::where('relacao_id', '=', $pessoabanda->id)->first();        // instantiates object City
            $object->estado_civil_id = $object->PessoaParentesco->Pessoa->PessoaFisica->estado_civil_id;
            $object->tipo_vinculo = self::onVinculo($object->estado_civil_id);
            $object->dt_inicial =  TDate::date2br($object->dt_inicial);
            $object->tempo = self::onCalculaTempo($object->dt_inicial);

            return $object;   // fill the form with the active record data

            TTransaction::close();           // close the transaction
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function onDecisao()
    {
        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');
        if ($dadosiniciaispf) {
            TForm::sendData('form_pf', $dadosiniciaispf);
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

        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');

        if ($dadosiniciaispf) {
            TForm::sendData('form_pf', $dadosiniciaispf);
        } else if (TSession::getValue('pessoa_painel')) {
            try {
                if (isset($param['id'])) {
                    $pessoa_painel = TSession::getValue('pessoa_painel');

                    $pessoa_painel->cpf_cnpj = $pessoa_painel->cpf;
                    $pessoa_painel->genero = $pessoa_painel->genero == 'Masculino' ? 'M' : 'F';
                    $pessoa_painel->dt_nascimento =  $pessoa_painel->dt_nascimento;
                    $pessoa_painel->idade = self::onCalculaIdade($pessoa_painel->dt_nascimento);

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

                    if ($d->parentesco_id = 921) {
                        $pessoa_vinculada[$key]['genero'] = 'Esposo';
                    } else if ($d->parentesco_id = 922) {
                        $pessoa_vinculada[$key]['genero'] = 'Esposa';
                    } else if ($d->parentesco_id = 923) {
                        $pessoa_vinculada[$key]['genero'] = 'Companheiro';
                    } else if ($d->parentesco_id = 924) {
                        $pessoa_vinculada[$key]['genero'] = 'Companheira';
                    } else if ($d->parentesco_id = 925) {
                        $pessoa_vinculada[$key]['genero'] = 'Convivente';
                    } else if ($d->parentesco_id = 926) {
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
                                if ($pessoagenero->Pessoa->PessoaFisica->genero == $dadosiniciaispf['genero']) {
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
                foreach ($pessoa_vinculada as $key => $pessoa) {
                    if ($pessoa['tem_vinculo'] == 1) {
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
        try {

            $this->form->validate();
            $data = $this->form->getData();

            TSession::setValue('dados_iniciais_pf', (array) $data);

            $retorno1 = $this->onMudancaEstadoCivilFilhoFilha($data);
            if ($retorno1 == true) {
                $retorno2 = self::verificaNomeDtnascimento((array)$data);
                if ($retorno2 == true) {
                    //avança 2
                    AdiantiCoreApplication::loadPage('DadosParentes', 'onReload');
                }
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $pfvazia = new stdClass;
            $pfvazia->cpf_cnpj = '';
            $pfvazia->genero = '';
            TForm::sendData('form_pf', $pfvazia);
        }
    }

    public function onAvanca3($data)
    {

        $dados_parentes_pf = TSession::getValue('dados_parentes_pf');

        foreach ($data['pessoa_vinculada'] as $key => $pessoa) {
            if ($pessoa['tem_vinculo'] == 1) {
                unset($dados_parentes_pf[$pessoa['cpf']]);
                TSession::setValue('dados_parentes_pf', $dados_parentes_pf);
            }
        }

        TSession::setValue('dados_iniciais_pf', (array) $data['data']);
        AdiantiCoreApplication::loadPage('DadosParentes', 'onReload');
    }
}
