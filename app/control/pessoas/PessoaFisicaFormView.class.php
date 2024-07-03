<?php

/**
 * CustomerFormView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PessoaFisicaFormView extends TPage
{

    use ControleEndereco;

    private $form; // form
    private $restrincoes;
    private $embedded;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param, $embedded = false)
    {
        parent::__construct();

        $this->embedded = $embedded;

        if (!$this->embedded) {
            parent::setTargetContainer('adianti_right_panel');
        }

        // creates the form
        $this->form = new BootstrapFormBuilder('form_pessoa');
        if (!$this->embedded) {
            $this->form->setFormTitle('Pessoa Fisica');
        }
        $this->form->setClientValidation(true);

        //dados da pessoa fisica
        $id             = new TEntry('id');
        $cpf_cnpj       = new TEntry('cpf_cnpj');
        $cpf_cnpj->setMask('999.999.999-99');
        $nome           = new TEntry('nome');
        $popular        = new TEntry('popular');
        $fone           = new TEntry('fone');
        $fone->setMask('(99) 99999-9999');
        $email          = new TEntry('email');
        $dt_nascimento  = new TDate('dt_nascimento');
        $dt_nascimento->setDatabaseMask('yyyy-mm-dd');
        $dt_nascimento->setMask('dd/mm/yyyy');
        $genero         = new TRadioGroup('genero');
        $genero->addItems(['M' => 'Masculino', 'F' => 'Feminino']);
        $genero->setLayout('horizontal');
        $genero->setUseButton();

        $profissao_id   = new TDBUniqueSearch('profissao_id', 'adea', 'Ocupacao', 'id', 'titulo');
        $profissao_id->setMask('{titulo} ({codigo})');
        $profissao_id->setMinLength(3);

        //use adianti\widget\wrapper\TDBUniqueSearch;
        $filterStatusPessoa = new TCriteria;
        $filterStatusPessoa->add(new TFilter('lista_id', '=', '5'));
        $status_pessoa   = new TDBCombo('status_pessoa', 'adea', 'ListaItens', 'id', 'item', '', $filterStatusPessoa);

        $filterTamanho = new TCriteria;
        $filterTamanho->add(new TFilter('lista_id', '=', '6'));
        $tm_camisa   = new TDBCombo('tm_camisa', 'adea', 'ListaItens', 'id', 'item', 'id', $filterTamanho);

        //endereço da pessoa
        //`cep`, `logradouro_id`, `n`, `bairro_id`, `ponto_referencia`
        $cep                 = new TEntry('cep');
        $cep->setMask('99.999-999');
        $estado_id       = new TDBCombo('estado_id', 'adea', 'Estado', 'id', 'estado', 'estado');
        $estado_id->enableSearch();

        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $cidade_id = new TDBCombo('cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $filter);
        $cidade_id->enableSearch();

        $filterItem = new TCriteria;
        $filterItem->add(new TFilter('lista_id', '=', '1'));
        $tipo_id       = new TDBCombo('tipo_id', 'adea', 'ListaItens', 'id', 'item', 'item', $filterItem);
        $tipo_id->enableSearch();

        $logradouro_id = new TDBCombo('logradouro_id', 'adea', 'Logradouro', 'id', 'logradouro', 'logradouro', $filter);
        $logradouro_id->enableSearch();

        $bairro_id = new TDBCombo('bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $filter);
        $bairro_id->enableSearch();

        $n                  = new TEntry('n');
        $ponto_referencia   = new TEntry('ponto_referencia');

        // define some properties for the form fields
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $cpf_cnpj->setSize('100%');
        $nome->setSize('100%');
        $popular->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $genero->setSize('100%');
        $dt_nascimento->setSize('100%');
        $profissao_id->setSize('100%');
        $tm_camisa->setSize('100%');
        $status_pessoa->setSize('100%');

        $cep->setSize('100%');
        $estado_id->setSize('100%');
        $cidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $logradouro_id->setSize('100%');
        $bairro_id->setSize('100%');
        $n->setSize('100%');
        $ponto_referencia->setSize('100%');

        //Dados
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `cep`,
        //`logradouro_id`, `n`, `bairro_id`, `ponto_referencia`, `status_pessoa`, `ck_pessoa`
        $this->form->appendPage('Dados');
        $row = $this->form->addFields(
            [new TLabel('Cod.'),     $id],
            [new TLabel('CPF'),     $cpf_cnpj]
        );
        $row->layout = ['col-sm-3', 'col-sm-9'];

        $row = $this->form->addFields([new TLabel('Nome Completo'),     $nome]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Popular'),
                $popular
            ],
            [
                new TLabel('Fone'),
                $fone
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields([new TLabel('Email'),    $email]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [new TLabel('DN'),
                $dt_nascimento
            ],
            [
                new TLabel('Gênero'),
                $genero
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields([new TLabel('Profissão'),    $profissao_id]);
        $row->layout = ['col-sm-12'];

        $row = $this->form->addFields(
            [
                new TLabel('Tamanho Camisa'),
                $tm_camisa
            ],
            [
                new TLabel('Status'),
                $status_pessoa
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        //Endereço
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `cep`,
        //`logradouro_id`, `n`, `bairro_id`, `ponto_referencia`, `status_pessoa`, `ck_pessoa`

        $this->form->appendPage('Endereço');
        $this->form->addFields([new TLabel('CEP')], [$cep]);

        $this->form->addFields(
            [new TLabel('Estado')],
            [$estado_id],
            [new TLabel('Cidade')],
            [$cidade_id]
        );

        $this->form->addFields(
            [new TLabel('Tipo')],
            [$tipo_id],
            [new TLabel('Endereço')],
            [$logradouro_id]
        );

        $this->form->addFields(
            [new TLabel('Nº')],
            [$n],
            [new TLabel('Bairro')],
            [$bairro_id]
        );

        $this->form->addFields([new TLabel('Ponto de Referência')],    [$ponto_referencia]);

        $estado_id->setChangeAction(new TAction(array($this, 'onStateChange')));
        $cidade_id->setChangeAction(new TAction(array($this, 'onCityChange')));
        $tipo_id->setChangeAction(new TAction(array($this, 'onTipoChange')));

        //Religião
        $this->form->appendPage('Profissional');
        $cargo_id   = new TDBUniqueSearch('cargo_id', 'adea', 'Ocupacao', 'id', 'titulo');
        $cargo_id->setMask('{titulo} ({codigo})');
        $cargo_id->setMinLength(3);
        $cargo_id->setSize('100%');
        $filterLocal = new TCriteria;
        $filterLocal->add(new TFilter('tipo_pessoa', '=', '78'));
        $local_id   = new TDBUniqueSearch('local_id', 'adea', 'Pessoa', 'id', 'nome', '', $filterLocal);
        $local_id->setMask('{nome} ({popular})');
        $local_id->setMinLength(1);
        $local_id->setSize('100%');
        $local_id->placeholder = 'Buscar Instituição/Empresa...)';
        $local_id->setChangeAction(new TAction(array($this, 'onChangeCheckLocal')));

        $obs_trabalho = new TEntry('obs_trabalho');
        $obs_trabalho->setSize('100%');
        $obs_trabalho->placeholder = 'Instituição/Empresa: endereço / contato...)';

        $this->form->addFields([new TLabel('Ocupação')],    [$cargo_id]);
        $this->form->addFields([new TLabel('Local')],    [$local_id]);
        $this->form->addFields([new TLabel('Novo Local')],    [$obs_trabalho]);
        self::onChangeCheckLocal('local_id');

        //Trabalho
        $this->form->appendPage('Religiosa');
        $filterReligiao = new TCriteria;
        $filterReligiao->add(new TFilter('lista_id', '=', '14'));
        $religiao_id       = new TDBCombo('religiao_id', 'adea', 'ListaItens', 'id', '{item} {(id)}', 'id', $filterReligiao);
        $religiao_id->setSize('100%');
        $religiao_id->setChangeAction(new TAction(array($this, 'onChangeType')));

        $filterIgreja = new TCriteria;
        $filterIgreja->add(new TFilter('tipo_pessoa', '=', '79'));
        $igreja_id   = new TDBUniqueSearch('igreja_id', 'adea', 'Pessoa', 'id', 'nome', '', $filterIgreja);
        $igreja_id->setMask('{nome} ({popular})');
        $igreja_id->setMinLength(1);
        $igreja_id->setSize('100%');
        $igreja_id->placeholder = 'Buscar Instituição/Empresa...)';
        $igreja_id->setChangeAction(new TAction(array($this, 'onChangeCheckIgreja')));

        $obs_religiao     = new TEntry('obs_religiao');
        $obs_religiao->setSize('100%');

        // fire change event
        //self::onChangeType(['religiao_id' => '']);

        $this->form->addFields([new TLabel('Religião')],    [$religiao_id]);
        $this->form->addFields([new TLabel('Igreja/Templo')],    [$igreja_id]);
        $this->form->addFields([new TLabel('Nova Igreja/Templo')],    [$obs_religiao]);

        //$this->form->appendPage('Informática');

        $this->form->appendPage('Habilidades');
        $filterHabilidades = new TCriteria;
        $filterHabilidades->add(new TFilter('lista_itens_id', '=', '35'));
        $habilidade_lista = new TDBCheckGroup('habilidade_lista', 'adea', 'ListaItensSub', 'id', 'item', 'id', $filterHabilidades);
        $this->form->addFields([new TLabel('Musical')],     [$habilidade_lista]);

        $this->form->addContent(['<hr>']);

        $filterInfor = new TCriteria;
        $filterInfor->add(new TFilter('lista_itens_id', '=', '48'));
        $nivel_info  = new TDBRadioGroup('nivel_info', 'adea', 'ListaItensSub', 'id', 'item', 'id', $filterInfor);
        $nivel_info->setUseButton();
        $nivel_info->setLayout('horizontal');
        $this->form->addFields([new TLabel('Informática')],     [$nivel_info]);

        $this->form->appendPage('Restrinções');
        $filterHabilidades = new TCriteria;
        $filterHabilidades->add(new TFilter('lista_id', '=', '9'));
        $restrincao_type = new TDBCombo('restrincao_type[]', 'adea', 'ListaItens', 'id', 'item', 'id', $filterHabilidades);
        $restrincao_type->setSize('100%');
        $restrincao_value = new TEntry('restrincao_value[]');
        $restrincao_value->setSize('100%');

        $this->restrincoes = new TFieldList;
        $this->restrincoes->addField('<b>Tipo</b>', $restrincao_type, ['width' => '50%']);
        $this->restrincoes->addField('<b>Detalhes</b>', $restrincao_value, ['width' => '50%']);
        $this->form->addField($restrincao_type);
        $this->form->addField($restrincao_value);
        $this->restrincoes->enableSorting();

        $this->form->addContent([new TLabel('Restrinção')], [$this->restrincoes]);

        //$cpf_cnpj->addValidation('CPF', new TRequiredValidator);
        $cpf_cnpj->addValidation('CPF', new TCPFValidator);
        $nome->addValidation('Nome Completo', new TRequiredValidator);
        $popular->addValidation('Nome Popular', new TRequiredValidator);
        $fone->addValidation('Fone', new TMinLengthValidator, array(11)); // cannot be less the 3 characters
        $email->addValidation('Email', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $genero->addValidation('Gênero', new TRequiredValidator);
        $dt_nascimento->addValidation('Data de Nascimento', new TRequiredValidator);
        $profissao_id->addValidation('Profissao', new TRequiredValidator);
        $estado_id->addValidation('Estado', new TRequiredValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $tipo_id->addValidation('Tipo', new TRequiredValidator);
        $logradouro_id->addValidation('Logradouro', new TRequiredValidator);
        $bairro_id->addValidation('Bairro', new TRequiredValidator);
        $n->addValidation('Nº', new TRequiredValidator);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave'], ['embedded' => $embedded ? '1' : '0']), 'fa:save green');

        if (!$this->embedded) {
            $this->form->addActionLink('Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');
            $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        }

        // add the form inside the page
        parent::add($this->form);
    }

    public static function onChangeCheckLocal($param)
    {
        if (!empty($param['local_id'])) {
            TQuickForm::hideField('form_pessoa', 'obs_trabalho');
            TEntry::disableField('form_pessoa', 'obs_trabalho');
            //TEntry::disableField('form_pessoa', 'obs_trabalho');
            //TCheckGroup::enableField('form_enable_disable', 'block2_check');
        } else {
            TQuickForm::showField('form_pessoa', 'obs_trabalho');
            TEntry::enableField('form_pessoa', 'obs_trabalho');
        }
    }

    public static function onChangeType($param)
    {
        if ($param['religiao_id'] >= 80 and $param['religiao_id'] <= 83) {
            TQuickForm::showField('form_pessoa', 'igreja_id');
            TQuickForm::showField('form_pessoa', 'obs_religiao');
        } else {
            TQuickForm::hideField('form_pessoa', 'igreja_id');
            TQuickForm::hideField('form_pessoa', 'obs_religiao');
        }
    }

    public static function onChangeCheckIgreja($param)
    {
        if (isset($param['igreja_id']) and !empty($param['igreja_id']) and $param['igreja_id'] != '') {
            TQuickForm::hideField('form_pessoa', 'obs_religiao');
        } else {
            TQuickForm::showField('form_pessoa', 'obs_religiao');
        }
    }

    /**
     * method onSave
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {

        //converte a data static BR para Americana
        $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_nascimento']);
        $param['dt_nascimento'] = $novadata->format('Y/m/d');
        $param['tipo_pessoa'] = 77;
        $param['ck_pessoa'] = 1;

        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `cep`, `logradouro_id`,
        //`n`, `bairro_id`, `ponto_referencia`, `status_pessoa`, `ck_pessoa

        //id`, `pessoa_id`, `genero`, `dt_nascimento`, `profissao_id`, `tm_camisa`

        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            if (empty($param['nome'])) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Nome'));
            }

            // read the form data and instantiates an Active Record

            if (isset($param['logradouro_id'])) {
                $consultaendereco = Endereco::where('logradouro_id', '=', $param['logradouro_id'])->where('n', '=', $param['n'])->where('bairro_id', '=', $param['bairro_id'])->first();

                if ($consultaendereco) {
                    $param['endereco_id'] = $consultaendereco->id;
                } else {
                    $endereco = new Endereco();
                    $endereco->fromArray($param);
                    $endereco->store();
                    $param['endereco_id'] = $endereco->id;
                }
            }

            $pessoa = new Pessoa();
            $pessoa->fromArray($param);
            $pessoa->store();
            
            PessoaFisica::where('pessoa_id', '=', $pessoa->id)->delete();
            $pessoafisica = new PessoaFisica();
            $pessoafisica->pessoa_id = $pessoa->id;
            $pessoafisica->fromArray($param);
            $pessoafisica->store();

            PessoaTrabalho::where('pessoa_id', '=', $pessoafisica->id)->delete();
            if (!empty($param['cargo_id'])) {
                $pessoatrabalho = new PessoaTrabalho();
                $pessoatrabalho->pessoa_id = $pessoa->id;
                $pessoatrabalho->cargo_id = $param['cargo_id'];
                $pessoatrabalho->local_id = $param['local_id'];
                if (empty($param['local_id'])) {
                    $pessoatrabalho->obs_trabalho = $param['obs_trabalho'];
                }
                $pessoatrabalho->store();
            }

            PessoaReligiao::where('pessoa_id', '=', $pessoa->id)->delete();
            if (!empty($param['religiao_id'])) {
                $pessoareligiao = new PessoaReligiao();
                $pessoareligiao->pessoa_id = $pessoa->id;
                $pessoareligiao->religiao_id = $param['religiao_id'];

                if ($param['religiao_id'] >= 80 and $param['religiao_id'] <= 83) {
                    $pessoareligiao->igreja_id = $param['igreja_id'];
                    if (empty($param['igreja_id'])) {
                        $pessoareligiao->obs_religiao = $param['obs_religiao'];
                    }
                }
                $pessoareligiao->store();
            }

            PessoaHabilidade::where('pessoa_id', '=', $pessoa->id)->delete();
            if (!empty($param['habilidade_lista'])) {
                foreach ($param['habilidade_lista'] as $habilidade_id) {
                    // add the skill to the customer
                    $pessoahabilidade = new PessoaHabilidade();
                    $pessoahabilidade->pessoa_id = $pessoa->id;
                    $pessoahabilidade->tipo_hab_id = $habilidade_id;
                    $pessoahabilidade->store();
                }
            }

            $data = new stdClass;
            $data->id = $pessoa->id;
            TForm::sendData('form_pessoa', $data);

            if (!$param['embedded']) {
                TScript::create("Template.closeRightPanel()");

                $posAction = new TAction(array('PessoaFisicaDataGridView', 'onReload'));
                $posAction->setParameter('target_container', 'adianti_div_content');

                // shows the success message
                new TMessage('info', 'Record saved', $posAction);
            } else {
                TWindow::closeWindowByName('PessoaFisicaFormWindow');
            }

            TTransaction::close(); // close the transaction
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * method onEdit
     * Edit a record data
     */
    function onEdit($param)
    {

        try {
            if (isset($param['id'])) {
                // open a transaction with database 'samples'
                TTransaction::open('adea');

                // load the Active Record according to its ID
                //$pessoa = new Pessoa($param['id']);
                //$pessoafisica = PessoaFisica::where('pessoa_id', '=', $param['id'])->first();
                $pessoa = new Pessoa($param['id']);
                $pessoafisica = PessoaFisica::where('pessoa_id', '=', $param['id'])->first();
                $endereco = new Endereco($pessoa->endereco_id);

                $pessoatrabalho = PessoaTrabalho::where('pessoa_id', '=', $param['id'])->first();
                if ($pessoatrabalho) {
                    $pessoa->cargo_id = $pessoatrabalho->cargo_id;
                    $pessoa->local_id = $pessoatrabalho->local_id;
                    $pessoa->obs_trabalho = $pessoatrabalho->obs_trabalho;
                }
                $pessoareligiao = PessoaReligiao::where('pessoa_id', '=', $param['id'])->first();
                if ($pessoareligiao) {
                    $pessoa->religiao_id = $pessoareligiao->religiao_id;
                    $pessoa->igreja_id = $pessoareligiao->igreja_id;
                    $pessoa->obs_religiao = $pessoareligiao->obs_religiao;
                }

                //load the contacts (composition)
                $restrincoes = PessoaRestrincao::where('pessoa_id', '=', $param['id'])->load();

                if ($restrincoes) {
                    $this->restrincoes->addHeader();
                    foreach ($restrincoes as $restrincao) {
                        $restrincao_detail = new stdClass;
                        $restrincao_detail->restrincao_type  = $restrincao->tipo_restrincao;
                        $restrincao_detail->restrincao_value = $restrincao->nome_restrincao;

                        $this->restrincoes->addDetail($restrincao_detail);
                    }

                    $this->restrincoes->addCloneAction();
                } else {
                    $this->onClear($param);
                }

                // load the skills (aggregation)
                $habilidades = PessoaHabilidade::where('pessoa_id', '=', $param['id'])->load();

                $habilidade_lista = array();
                if ($habilidades) {
                    foreach ($habilidades as $habilidade) {
                        $habilidade_lista[] = $habilidade->tipo_hab_id;
                    }
                }

                $pessoa->habilidade_lista = $habilidade_lista;

                // fill the form with the active record data*/
                $this->form->setData($pessoa);
                $this->form->setData($pessoafisica);

                if ($endereco) {
                    // force fire events
                    $data = new stdClass;
                    $data->cep                  = $endereco->cep;
                    $data->estado_id            = $endereco->Bairro->Cidade->Estado->id;
                    $data->cidade_id            = $endereco->Bairro->Cidade->id;
                    $data->tipo_id              = $endereco->Logradouro->Tipo->id;
                    $data->logradouro_id        = $endereco->logradouro_id;
                    $data->n                    = $endereco->n;
                    $data->bairro_id            = $endereco->bairro_id;
                    $data->ponto_referencia     = $endereco->ponto_referencia;
                    $data->local_id             = $pessoa->local_id;
                    $data->igreja_id            = $pessoa->igreja_id;
                    $data->religiao_id          = $pessoa->religiao_id;
                    TForm::sendData('form_pessoa', $data);
                }

                // close the transaction
                TTransaction::close();
            } else {
                $this->onClear($param);
            }
        } catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear();

        $this->restrincoes->addHeader();
        $this->restrincoes->addDetail(new stdClass);
        $this->restrincoes->addCloneAction();
    }

    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
