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
        $filter->add(new TFilter('ck', '=', 1));

        if (isset($pessoa_painel->id) and !empty($pessoa_painel->id)) {
            try {
                TTransaction::open('adea');
                $pessoa_parente_painel = PessoaParentesco::where('pessoa_id', '=', $pessoa_painel->id)->load();

                if ($pessoa_painel->genero == 'Masculino') {
                    $filter->add(new TFilter('id', '!=', 921));
                    $filter->add(new TFilter('id', '!=', 923));
                    $filter->add(new TFilter('id', '!=', 925));
                } else {
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
        if (TSession::getValue('dados_iniciais_pf')) {
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
        if (TSession::getValue('dados_parentes_pf')) {
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
        $parentesco_id = new TDBCombo('parentesco_id', 'adea', 'ListaItens', 'id', '{item} {abrev}', 'item', $filter);
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

        $options = ['s' => 'Sim', 'n' => 'Não'];
        $endereco_id = new TRadioGroup('endereco_id');
        $endereco_id->setUseButton();
        $endereco_id->addItems($options);
        $endereco_id->setLayout('horizontal');
        $endereco_id->setValue('s');

        $cpf->setEditable(FALSE);
        $nome->setEditable(FALSE);
        $popular->setEditable(FALSE);
        $genero->setEditable(FALSE);
        $dt_nascimento->setEditable(FALSE);
        $endereco_id->setEditable(FALSE);
        $parentesco_id->setSize('100%');
        $cpf->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $genero->setSize('100%');
        $dt_nascimento->setSize('100%');
        $endereco_id->setSize('100%');

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
        $row = $this->form->addFields([new TLabel('Mora comigo?', 'red')], [$endereco_id]);
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
        $endereco_id->addValidation('Mora comigo?', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Inserir', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
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

            $this->form->validate();
            $data = $this->form->getData();

            $this->form->setData($data); // put the data back to the form

            $pessoa_painel = TSession::getValue('pessoa_painel');

            if ($pessoa_painel) {
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
                    if ($data->endereco_id == 's') {
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
                    $pessoa_fisica_nova->store();

                    $data->pessoa_parente_id = $pessoa_nova->id;
                }

                $novoparentesco = new PessoaParentesco();
                $novoparentesco->fromArray((array) $data); // load the object with data
                $novoparentesco->store();

                $this->onSalvaParenteInverso($pessoa_painel->genero, $pessoa_painel->id, $data->parentesco_id, $data->pessoa_parente_id);

                self::onClose();

                $posAction = new TDataGridAction(['PessoaPanel', 'onView'],   ['key' => $pessoa_painel->id, 'register_state' => 'false']);

                new TMessage('info', 'Pessoa vinculada com sucesso!', $posAction);
            } else {

                $dadosparentespf = TSession::getValue('dados_parentes_pf');

                if (isset($dadosparentespf)) {
                    foreach ($dadosparentespf as $pps) {
                        if ($data->cpf == $pps->cpf) {
                            throw new Exception('<b>Atenção!</b> Você já vinculou <b>' . $pps->nome . '</b> à sua lista!');
                        }
                    }
                }

                $dadosparentespf[$data->cpf] = $data;
                TSession::setValue('dados_parentes_pf', (array) $dadosparentespf);
                // show the message
                new TMessage('info', 'Pessoa vinculada com sucesso!', new TAction(array('DadosParentes', 'onReload')));
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

    public static function verificaParentescoCpfGenero($parentesco_id, $cpf, $genero)
    {
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
                    if ($cpf == $ppv->PessoaParente->cpf_cnpj) {
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
                        TForm::sendData('form_PessoaParente', $pf);
                        TButton::enableField('form_PessoaParente', 'inserir');
                    }
                } else {
                    $pf->dt_nascimento =  TDate::date2br($pf->dt_nascimento);
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

    public static function verificaNomeDtnascimento($param)
    {
        try {
            TTransaction::open('adea');
            if ($param['cpf'] and $param['nome'] and $param['dt_nascimento']) {

                $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_nascimento']);
                $param['dt_nascimento'] = $novadata->format('Y/m/d');

                $pf = ViewPessoaFisica::where('nome', '=', $param['nome'])->where('dt_nascimento', '=', $param['dt_nascimento'])->first();

                if ($pf) {
                    if ($pf->cpf != $param['cpf']) {
                        throw new Exception('<b>Atenção:</b> Você não pode vincular<b> ' . $pf->nome . ' (' . $novadata->format('d/m/Y') . ')</b>.<br>Pessoa EXISTENTE em outro CPF. <br> Se acreditar que estes dados estão incorretos, entre em contato com o Administrador do sistema!');
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
            $pfvazia->dt_nascimento = '';
            TForm::sendData('form_PessoaParente', $pfvazia);
        }
    }

    public static function onConsultaCPF($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['parentesco_id'])) {
                self::onChangeAction($param);
                if (!empty($param['cpf']) and !empty($param['genero'])) {
                    self::verificaParentescoCpfGenero($param['parentesco_id'], $param['cpf'], $param['genero']);
                }
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $pfvazia = new stdClass;
            //$pfvazia->parentesco_id = '';
            $pfvazia->cpf = '';
            $pfvazia->nome = '';
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
                    TRadioGroup::disableField('form_PessoaParente', 'endereco_id');
                    $generoparente->endereco_id = 's';
                } else {
                    TRadioGroup::enableField('form_PessoaParente', 'endereco_id');
                }
                //TEntry::enableField('form_PessoaParente', 'nome');
                //TDate::enableField('form_PessoaParente', 'dt_nascimento');

                //TQuickForm::showField('form_show_hide', 'units');
                //TQuickForm::hideField('form_PessoaParente', 'filho_casal');

                TForm::sendData('form_PessoaParente', $generoparente);
            } else {
                TEntry::disableField('form_PessoaParente', 'cpf');
                TEntry::disableField('form_PessoaParente', 'nome');
                TDate::disableField('form_PessoaParente', 'dt_nascimento');
                TRadioGroup::disableField('form_PessoaParente', 'endereco_id');
            }
            TButton::disableField('form_PessoaParente', 'inserir');

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }


    /**
     * Close window after insert
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
