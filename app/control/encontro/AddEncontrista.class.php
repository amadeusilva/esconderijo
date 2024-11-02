<?php

use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;

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
class AddEncontrista extends TWindow
{
    use ControleEndereco;
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
        parent::setSize(600, null);
        parent::setTitle('Encontrista');

        $this->setDatabase('adea');    // defines the database
        //$this->setActiveRecord('PessoaContato');   // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_AddEncontrista');
        $this->form->setClientValidation(true);

        // create the form fields

        // dados dele
        //$this->form->appendPage('Dados Dele');
        $filterGeneroM = new TCriteria;
        $filterGeneroM->add(new TFilter('genero', '=', 'M'));
        $ele_nome  = new TDBEntry('ele_nome', 'adea', 'ViewPessoaFisica', 'nome', '', $filterGeneroM);
        $ele_nome->setInnerIcon(new TImage('fa:user #2196F3'), 'left');
        $ele_nome->placeholder = ' Nome Completo';
        $ele_nome->forceUpperCase();
        $ele_nome->setSize('100%');

        $ele_popular  = new TDBEntry('ele_popular', 'adea', 'ViewPessoaFisica', 'popular', '', $filterGeneroM);
        $ele_popular->setInnerIcon(new TImage('fa:user #2196F3'), 'left');
        $ele_popular->placeholder = ' Apelido';
        $ele_popular->forceUpperCase();
        $ele_popular->setSize('100%');

        $row = $this->form->addFields([new TLabel('Popular dele:', '#2196F3'), $ele_popular], [new TLabel('Nome dele:', '#2196F3'), $ele_nome]);
        $row->layout = ['col-sm-5', 'col-sm-7'];

        $ele_nome->addValidation('Nome Dele', new TRequiredValidator);
        $ele_nome->setExitAction(new TAction(array($this, 'onNome')));
        $ele_popular->addValidation('Popular Dele', new TRequiredValidator);

        $dn_ele       = new TDate('dn_ele');
        $dn_ele->setMask('dd/mm/yyyy');
        $dn_ele->setDatabaseMask('yyyy-mm-dd');
        $dn_ele->setSize('100%');
        $dn_ele->addValidation('Nascimento dele', new TRequiredValidator);

        $ctt_ele       = new TEntry('ctt_ele');
        $ctt_ele->setMask('(99) 99999-9999');
        $ctt_ele->setSize('100%');
        $ctt_ele->setValue('00000000000');
        //$ctt_ele->addValidation('Contato Dele', new TRequiredValidator);

        $row = $this->form->addFields([new TLabel('Nascimento dele:', '#2196F3'), $dn_ele], [new TLabel('Contato dele:', '#2196F3'), $ctt_ele]);
        $row->layout = ['col-sm-6', 'col-sm-6'];

        // dados dela
        //$this->form->appendPage('Dados Dela');
        $filterGeneroF = new TCriteria;
        $filterGeneroF->add(new TFilter('genero', '=', 'F'));
        $ela_nome  = new TDBEntry('ela_nome', 'adea', 'ViewPessoaFisica', 'nome', '', $filterGeneroF);
        $ela_nome->setInnerIcon(new TImage('fa:user #FF007F'), 'left');
        $ela_nome->placeholder = ' Nome Completo';
        $ela_nome->forceUpperCase();
        $ela_nome->setSize('100%');

        $ela_popular  = new TDBEntry('ela_popular', 'adea', 'ViewPessoaFisica', 'popular', '', $filterGeneroF);
        $ela_popular->setInnerIcon(new TImage('fa:user #FF007F'), 'left');
        $ela_popular->placeholder = ' Apelido';
        $ela_popular->forceUpperCase();
        $ela_popular->setSize('100%');

        $row = $this->form->addFields([new TLabel('Popular dela:', '#FF007F'), $ela_popular], [new TLabel('Nome dela:', '#FF007F'), $ela_nome]);
        $row->layout = ['col-sm-5', 'col-sm-7'];

        $ela_nome->addValidation('Nome dela', new TRequiredValidator);
        $ela_popular->addValidation('Popular dela', new TRequiredValidator);

        $dn_ela       = new TDate('dn_ela');
        $dn_ela->setMask('dd/mm/yyyy');
        $dn_ela->setDatabaseMask('yyyy-mm-dd');
        $dn_ela->setSize('100%');
        $dn_ela->addValidation('Nascimento dela', new TRequiredValidator);

        $ctt_ela       = new TEntry('ctt_ela');
        $ctt_ela->setMask('(99) 99999-9999');
        $ctt_ela->setSize('100%');
        $ctt_ela->setValue('00000000000');
        //$ctt_ela->addValidation('Contato dela', new TRequiredValidator);

        $row = $this->form->addFields([new TLabel('Nascimento dela:', '#FF007F'), $dn_ela], [new TLabel('Contato dela:', '#FF007F'), $ctt_ela]);
        $row->layout = ['col-sm-6', 'col-sm-6'];

        // dados deles
        //$this->form->appendPage('Dados Relação');
        $relacao_id       = new TEntry('relacao_id');
        $relacao_id->setEditable(FALSE);
        $relacao_id->setSize('100%');

        $refer_id       = new TEntry('refer_id');
        $refer_id->setEditable(FALSE);
        $refer_id->setSize('100%');

        $dt_casamento       = new TDate('dt_casamento');
        $dt_casamento->setMask('dd/mm/yyyy');
        $dt_casamento->setDatabaseMask('yyyy-mm-dd');
        $dt_casamento->setSize('100%');
        $dt_casamento->addValidation('Casamento', new TRequiredValidator);

        $row = $this->form->addFields([new TLabel('Casamento.:', 'red'), $dt_casamento], [new TLabel('Ref:', 'red'), $refer_id], [new TLabel('Cod:', 'red'), $relacao_id]);
        $row->layout = ['col-sm-6', 'col-sm-3', 'col-sm-3'];

        $filter = new TCriteria;
        $filter->add(new TFilter('evento_id', '=', '701'));
        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', 'sigla', 'id', $filter);
        $encontro_id->setSize('100%');
        $encontro_id->enableSearch();
        $encontro_id->setValue(10);
        $encontro_id->addValidation('Encontro', new TRequiredValidator);

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'abrev', 'id', $filterCirculo);
        $circulo_id->setSize('100%');
        $circulo_id->setValue(17);
        $circulo_id->addValidation('Círculo', new TRequiredValidator);

        $row = $this->form->addFields([new TLabel('Encontro:', 'red'), $encontro_id], [new TLabel('Círculo:', 'red'), $circulo_id]);
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $options = [1 => 'Santana', 2 => 'Macapá', 3 => 'Belém'];

        $local = new TRadioGroup('local');
        $local->setUseButton();
        $local->addItems($options);
        $local->setLayout('horizontal');
        $local->setValue(1);
        $local->setSize('100%');

        $row = $this->form->addFields([new TLabel('Local:', 'red')], [$local]);
        $row->layout = ['col-sm-12', 'col-sm-12'];

        /*
        // dados endereço
        $this->form->appendPage('Endereço');
        $cep                 = new TEntry('cep');
        $cep  = new TDBEntry('cep', 'adea', 'Endereco', 'cep');
        $cep->setMask('99.999-999');
        $cep->setValue(68925165);
        $cep->setExitAction(new TAction(array($this, 'onCEPAction')));

        $estado_id       = new TDBCombo('estado_id', 'adea', 'Estado', 'id', 'estado', 'estado');
        $estado_id->enableSearch();

        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $cidade_id = new TDBCombo('cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $filter);
        $cidade_id->enableSearch();

        $tipo_id       = new TDBCombo('tipo_id', 'adea', 'TipoLogradouro', 'id', 'tipo', 'tipo');
        $tipo_id->enableSearch();

        $logradouro_id = new TDBCombo('logradouro_id', 'adea', 'Logradouro', 'id', 'logradouro', 'logradouro', $filter);
        $logradouro_id->enableSearch();

        $bairro_id = new TDBCombo('bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $filter);
        $bairro_id->enableSearch();

        $n                  = new TEntry('n');
        $n->setValue('S/N');

        $ponto_referencia  = new TDBEntry('ponto_referencia', 'adea', 'ENDERECO', 'ponto_referencia');
        $ponto_referencia->placeholder = 'PRÓXIMO A PRAÇAS, HOSPITAIS, EMPRESAS...';
        $ponto_referencia->forceUpperCase();
        

        // define some properties for the form fields
        $tipo_id->setEditable(FALSE);
        $cep->setSize('100%');
        $estado_id->setSize('100%');
        $cidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $logradouro_id->setSize('100%');
        $bairro_id->setSize('100%');
        $n->setSize('100%');
        $ponto_referencia->setSize('100%');

        $row = $this->form->addFields(
            [new TLabel('CEP'),    $cep],
            [
                new TLabel('Estado'),
                $estado_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Cidade'),
                $cidade_id
            ],
            [
                new TLabel('Tipo'),
                $tipo_id
            ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $this->form->addFields(
            [
                new TLabel('Endereço'),
                $logradouro_id
            ],
            [
                new TLabel('Nº'),
                $n
            ]
        );
        $row->layout = ['col-sm-9', 'col-sm-3'];

        $row = $this->form->addFields(
            [
                new TLabel('Bairro'),
                $bairro_id
            ],
            [
                new TLabel('Ponto de Referência'),
                $ponto_referencia
            ]
        );
        $row->layout = ['col-sm-5', 'col-sm-7'];

        

        $estado_id->setChangeAction(new TAction(array($this, 'onStateChange')));
        $cidade_id->setChangeAction(new TAction(array($this, 'onCityChange')));
        $tipo_id->setChangeAction(new TAction(array($this, 'onTipoChange')));

        */

        // validations
        //$estado_id->addValidation('Estado', new TRequiredValidator);
        //$cidade_id->addValidation('Cidade', new TRequiredValidator);
        //$tipo_id->addValidation('Tipo', new TRequiredValidator);
        //$logradouro_id->addValidation('Logradouro', new TRequiredValidator);
        //$bairro_id->addValidation('Bairro', new TRequiredValidator);
        //$n->addValidation('Nº', new TRequiredValidator);

        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');

        $this->setAfterSaveAction(new TAction([$this, 'onClose']));
        $this->setUseMessages(false);

        parent::add($this->form);
    }

    public static function onNome($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['ele_nome'])) {
                $buscapessoa = ViewPessoaFisica::where('nome', '=', $param['ele_nome'])->first();
                if ($buscapessoa) {
                    $nometela = new stdClass;
                    $nometela->ele_popular        = $buscapessoa->popular;
                    $nometela->dn_ele             = TDate::date2br($buscapessoa->dt_nascimento);

                    $buscacasal = ViewCasal::where('ele_id', '=', $buscapessoa->id)->first();


                    $nometela->relacao_id  = $buscacasal->relacao_id;

                    $nometela->dt_casamento  = TDate::date2br($buscacasal->dt_inicial);

                    $buscapessoa2 = new ViewPessoaFisica($buscacasal->ela_id);

                    $nometela->ela_nome           = $buscapessoa2->nome;
                    $nometela->ela_popular        = $buscapessoa2->popular;
                    $nometela->dn_ela             = TDate::date2br($buscapessoa2->dt_nascimento);

                    TForm::sendData('form_AddEncontrista', $nometela);
                }
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try {
            TTransaction::open('adea');   // open a transaction with database 'samples'
            if ($param['id'] and $param['casal_id']) {

                $key = $param['casal_id'];  // get the parameter
                $object = new ViewCasal($key);        // instantiates object City

                $object->dt_casamento = $object->dt_inicial;

                $object->ele_nome = $object->Ele->nome;
                $object->ele_popular = $object->Ele->popular;
                $object->dn_ele = $object->Ele->dt_nascimento;
                if (isset($object->Ele->Fone->contato) and !empty($object->Ele->Fone->contato)) {
                    $object->ctt_ele = $object->Ele->Fone->contato;
                }

                $object->ela_nome = $object->Ela->nome;
                $object->ela_popular = $object->Ela->popular;
                $object->dn_ela = $object->Ela->dt_nascimento;
                if (isset($object->Ela->Fone->contato) and !empty($object->Ela->Fone->contato)) {
                    $object->ctt_ela = $object->Ela->Fone->contato;
                }

                $encontrista = new ViewEncontrista($param['id']);
                $object->encontro_id = $encontrista->encontro_id;
                $object->circulo_id = $encontrista->circulo_id;


                $endereco = Endereco::find($object->Ele->endereco_id);        // instantiates object City

                if ($endereco) {

                    $dados_endereco = new stdClass;

                    if ($endereco->cep == '68.925-165') {
                        $dados_endereco->local            = 1;
                    } else if ($endereco->cep == '68.901-130') {
                        $dados_endereco->local            = 2;
                    } else if ($endereco->cep == '66.017-070') {
                        $dados_endereco->local            = 3;
                    }

                    $dados_endereco->refer_id            = $param['refer_id'];

                    TForm::sendData('form_AddEncontrista', $dados_endereco);
                }

                /*

                if ($endereco) {
                    $this->form->setData($endereco);   // fill the form with the active record data

                    // force fire events
                    $dados_endereco = new stdClass;
                    $dados_endereco->estado_id            = $endereco->Bairro->Cidade->Estado->id;
                    $dados_endereco->cidade_id            = $endereco->Bairro->Cidade->id;
                    $dados_endereco->tipo_id              = $endereco->Logradouro->Tipo->id;
                    $dados_endereco->logradouro_id        = $endereco->logradouro_id;
                    $dados_endereco->bairro_id            = $endereco->bairro_id;
                    TForm::sendData('form_AddEncontrista', $dados_endereco);
                }
                    */

                $this->form->setData($object);   // fill the form with the active record data

            } else {
                $this->form->clear(true);
            }
            TTransaction::close();           // close the transaction
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            $this->form->validate(); // run form validation

            $data = $this->form->getData(); // get form data as array

            if (isset($data->relacao_id) and !empty($data->relacao_id)) {
                $id_dele = ViewCasal::find($data->relacao_id);
                if ($id_dele) {
                    $ele = Pessoa::find($id_dele->ele_id);
                }
            } else {
                //inserir ele
                $ele = new Pessoa();
                $ele->tipo_pessoa = 1;
            }

            $ele->nome = $data->ele_nome;
            $ele->popular = $data->ele_popular;

            //endereco_id deles
            /*
            if (isset($data->logradouro_id) and !empty($data->logradouro_id)) {
                $consultaendereco = Endereco::where('logradouro_id', '=', $data->logradouro_id)->where('n', '=', $data->n)->where('bairro_id', '=', $data->bairro_id)->first();
                if ($consultaendereco) {
                    $ele->endereco_id = $consultaendereco->id;
                } else {
                    $endereco = new Endereco();
                    $endereco->cep = $data->cep;
                    $endereco->logradouro_id = $data->logradouro_id;
                    $endereco->n = $data->n;
                    $endereco->bairro_id = $data->bairro_id;
                    if (!$data->ponto_referencia) {
                        $endereco->ponto_referencia = 'SEM REFERÊNCIA';
                    } else {
                        $endereco->ponto_referencia = $data->ponto_referencia;
                    }

                    $endereco->store();
                    $ele->endereco_id = $endereco->id;
                }
            }
                */

            if (isset($data->local) and !empty($data->local)) {

                if ($data->local == 1) {
                    $ele->endereco_id = 2;
                } else if ($data->local == 2) {
                    $ele->endereco_id = 21;
                } else if ($data->local == 3) {
                    $ele->endereco_id = 1;
                }
            }

            if (!isset($data->relacao_id) or empty($data->relacao_id)) {
                $ele->status_pessoa = 21;
                $ele->ck_pessoa = 2;
            }
            $ele->store(); // save the object

            $ele_pf = PessoaFisica::where('pessoa_id', '=', $ele->id)->first();

            if (!$ele_pf) {
                $ele_pf = new PessoaFisica();
                $ele_pf->pessoa_id = $ele->id;
                $ele_pf->genero = 'M';
                $ele_pf->estado_civil_id = 803;
            }
            $ele_pf->dt_nascimento = $data->dn_ele;

            //$ele_pf->tm_camisa = '';
            $ele_pf->store(); // save the object

            PessoaContato::where('pessoa_id', '=', $ele->id)->where('tipo_contato_id', '=', 101)->delete();

            if (isset($data->ctt_ele) and !empty($data->ctt_ele)) {
                $contato_dela = new PessoaContato();
                $contato_dela->pessoa_id = $ele->id;
                $contato_dela->tipo_contato_id = 101;
                $contato_dela->contato = $data->ctt_ele;
                $contato_dela->status_contato_id = 1;
                $contato_dela->store();
            }

            if (isset($data->relacao_id) and !empty($data->relacao_id)) {
                $id_dela = ViewCasal::find($data->relacao_id);
                if ($id_dela) {
                    $ela = Pessoa::find($id_dele->ela_id);
                }
            } else {
                //inserir ela
                $ela = new Pessoa();
                $ela->tipo_pessoa = 1;
            }

            $ela->nome = $data->ela_nome;
            $ela->popular = $data->ela_popular;
            if (isset($ele->endereco_id) and !empty($ele->endereco_id)) {
                $ela->endereco_id = $ele->endereco_id;
            }
            if (!isset($data->relacao_id) or empty($data->relacao_id)) {
                $ela->status_pessoa = 21;
                $ela->ck_pessoa = 2;
            }
            $ela->store(); // save the object

            $ela_pf = PessoaFisica::where('pessoa_id', '=', $ela->id)->first();

            if (!$ela_pf) {
                $ela_pf = new PessoaFisica();
                $ela_pf->pessoa_id = $ela->id;
                $ela_pf->genero = 'F';
                $ela_pf->estado_civil_id = 804;
            }
            $ela_pf->dt_nascimento = $data->dn_ela;

            //$ela_pf->tm_camisa = '';
            $ela_pf->store(); // save the object

            PessoaContato::where('pessoa_id', '=', $ela->id)->where('tipo_contato_id', '=', 101)->delete();

            if (isset($data->ctt_ela) and !empty($data->ctt_ela)) {
                $contato_dela = new PessoaContato();
                $contato_dela->pessoa_id = $ela->id;
                $contato_dela->tipo_contato_id = 101;
                $contato_dela->contato = $data->ctt_ela;
                $contato_dela->status_contato_id = 1;
                $contato_dela->store();
            }

            //relacao deles

            if (isset($data->relacao_id) and !empty($data->relacao_id)) {
                $relacao_deles = PessoasRelacao::find($data->relacao_id);
            } else {
                $relacao_deles = new PessoasRelacao();
                $relacao_deles->tipo_vinculo = 'Sem documento de registro em cartório';
                $relacao_deles->status_relacao_id = 1;
            }
            $relacao_deles->dt_inicial = $data->dt_casamento;
            $relacao_deles->store(); // save the object

            if (!isset($data->relacao_id) or empty($data->relacao_id)) {
                //vinculo dele
                $ele_vinculo = new PessoaParentesco();
                $ele_vinculo->pessoa_id = $ele->id;
                $ele_vinculo->parentesco_id = 926;
                $ele_vinculo->pessoa_parente_id = $ela->id;
                $ele_vinculo->relacao_id = $relacao_deles->id;
                //obs_parentesco
                $ele_vinculo->store(); // save the object

                //vinculo dela
                $ela_vinculo = new PessoaParentesco();
                $ela_vinculo->pessoa_id = $ela->id;
                $ela_vinculo->parentesco_id = 925;
                $ela_vinculo->pessoa_parente_id = $ele->id;
                $ela_vinculo->relacao_id = $relacao_deles->id;
                //obs_parentesco
                $ela_vinculo->store(); // save the object
            }

            //montagem
            if (isset($data->relacao_id) and !empty($data->relacao_id)) {
                $montagem = Montagem::where('casal_id', '=', $relacao_deles->id)->where('tipo_id', '=', 1)->first();
                $encontrista = Encontrista::where('montagem_id', '=', $montagem->id)->first();
            } else {
                $montagem = new Montagem();
                $montagem->tipo_id = 1;
                $montagem->casal_id = $relacao_deles->id;

                //encontrista
                $encontrista = new Encontrista();
            }

            $montagem->encontro_id = $data->encontro_id;
            //conducao_propria_id
            $montagem->circulo_id = $data->circulo_id;
            $montagem->store(); // save the object

            //casal_convite_id
            $encontrista->montagem_id = $montagem->id;
            $encontrista->secretario_s_n = 2;
            $encontrista->store(); // save the object

            if (isset($data->relacao_id) and !empty($data->relacao_id)) {
                $historico_circulo = CirculoHistorico::where('casal_id', '=', $data->relacao_id)->orderBy('id', 'asc')->first();
            } else {
                $historico_circulo = new CirculoHistorico();
                $historico_circulo->dt_historico = $montagem->Encontro->dt_inicial;
                $relacao_id_deles = ViewCasal::where('ele_id', '=', $ele->id)->where('ela_id', '=', $ela->id)->first();
                $historico_circulo->casal_id = $relacao_id_deles->relacao_id;
            }

            $historico_circulo->user_sessao_id = TSession::getValue('userid');
            $historico_circulo->circulo_id = $data->circulo_id;
            $historico_circulo->motivo_id = 1101;
            $historico_circulo->obs_motivo = 'Montagem de participação como Encontrista';
            $historico_circulo->store(); // save the object

            // fill the form with the active record data
            $this->form->setData($relacao_deles);

            self::onClose();

            if ($data->refer_id == 2) {
                $posAction = new TDataGridAction(['EncontreiroDataGrid', 'onReload'],   ['register_state' => 'false']);
            } else {
                $posAction = new TDataGridAction(['EncontristaDataGrid', 'onReload'],   ['register_state' => 'false']);
            }
            TTransaction::close();  // close the transaction

            // shows the success message
            new TMessage('info', 'Registro Salvo!', $posAction);
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData($this->form->getData()); // keep form data
            TTransaction::rollback(); // undo all pending operations
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
