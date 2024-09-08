<?php

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
class DadosEndereco extends TPage
{
    use ControleEndereco;
    use ControlePessoas;
    protected $form; // form

    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;

    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();

        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');

        // creates the form
        $this->form = new BootstrapFormBuilder('form_endereco');
        $this->form->setFormTitle('Endereço de <b>' . $dadosiniciaispf['popular'] . '</b>');
        $this->form->setClientValidation(true);

        //dados de endereço
        $cep  = new TDBEntry('cep', 'adea', 'Endereco', 'cep');
        $cep->setMask('99.999-999');

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
        $ponto_referencia  = new TDBEntry('ponto_referencia', 'adea', 'ENDERECO', 'ponto_referencia');
        $ponto_referencia->placeholder = 'PRÓXIMO A PRAÇAS, HOSPITAIS, EMPRESAS...';
        $ponto_referencia->forceUpperCase();

        // define some properties for the form fields
        $cep->setSize('100%');
        $estado_id->setSize('100%');
        $cidade_id->setSize('100%');
        $tipo_id->setSize('100%');
        $logradouro_id->setSize('100%');
        $bairro_id->setSize('100%');
        $n->setSize('100%');
        $ponto_referencia->setSize('100%');

        //$action = new TAction(array($this, 'onCEPAction'));

        //$action->setParameter('cep', $cep);
        //$b2 = new TActionLink('completar', $action, 'white', 10, '', 'far:check-square #FEFF00');
        //$b2->class = 'btn btn-success';

        //$this->form->addAction('Completar Endereço', new TAction(array($this, 'onCompletaCEPclick')), 'far:check-square green');

        $cep->setExitAction(new TAction(array($this, 'onCEPAction')));

        //$cep->setExitAction(new TAction(array($this, 'onCompletaCEPclick')));

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

        // validations
        $estado_id->addValidation('Estado', new TRequiredValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $tipo_id->addValidation('Tipo', new TRequiredValidator);
        $logradouro_id->addValidation('Logradouro', new TRequiredValidator);
        $bairro_id->addValidation('Bairro', new TRequiredValidator);
        $n->addValidation('Nº', new TRequiredValidator);

        // add a form action
        $this->form->addAction('Voltar', new TAction(array($this, 'onVolta')), 'far:arrow-alt-circle-left red');
        $this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addAction('Salvar', new TAction(array($this, 'onSalvar')), 'far:check-circle green');

        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'PessoaFisicaDataGrid'));
        $vbox->add($this->form);
        parent::add($vbox);
    }

    public function onEdit()
    {

        $enderecopessoa = TSession::getValue('endereco_pessoa');

        if ($enderecopessoa) {
            TForm::sendData('form_endereco', $enderecopessoa);
        } else if (TSession::getValue('pessoa_painel')) {
            $pessoa_painel = TSession::getValue('pessoa_painel');
            try {

                TTransaction::open('adea');
                $object = Endereco::where('id', '=', $pessoa_painel->endereco_id)->first();        // instantiates object City

                if (isset($object)) {
                    $this->form->setData($object);   // fill the form with the active record data

                    // force fire events
                    $data = new stdClass;
                    $data->estado_id            = $object->Bairro->Cidade->Estado->id;
                    $data->cidade_id            = $object->Bairro->Cidade->id;
                    $data->tipo_id              = $object->Logradouro->Tipo->id;
                    $data->logradouro_id        = $object->logradouro_id;
                    $data->bairro_id            = $object->bairro_id;
                    //TDBCombo::disableField('form_endereco', 'tipo_id');
                    TForm::sendData('form_endereco', $data);
                } else {
                    $this->form->clear(true);
                }

                TTransaction::close();
            } catch (Exception $e) // in case of exception
            {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        }
    }

    public function onVolta()
    {
        try {
            $this->form->validate();
            $data = $this->form->getData();
            TSession::setValue('endereco_pessoa', (array) $data);

            AdiantiCoreApplication::loadPage('DadosParentes', 'onLoad');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onAvanca()
    {
        try {
            $this->form->validate();
            $data = $this->form->getData();
            TSession::setValue('endereco_pessoa', (array) $data);

            AdiantiCoreApplication::loadPage('DadosRelacao', 'onEdit');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * confirmation screen
     */
    public function onSalvar()
    {

        try {
            // open a transaction with database 'samples'
            TTransaction::open('adea');

            $this->form->validate(); // run form validation
            $data = $this->form->getData(); // get form data as array
            TSession::setValue('endereco_pessoa', (array) $data);

            $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');
            $dadosparentespf = TSession::getValue('dados_parentes_pf');
            $enderecopessoa = TSession::getValue('endereco_pessoa');
            $dadosrelacao = TSession::getValue('dados_relacao');


            /*
            echo '<pre>';
            print_r($dadosiniciaispf);
            print_r($dadosparentespf);
            print_r($enderecopessoa);
            print_r($dadosrelacao);
            echo '</pre>';
            exit;
            */


            //converte a data static BR para Americana
            $novadata = DateTime::createFromFormat('d/m/Y', $dadosiniciaispf['dt_nascimento']);
            $dadosiniciaispf['dt_nascimento'] = $novadata->format('Y/m/d');
            $dadosiniciaispf['tipo_pessoa'] = 1;
            $dadosiniciaispf['status_pessoa'] = 21;
            $dadosiniciaispf['ck_pessoa'] = 2;

            if (isset($enderecopessoa['logradouro_id'])) {
                $consultaendereco = Endereco::where('logradouro_id', '=', $enderecopessoa['logradouro_id'])->where('n', '=', $enderecopessoa['n'])->where('bairro_id', '=', $enderecopessoa['bairro_id'])->first();
                if ($consultaendereco) {
                    $dadosiniciaispf['endereco_id'] = $consultaendereco->id;
                } else {
                    $endereco = new Endereco();
                    $endereco->fromArray($enderecopessoa);
                    if (!$enderecopessoa['ponto_referencia']) {
                        $endereco->ponto_referencia = 'SEM REFERÊNCIA';
                    }
                    $endereco->store();
                    $dadosiniciaispf['endereco_id'] = $endereco->id;
                }
            }

            $pessoa = new Pessoa();
            $pessoa->fromArray($dadosiniciaispf);
            $pessoa->store();

            PessoaFisica::where('pessoa_id', '=', $pessoa->id)->delete();
            $pessoafisica = new PessoaFisica();
            $pessoafisica->pessoa_id = $pessoa->id;
            $pessoafisica->fromArray($dadosiniciaispf);
            $pessoafisica->store();

            PessoaContato::where('pessoa_id', '=', $pessoa->id)->delete();

            if (isset($dadosiniciaispf['fone']) and !empty($dadosiniciaispf['fone'])) {
                $pessoacontatofone = new PessoaContato();
                $pessoacontatofone->pessoa_id = $pessoa->id;
                $pessoacontatofone->tipo_contato_id = 101;
                $pessoacontatofone->contato = $dadosiniciaispf['fone'];
                $pessoacontatofone->status_contato_id = 1;
                $pessoacontatofone->store();
            }
            if (isset($dadosiniciaispf['email']) and !empty($dadosiniciaispf['email'])) {
                $pessoacontatoemail = new PessoaContato();
                $pessoacontatoemail->pessoa_id = $pessoa->id;
                $pessoacontatoemail->tipo_contato_id = 102;
                $pessoacontatoemail->contato = $dadosiniciaispf['email'];
                $pessoacontatoemail->status_contato_id = 1;
                $pessoacontatoemail->store();
            }

            //não deletar relações de parentesco
            //PessoaParentesco::where('pessoa_id', '=', $pessoa->id)->delete();
            //PessoaParentesco::where('pessoa_parente_id', '=', $pessoa->id)->delete();

            if (isset($dadosparentespf) and !empty($dadosparentespf)) {

                foreach ($dadosparentespf as $key => $parente) {

                    $parente->pessoa_id = $pessoa->id; // passando a pessoa id para o objeto que salvara pessoa parente
                    // parentesco ja eta nno parente

                    if ($parente->id) {
                        $buscapessoaparente = Pessoa::where('id', '=', $parente->id)->first();
                    } else {
                        $buscapessoaparente = Pessoa::where('cpf_cnpj', '=', $key)->first();
                    }

                    if ($buscapessoaparente) {

                        $pessoanova = $buscapessoaparente;

                        //if ($parente->moracomigo == 's') {
                        //    $buscapessoaparente->endereco_id = $dadosiniciaispf['endereco_id'];
                        //    $buscapessoaparente->store();
                        //}

                        //$parente->pessoa_parente_id = $buscapessoaparente->id; // passando a pessoa parente id para o objeto que salvara pessoa parente
                    } else {
                        $pessoanova = new Pessoa();
                    }

                    $pessoanova->tipo_pessoa = 1;
                    $pessoanova->cpf_cnpj = $parente->cpf;
                    $pessoanova->nome = $parente->nome;
                    $pessoanova->popular = $parente->popular;
                    if ($parente->moracomigo == 's') {
                        $pessoanova->endereco_id = $dadosiniciaispf['endereco_id'];
                    }
                    $pessoanova->status_pessoa = 21;
                    $pessoanova->ck_pessoa = 2;
                    $pessoanova->store();

                    $pessoafisicanova = PessoaFisica::where('pessoa_id', '=', $pessoanova->id)->first();

                    if (!$pessoafisicanova) {
                        $pessoafisicanova = new PessoaFisica();
                    } else {
                    }

                    //$pessoafisicanova = new PessoaFisica($pessoanova->id);
                    $pessoafisicanova->pessoa_id = $pessoanova->id;
                    $pessoafisicanova->genero = $parente->genero;

                    $nova_dt_nascimento = DateTime::createFromFormat('d/m/Y', $parente->dt_nascimento);
                    $parente->dt_nascimento = $nova_dt_nascimento->format('Y/m/d');

                    $pessoafisicanova->dt_nascimento = $parente->dt_nascimento;

                    if (!$pessoafisicanova->estado_civil_id) {
                        if ($pessoafisicanova->genero == 'M') {
                            $pessoafisicanova->estado_civil_id = 801;
                        } else {
                            $pessoafisicanova->estado_civil_id = 802;
                        }
                    }

                    $pessoafisicanova->store();

                    $parente->pessoa_parente_id = $pessoanova->id; // passando a pessoa parente id para o objeto que salvara pessoa parente

                    //Salvando Parentesco
                    $this->onSalvaParente($parente);
                }
            }

            //estado civil: 803-804: convivent / 805-806: ue / 807-808: casad
            //parentesco: 921-922: espos / 923-924: companheir / 925-926: convivente

            if (isset($dadosrelacao['estado_civil_id']) and !empty($dadosrelacao['estado_civil_id'])) {

                if ($dadosrelacao['estado_civil_id'] >= 803 and $dadosrelacao['estado_civil_id'] <= 808) {

                    $buscarelacao1 = PessoaParentesco::where('pessoa_id', '=', $pessoa->id)->where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->first();
                    $buscarelacao2 = PessoaParentesco::where('pessoa_id', '=', $buscarelacao1->pessoa_parente_id)->where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->first();

                    $this->onSalvaFilhosFilhasPaiMae($buscarelacao1);

                    $this->onMudaEstadoCivil($buscarelacao1);

                    PessoasRelacao::where('id', '=', $buscarelacao1->relacao_id)->delete();
                    PessoasRelacao::where('id', '=', $buscarelacao2->relacao_id)->delete();

                    $pessoarelacao = new PessoasRelacao();
                    $novadatanapr = DateTime::createFromFormat('d/m/Y', $dadosrelacao['dt_inicial']);
                    $dadosrelacao['dt_inicial'] = $novadatanapr->format('Y/m/d');
                    $pessoarelacao->dt_inicial = $dadosrelacao['dt_inicial'];
                    if (!empty($dadosrelacao['doc_imagem'])) {
                        $pessoarelacao->doc_imagem = $dadosrelacao['doc_imagem'];
                    }
                    $pessoarelacao->estado_civil_id = $dadosrelacao['estado_civil_id'];
                    $pessoarelacao->tipo_vinculo = $dadosrelacao['tipo_vinculo'];
                    $pessoarelacao->status_relacao_id = 1;
                    $pessoarelacao->store();

                    $buscarelacao1->relacao_id = $pessoarelacao->id;
                    $buscarelacao1->store();
                    $buscarelacao2->relacao_id = $pessoarelacao->id;
                    $buscarelacao2->store();

                    // copy file to target folder
                    if (!empty($dadosrelacao['doc_imagem'])) {
                        $this->saveFile($pessoarelacao, (object) $dadosrelacao, 'doc_imagem', 'app/images/dadosderelacao');
                    }
                } else if ($dadosrelacao['estado_civil_id'] >= 809 and $dadosrelacao['estado_civil_id'] <= 814) {

                    $buscarelacao3 = PessoaParentesco::where('pessoa_id', '=', $pessoa->id)->where('parentesco_id', '>=', 927)->where('parentesco_id', '<=', 932)->orderBy('id', 'desc')->first();
                    //$buscarelacao4 = PessoaParentesco::where('pessoa_id', '=', $buscarelacao3->pessoa_parente_id)->where('parentesco_id', '>=', 927)->where('parentesco_id', '<=', 932)->orderBy('id', 'desc')->first();

                    $this->onMudaEstadoCivil($buscarelacao3);

                    if ($dadosrelacao['estado_civil_id'] == 813 or $dadosrelacao['estado_civil_id'] == 814) {

                        $falecido = new Pessoa($buscarelacao3->pessoa_parente_id);
                        $falecido->status_pessoa = 29; // falecido(a)
                        $falecido->store();
                    }

                    $novadatanapr = DateTime::createFromFormat('d/m/Y', $dadosrelacao['dt_inicial']);
                    $dadosrelacao['dt_inicial'] = $novadatanapr->format('Y-m-d');

                    //relação ja foi atualizada

                    $pessoarelacao = new PessoasRelacao($buscarelacao3->relacao_id);

                    $pessoarelacao->dt_final = $dadosrelacao['dt_inicial'];
                    if (!empty($dadosrelacao['doc_imagem'])) {
                        $pessoarelacao->doc_imagem = $dadosrelacao['doc_imagem'];
                    }
                    $pessoarelacao->estado_civil_id = $dadosrelacao['estado_civil_id'];
                    $pessoarelacao->tipo_vinculo = $dadosrelacao['tipo_vinculo'];
                    $pessoarelacao->status_relacao_id = 10;

                    $pessoarelacao->store();

                    // copy file to target folder
                    if (!empty($dadosrelacao['doc_imagem'])) {
                        $this->saveFile($pessoarelacao, (object) $dadosrelacao, 'doc_imagem', 'app/images/dadosderelacao');
                    }

                    /*
                    $pessoarelacao2 = PessoasRelacao::where('relacao_id', '=', $buscarelacao4->id)->first();
                    $pessoarelacao2->dt_final = $pessoarelacao->dt_final;
                    if (!empty($pessoarelacao->doc_imagem)) {
                        $pessoarelacao2->doc_imagem = $pessoarelacao->doc_imagem;
                    }
                    $pessoarelacao2->status_relacao_id = 2;
                    $pessoarelacao2->store();
                    */
                }
            }

            TTransaction::close();  // close the transaction

            // fill the form with the active record data
            $posAction = new TAction(array('PessoaFisicaDataGrid', 'onReload'));

            // show the message dialog
            new TMessage('info', 'Pessoa Salva com Sucesso!', $posAction);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onSalvaFilhosFilhasPaiMae($param)
    {

        $pegarfilhos1 = PessoaParentesco::where('pessoa_id', '=', $param->pessoa_id)->where('parentesco_id', '>=', 903)->where('parentesco_id', '<=', 904)->load();
        if ($pegarfilhos1) {
            foreach ($pegarfilhos1 as $filhos1) {
                $filhos1->pessoa_id = $param->pessoa_parente_id;
                //Salvando Parentesco
                $this->onSalvaParente($filhos1);
            }
        }
        $pegarfilhos2 = PessoaParentesco::where('pessoa_id', '=', $param->pessoa_parente_id)->where('parentesco_id', '>=', 903)->where('parentesco_id', '<=', 904)->load();
        if ($pegarfilhos2) {
            foreach ($pegarfilhos2 as $filhos2) {
                $filhos2->pessoa_id = $param->pessoa_id;
                //Salvando Parentesco
                $this->onSalvaParente($filhos2);
            }
        }
    }
}
