<?php

use Adianti\Validator\TCNPJValidator;
use Adianti\Widget\Form\TDate;

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
class CasalFormView extends TPage
{

    private $form; // form
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
        $this->form = new BootstrapFormBuilder('form_casal');
        if (!$this->embedded) {
            $this->form->setFormTitle('Casal');
        }
        $this->form->setClientValidation(true);

        //dados do casal
        $id             = new TEntry('id');
        $filterPessoa1 = new TCriteria;
        $filterPessoa1->add(new TFilter('genero', '=', 'M'));
        $ele_id   = new TDBUniqueSearch('ele_id', 'adea', 'ViewPessoaFisica', 'id', 'nome', '', $filterPessoa1);
        $ele_id->setMask('{nome} ({popular})');
        $ele_id->setMinLength(3);

        $filterPessoa2 = new TCriteria;
        $filterPessoa2->add(new TFilter('genero', '=', 'F'));
        $ela_id   = new TDBUniqueSearch('ela_id', 'adea', 'ViewPessoaFisica', 'id', 'nome', '', $filterPessoa2);
        $ela_id->setMask('{nome} ({popular})');
        $ela_id->setMinLength(3);

        $dt_casamento       = new TDate('dt_casamento');
        $dt_casamento->setDatabaseMask('yyyy-mm-dd');
        $dt_casamento->setMask('dd/mm/yyyy');

        $filterPessoa3 = new TCriteria;
        $filterPessoa3->add(new TFilter('tipo_pessoa', '=', '86'));
        $cartorio_id   = new TDBUniqueSearch('cartorio_id', 'adea', 'Pessoa', 'id', 'nome', '', $filterPessoa3);
        $cartorio_id->setMask('{nome} ({popular})');
        $cartorio_id->setMinLength(3);

        $filterStatusCasal = new TCriteria;
        $filterStatusCasal->add(new TFilter('lista_id', '=', '5'));
        $status_casal   = new TDBCombo('status_casal', 'adea', 'ListaItens', 'id', 'item', '', $filterStatusCasal);

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
        //id`, `tipo_pessoa`, `cpf_cnpj`, `nome`, `popular`, `fone`, `email`, `status_pessoa`, `ck_pessoa`
        //id`, `pessoa_id`, `genero`, `dt_nascimento`, `profissao_id`, `tm_camisa`
        $id->setEditable(FALSE);
        $id->setSize('100%');
        $ele_id->setSize('100%');
        $ela_id->setSize('100%');
        $dt_casamento->setSize('100%');
        $cartorio_id->setSize('100%');
        //conducao_propria
        $status_casal->setSize('100%');

        $cep->setSize('100%');
        $estado_id->setSize('100%');
        $cidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $logradouro_id->setSize('100%');
        $bairro_id->setSize('100%');
        $n->setSize('100%');
        $ponto_referencia->setSize('100%');

        $this->form->appendPage('Dados');
        $this->form->addFields(
            [new TLabel('Cod.')],
            [$id],
            [new TLabel('Status')],
            [$status_casal]
        );

        $this->form->addFields(
            [new TLabel('Ele')],
            [$ele_id]
        );

        $this->form->addFields(
            [new TLabel('Ela')],
            [$ela_id]
        );

        $this->form->addFields(
            [new TLabel('Casamento')],
            [$dt_casamento],
            [new TLabel('Cartório')],
            [$cartorio_id]
        );

        $this->form->appendPage('Endereço');
        $this->form->addFields([new TLabel('CEP')],    [$cep]);

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

        $this->form->appendPage('Condução Própria');
        $conducao_resposta  = new TCombo('conducao_resposta');
        $conducao_resposta->setChangeAction(new TAction(array($this, 'onChangeConducao')));
        $combo_items = array();
        $combo_items['n'] = 'Não';
        $combo_items['s'] = 'Sim';
        $conducao_resposta->addItems($combo_items);
        $conducao_resposta->setValue('n');
        $placa  = new TEntry('placa');
        $placa->placeholder = 'Ex.: ABC-1234 ou ABC1D34)';
        $detalhes_conducao  = new TEntry('detalhes_conducao');
        $detalhes_conducao->placeholder = 'Informe: Marca/Modelo/Cor)';

        $conducao_resposta->setSize('100%');
        $placa->setSize('100%');
        $detalhes_conducao->setSize('100%');

        $row = $this->form->addFields(
            [new TLabel('Possui'),     $conducao_resposta]
        );
        $row->layout = ['col-sm-4'];

        $row = $this->form->addFields(
            [new TLabel('Placa'),     $placa],
            [new TLabel('Detalhes'),     $detalhes_conducao]
        );
        $row->layout = ['col-sm-4', 'col-sm-8'];

        $estado_id->setChangeAction(new TAction(array($this, 'onStateChange')));
        $cidade_id->setChangeAction(new TAction(array($this, 'onCityChange')));
        $tipo_id->setChangeAction(new TAction(array($this, 'onTipoChange')));

        $ele_id->addValidation('Ele', new TRequiredValidator);
        $ela_id->addValidation('Ela', new TRequiredValidator);
        $dt_casamento->addValidation('Casamento', new TRequiredValidator);
        $cartorio_id->addValidation('Cartório', new TRequiredValidator);
        $status_casal->addValidation('Status', new TRequiredValidator);

        $estado_id->addValidation('Estado', new TRequiredValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $tipo_id->addValidation('Tipo', new TRequiredValidator);
        $logradouro_id->addValidation('Logradouro', new TRequiredValidator);
        $bairro_id->addValidation('Bairro', new TRequiredValidator);
        $n->addValidation('Nº', new TRequiredValidator);

        $conducao_resposta->addValidation('Conducao', new TRequiredValidator);
        //$placa->addValidation('Placa', new TRequiredValidator);
        //$detalhes_conducao->addValidation('Detalhes', new TRequiredValidator);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave'], ['embedded' => $embedded ? '1' : '0']), 'fa:save green');

        if (!$this->embedded) {
            $this->form->addActionLink('Clear', new TAction([$this, 'onClear']), 'fa:eraser red');
            $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        }

        // add the form inside the page
        parent::add($this->form);
    }

    public static function onChangeConducao($param)
    {
        if ($param['conducao_resposta'] == 's') {
            TQuickForm::showField('form_casal', 'placa');
            TQuickForm::showField('form_casal', 'detalhes_conducao');
        } else {
            TQuickForm::hideField('form_casal', 'placa');
            TQuickForm::hideField('form_casal', 'detalhes_conducao');
        }
    }

    /**
     * method onSave
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {
        $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_casamento']);
        $param['dt_casamento'] = $novadata->format('Y/m/d');
        $param['ck_casal'] = 1;

        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            if (empty($param['dt_casamento'])) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', 'Casamento'));
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

            $ele = new Pessoa($param['ele_id']);
            $ela = new Pessoa($param['ela_id']);
            $ele->endereco_id = $param['endereco_id'];
            $ela->endereco_id = $param['endereco_id'];
            $ele->store();
            $ela->store();

            if ($param['conducao_resposta'] = 's') {
                if (!empty($param['placa'])) {
                    $consultaconducao = ConducaoPropria::where('placa', '=', $param['placa'])->first();
                    if ($consultaconducao) {
                        $param['conducao_propria'] = $consultaconducao->id;
                    } else {
                        $conducaopropria = new ConducaoPropria();
                        $conducaopropria->fromArray($param);
                        $conducaopropria->store();
                        $param['conducao_propria'] = $conducaopropria->id;
                    }
                }
            } else {
                $param['conducao_propria'] = 0;
            }

            $casal = new Casal();
            $casal->fromArray($param);
            $casal->store();

            $data = new stdClass;
            $data->id = $casal->id;
            TForm::sendData('form_casal', $data);

            if (!$param['embedded']) {
                TScript::create("Template.closeRightPanel()");

                $posAction = new TAction(array('CasalDataGridView', 'onReload'));
                $posAction->setParameter('target_container', 'adianti_div_content');

                // shows the success message
                new TMessage('info', 'Record saved', $posAction);
            } else {
                TWindow::closeWindowByName('CasalFormWindow');
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
                $casal = new Casal($param['id']);
                $endereco = new Endereco($casal->Ele->endereco_id);

                // fill the form with the active record data*/
                $this->form->setData($casal);

                if ($endereco) {
                    // force fire events
                    $data = new stdClass;
                    $data->cep                  = $endereco->cep;
                    $data->estado_id            = $endereco->bairro->cidade->estado->id;
                    $data->cidade_id            = $endereco->bairro->cidade->id;
                    $data->tipo_id              = $endereco->logradouro->tipo->id;
                    $data->logradouro_id        = $endereco->logradouro_id;
                    $data->n                    = $endereco->n;
                    $data->bairro_id            = $endereco->bairro_id;
                    $data->ponto_referencia     = $endereco->ponto_referencia;
                    TForm::sendData('form_casal', $data);
                }

                if (isset($casal->conducao_propria) and $casal->conducao_propria != 0) {
                    $conducaopropria = new ConducaoPropria($casal->conducao_propria);
                    $conducaopropria->conducao_resposta = 's';
                    TForm::sendData('form_casal', $conducaopropria);
                } else {
                    $data = new stdClass;
                    $data->conducao_resposta = 'n';
                    TForm::sendData('form_casal', $data);
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
    }

    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    public static function onStateChange($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['estado_id'])) {
                $criteria = TCriteria::create(['estado_id' => $param['estado_id']]);

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_casal', 'cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $criteria, TRUE);
            } else {
                TCombo::clearField('form_casal', 'cidade_id');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onCityChange($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['cidade_id'])) {
                $criteria = TCriteria::create(['cidade_id' => $param['cidade_id']]);

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_casal', 'bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $criteria, TRUE);
            } else {
                TCombo::clearField('form_casal', 'bairro_id');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onTipoChange($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['cidade_id']) and !empty($param['cidade_id'])) {

                TTransaction::open('adea');
                $logradouros = Logradouro::where('cidade_id', '=', $param['cidade_id'])->where('tipo_id', '=', $param['tipo_id'])->load();
                $logradourosrray = array();
                foreach ($logradouros as $logradouro) {
                    $logradourosrray[$logradouro->id] = $logradouro->logradouro;
                }
                TCombo::reload('form_casal', 'logradouro_id', $logradourosrray);
                TTransaction::close();
            } else {
                TCombo::clearField('form_casal', 'logradouro_id');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
}
