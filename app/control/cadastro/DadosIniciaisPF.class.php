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

        if (TSession::getValue('dados_relacao')) {

            $label = new TLabel('<br>Dados da Relação', '#62a8ee', 14, 'b');
            $label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
            $dados_relacao = TSession::getValue('dados_relacao');

            if (!empty($dados_relacao['doc_imagem'])) {
                $dados_relacao['doc_imagem'] = substr((json_decode(urldecode($dados_relacao['doc_imagem']))->fileName), 4); // aqui foi a solução
            }


            $c = new THyperLink('Documento Anexado', 'download.php?file=tmp/' . $dados_relacao['doc_imagem'], 'blue', 12, 'biu');

            $labeldadosrelacao = new TLabel('Tipo de Vínculo: ' . $dados_relacao['tipo_vinculo'] . ' - (' . $dados_relacao['dt_inicial'] . ') - Há ' . $dados_relacao['tempo'] . '. (' . $c . ').', '#555555', 12, 'b');
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

    public static function onEstadocivilChange($param)
    {
        try {
            TTransaction::open('adea');

            $dados_relacao = TSession::getValue('dados_relacao');

            if (!empty($param['estado_civil_id'])) {
                if ($param['estado_civil_id'] >= 803 and $param['estado_civil_id'] <= 808) {
                    if ($dados_relacao) {
                        if ($param['estado_civil_id'] != $dados_relacao['estado_civil_id']) {
                            AdiantiCoreApplication::loadPage('DadosRelacao', 'onEdit', ['param' => $param]);
                            $param['estado_civil_id'] = '';
                            TForm::sendData('form_pf', $param);
                        }
                    } else {
                        AdiantiCoreApplication::loadPage('DadosRelacao', 'onEdit', ['param' => $param]);
                        $param['estado_civil_id'] = '';
                        TForm::sendData('form_pf', $param);
                    }
                } else if ($dados_relacao) {
                    $posAction = new TAction(array(__CLASS__, 'onDeletarelacao'));
                    $posAction->setParameter('deleterelacao', 1);
                    $posAction->setParameter('novoparam', $param);
                    $posAction->setParameter('register_state', 'false');

                    $param['estado_civil_id'] = '';
                    TForm::sendData('form_pf', $param);

                    // shows the question dialog
                    new TQuestion('<b>Atenção!</b> Você possui dados de relação, caso confirme a ação de mudança, <b>você perderá os dados já preenchidos</b>, Deseja prosseguir?', $posAction);
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onGeneroChange($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['genero'])) {

                $criteria = TCriteria::create(['lista_id' => 17, 'abrev' => 'GP', 'obs' => $param['genero']]);

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_pf', 'estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id', $criteria, TRUE);
            } else {
                TCombo::clearField('form_pf', 'estado_civil_id');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
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

    public function onDeletarelacao($param)
    {
        if (isset($param['deleterelacao']) and $param['deleterelacao'] == 1) {

            TSession::delValue('dados_relacao');
            TSession::setValue('dados_iniciais_pf', (array) $param['novoparam']);

            AdiantiCoreApplication::loadPage(__CLASS__, 'onEdit');
        }
    }

    public function onEdit()
    {
        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');

        TSession::delValue('pessoa_painel');
        TSession::delValue('pessoa_painel_vinculos');

        if ($dadosiniciaispf) {
            TForm::sendData('form_pf', $dadosiniciaispf);
        }
    }

    public static function onCalculaIdade($param)
    {
        if (isset($param['dt_nascimento']) and !empty($param['dt_nascimento'])) {
            //converte a data static BR para Americana
            $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_nascimento']);
            $param['dt_nascimento'] = $novadata->format('Y/m/d');
            $interval = $novadata->diff(new DateTime(date('Y-m-d')));
            $idade_cauculada = new stdClass;
            $idade_cauculada->idade = $interval->format('%Y anos');

            TForm::sendData('form_pf', $idade_cauculada);
        }
    }

    public static function onConsultaCPF($param)
    {
        try {
            TTransaction::open('adea');
            if (isset($param['cpf_cnpj']) and !empty($param['cpf_cnpj'])) {
                $pessoaexistente = Pessoa::where('cpf_cnpj', '=', $param['cpf_cnpj'])->first();
                if ($pessoaexistente) {

                    $posAction = new TAction(array('DadosIniciaisPF', 'onClear'));

                    // show the message dialog
                    new TMessage('error', 'CPF já cadastrado para: <b>' . $pessoaexistente->nome . '</b>', $posAction);
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function verificaNomeDtnascimento($param)
    {
        try {
            TTransaction::open('adea');
            if ($param['cpf_cnpj'] and $param['nome'] and $param['dt_nascimento']) {

                $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_nascimento']);
                $param['dt_nascimento'] = $novadata->format('Y/m/d');

                $pf = ViewPessoaFisica::where('nome', '=', $param['nome'])->where('dt_nascimento', '=', $param['dt_nascimento'])->first();

                if ($pf) {
                    if ($pf->cpf != $param['cpf_cnpj']) {
                        throw new Exception('<b>Atenção:</b> A pessoa: <b>' . $pf->nome . ' (' . $novadata->format('d/m/Y') . ')</b>.<br>Registro EXISTENTE em outro CPF. <br> Se acreditar que estes dados estão incorretos, entre em contato com o Administrador do sistema!');
                    }
                } else {
                    return 1;
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $pfvazia = new stdClass;
            $pfvazia->nome = '';
            $pfvazia->dt_nascimento = '';
            $pfvazia->genero = '';
            TForm::sendData('form_pf', $pfvazia);
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
            $data->nome = strtoupper($data->nome);
            $data->popular = ucwords($data->popular);
            $data->email = strtolower($data->email);

            $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');
            $dados_parentes_pf = TSession::getValue('dados_parentes_pf');

            //estado civil: 803-804: convivent / 805-806: ue / 807-808: casad
            //parentesco: 921-922: espos / 923-924: companheir / 925-926: convivente

            if (TSession::getValue('dados_parentes_pf')) {

                $tem_esposa_esposo = 0;
                $tem_companheira_companheiro = 0;
                $tem_convivente = 0;
                $nome = '';
                $genero = '';

                foreach ($dados_parentes_pf as $d) {
                    if ($d->parentesco_id == 921 or $d->parentesco_id == 922) {
                        $nome = $d->nome;
                        $tem_esposa_esposo = $d->parentesco_id;
                        if ($d->parentesco_id == 921) {
                            $genero = 'Esposo';
                        } else {
                            $genero = 'Esposa';
                        }
                    } else if ($d->parentesco_id == 923 or $d->parentesco_id == 924) {
                        $nome = $d->nome;
                        $tem_companheira_companheiro = $d->parentesco_id;
                        if ($d->parentesco_id == 923) {
                            $genero = 'Companheiro';
                        } else {
                            $genero = 'Companheira';
                        }
                    } else if ($d->parentesco_id == 925 or $d->parentesco_id == 926) {
                        $nome = $d->nome;
                        $tem_convivente = $d->parentesco_id;
                        if ($d->parentesco_id == 925) {
                            $genero = 'Convivente';
                        } else {
                            $genero = 'Convivente';
                        }
                    }
                }

                if ($dadosiniciaispf['estado_civil_id'] == 807 and $tem_esposa_esposo == 922) {
                    $this->onAvanca2($data);
                } else if ($dadosiniciaispf['estado_civil_id'] == 808 and $tem_esposa_esposo == 921) {
                    $this->onAvanca2($data);
                } else if ($dadosiniciaispf['estado_civil_id'] == 805 and $tem_companheira_companheiro == 924) {
                    $this->onAvanca2($data);
                } else if ($dadosiniciaispf['estado_civil_id'] == 806 and $tem_companheira_companheiro == 923) {
                    $this->onAvanca2($data);
                } else if ($dadosiniciaispf['estado_civil_id'] == 803 and $tem_convivente == 926) {
                    $this->onAvanca2($data);
                } else if ($dadosiniciaispf['estado_civil_id'] == 804 and $tem_convivente == 925) {
                    $this->onAvanca2($data);
                } else {
                    $posAction = new TAction([$this, 'onAvanca3']);
                    $posAction->setParameter('data', $data);

                    $param['estado_civil_id'] = '';
                    TForm::sendData('form_pf', $param);

                    new TQuestion('Você vinculou <b>' . $nome . '</b> como <b> ' . $genero . '</b>, se prosseguir perderá o vínculo, Deseja prosseguir?', $posAction);
                }
            } else {
                $verificarpessoa = self::verificaNomeDtnascimento((array)$data);
                if ($verificarpessoa == 1) {
                    $this->onAvanca2($data);
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

    public function onAvanca2($data)
    {
        TSession::setValue('dados_iniciais_pf', (array) $data);
        AdiantiCoreApplication::loadPage('DadosParentes', 'onReload');
    }

    //estado civil: 803-804: convivent / 805-806: ue / 807-808: casad
    //parentesco: 921-922: espos / 923-924: companheir / 925-926: convivente

    public function onAvanca3($data)
    {
        $dados_parentes_pf = TSession::getValue('dados_parentes_pf');

        $key = 0;

        foreach ($dados_parentes_pf as $d) {
            if ($d->parentesco_id >= 921 and $d->parentesco_id <= 926) {
                $key = $d->cpf;
            }
        }

        if ($key != 0) {
            unset($dados_parentes_pf[$key]);
            TSession::setValue('dados_parentes_pf', $dados_parentes_pf);
        }

        TSession::setValue('dados_iniciais_pf', (array) $data['data']);
        AdiantiCoreApplication::loadPage('DadosParentes', 'onReload');
    }
}
