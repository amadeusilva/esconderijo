<?php

use Adianti\Widget\Form\TEntry;

trait ControlePessoas
{
    public function onSalvaParente(object $pessoaparente)
    {

        $relacaoexistente = PessoaParentesco::where('pessoa_id', '=', $pessoaparente->pessoa_id)->where('pessoa_parente_id', '=', $pessoaparente->pessoa_parente_id)->first();

        if (!$relacaoexistente) {
            $novoparente = new PessoaParentesco();
            $novoparente->pessoa_id = $pessoaparente->pessoa_id;

            if ($pessoaparente->parentesco_id == 903 or $pessoaparente->parentesco_id == 904) { // 904 - FILHA - F / 903 - FILHO - M // entra como meu filho ou filha
                $verificapaimae = PessoaParentesco::where('parentesco_id', '=', $pessoaparente->parentesco_id)->where('pessoa_parente_id', '=', $pessoaparente->pessoa_parente_id)->load();
                if ($verificapaimae) {
                    $tem_pai_mae = 0;
                    $genero_filho_filha = 'M';
                    foreach ($verificapaimae as $paimae) {
                        if ($novoparente->Pessoa->PessoaFisica->genero == $paimae->Pessoa->PessoaFisica->genero) {
                            $tem_pai_mae = 1;
                            $genero_filho_filha = $paimae->PessoaParente->PessoaFisica->genero;
                        }
                    }

                    if ($tem_pai_mae == 1) {

                        $novoparente->parentesco_id = $genero_filho_filha == 'M' ? 933 : 934; // 934 - ENTEADA - F / 933 - ENTEADO - M // saio como enteado ou enteada deles

                    } else {
                        $novoparente->parentesco_id = $pessoaparente->parentesco_id;
                    }
                } else {
                    $novoparente->parentesco_id = $pessoaparente->parentesco_id;
                }
            } else {
                $novoparente->parentesco_id = $pessoaparente->parentesco_id;
            }

            $novoparente->pessoa_parente_id = $pessoaparente->pessoa_parente_id;
            $novoparente->store();
            $this->onSalvaParenteInversoTeste($novoparente);
        }
    }

    public function onSalvaParenteInversoTeste(object $pessoaparente)
    {
        $pessoa = Pessoa::find($pessoaparente->pessoa_id);
        $generodapessoa = $pessoa->PessoaFIsica->genero;

        $novoparentesco_id = 0;

        if ($pessoaparente->parentesco_id == 901 or $pessoaparente->parentesco_id == 902) { // 902 - MÃE - F / 901 - PAI - M // entra como meu pai ou mae
            $novoparentesco_id = $generodapessoa == 'M' ? 903 : 904; // 904 - FILHA - F / 903 - FILHO - M // saio como filho / filha deles

        } else if ($pessoaparente->parentesco_id == 935 or $pessoaparente->parentesco_id == 936) { // 936 - MADRASTA - F / 935 - PADRASTO - M // entra como meu padrasto ou madrasta
            $novoparentesco_id = $generodapessoa == 'M' ? 933 : 934; // 934 - ENTEADA - F / 933 - ENTEADO - M // saio como enteado ou enteada deles

        } else if ($pessoaparente->parentesco_id == 903 or $pessoaparente->parentesco_id == 904) { // 904 - FILHA - F / 903 - FILHO - M // entra como meu filho ou filha
            $novoparentesco_id = $generodapessoa == 'M' ? 901 : 902; // 902 - MÃE - F / 901 - PAI - M // saio como pais deles

        } else if ($pessoaparente->parentesco_id == 933 or $pessoaparente->parentesco_id == 934) { // 934 - ENTEADA - F / 933 - ENTEADO - M // entra como meu enteado ou enteada
            $novoparentesco_id = $generodapessoa == 'M' ? 935 : 936; // 936 - MADRASTA - F / 935 - PADRASTO - M // saio como padrastou ou madrasta deles

        } else if ($pessoaparente->parentesco_id == 921 or $pessoaparente->parentesco_id == 922) { // 922 - ESPOSA - F / 921 - ESPOSO - M // entra como meu conjugue
            $novoparentesco_id = $generodapessoa == 'M' ? 921 : 922; // saio tambm como conjuge

        } else if ($pessoaparente->parentesco_id == 923 or $pessoaparente->parentesco_id == 924) { // 924 - COMPANHEIRA - F / 923 - COMPANHEIRO - M
            $novoparentesco_id = $generodapessoa == 'M' ? 923 : 924;
        } else if ($pessoaparente->parentesco_id == 925 or $pessoaparente->parentesco_id == 926) { // 926 - CONVIVENTE - F / 925 - CONVIVENTE - M
            $novoparentesco_id = $generodapessoa == 'M' ? 925 : 926;
        }

        PessoaParentesco::where('pessoa_id', '=', $pessoaparente->pessoa_parente_id)->where('parentesco_id', '=', $novoparentesco_id)->where('pessoa_parente_id', '=', $pessoaparente->pessoa_id)->delete();
        $novoparente = new PessoaParentesco();
        $novoparente->pessoa_id = $pessoaparente->pessoa_parente_id;
        $novoparente->parentesco_id = $novoparentesco_id;
        $novoparente->pessoa_parente_id = $pessoaparente->pessoa_id;
        $novoparente->store();
    }

    public function onMudaEstadoCivil(object $pessoaparente)
    {
        $buscaesps = PessoaFisica::where('pessoa_id', '=', $pessoaparente->pessoa_parente_id)->first();
        if ($buscaesps) {
            if ($pessoaparente->parentesco_id == 921) {
                $buscaesps->estado_civil_id = 807;
            } else if ($pessoaparente->parentesco_id == 922) {
                $buscaesps->estado_civil_id = 808;
            } else if ($pessoaparente->parentesco_id == 923) {
                $buscaesps->estado_civil_id = 805;
            } else if ($pessoaparente->parentesco_id == 924) {
                $buscaesps->estado_civil_id = 806;
            } else if ($pessoaparente->parentesco_id == 925) {
                $buscaesps->estado_civil_id = 803;
            } else if ($pessoaparente->parentesco_id == 926) {
                $buscaesps->estado_civil_id = 804;
            } else if ($pessoaparente->parentesco_id == 927) {
                $buscaesps->estado_civil_id = 811;
            } else if ($pessoaparente->parentesco_id == 928) {
                $buscaesps->estado_civil_id = 812;
            } else if ($pessoaparente->parentesco_id == 929) {
                $buscaesps->estado_civil_id = 809;
            } else if ($pessoaparente->parentesco_id == 930) {
                $buscaesps->estado_civil_id = 810;
            } else if ($pessoaparente->parentesco_id == 931) {
                $buscaesps->estado_civil_id = 809;
            } else if ($pessoaparente->parentesco_id == 932) {
                $buscaesps->estado_civil_id = 810;
            }
            $buscaesps->store();
        }
    }

    public static function onVinculo($param)
    {
        $tipo_vinculo = '';

        if ($param == 805 or $param == 806) {
            $tipo_vinculo = 'Declaração de União Estável';
            TQuickForm::showField('form_dados_relacao', 'doc_imagem');
        } else if ($param == 803 or $param == 804) {
            $tipo_vinculo = 'Sem documento de registro em cartório';
            TQuickForm::hideField('form_dados_relacao', 'doc_imagem');
        } else if ($param == 807 or $param == 808) {
            $tipo_vinculo = 'Certidão de Casamento';
            TQuickForm::showField('form_dados_relacao', 'doc_imagem');
        }

        return $tipo_vinculo;
    }

    public static function onCalculaTempo($param)
    {

        if (isset($param) and !empty($param)) {
            if (isset($param['dt_inicial']) and !empty($param['dt_inicial'])) {
                $novadata = DateTime::createFromFormat('d/m/Y', $param['dt_inicial']);
                $param['dt_inicial'] = $novadata->format('Y/m/d');
            } else {
                $novadata = DateTime::createFromFormat('d/m/Y', $param);
                $param = $novadata->format('Y/m/d');
            }

            $interval = $novadata->diff(new DateTime(date('Y-m-d')));
            $tempo_calculado = new stdClass;
            $tempo_calculado->tempo = $interval->format('%Y anos');
            $tempo_calculado->idade = $interval->format('%Y anos');

            //return $tempo_calculado;

            TForm::sendData('form_dados_relacao', $tempo_calculado);
            TForm::sendData('form_pf', $tempo_calculado);
            TForm::sendData('form_PessoaParente', $tempo_calculado);
        }
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
                    } else if (TSession::getValue('pessoa_painel')) {
                        $pessoa_painel = TSession::getValue('pessoa_painel');
                        if ($pessoa_painel->estado_civil_id != $param['estado_civil_id']) {


                            AdiantiCoreApplication::loadPage('DadosRelacao', 'onEdit', ['param' => $param]);
                            $param['estado_civil_id'] = '';
                            TForm::sendData('form_pf', $param);

                            //$pessoabanda = PessoaParentesco::where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->where('pessoa_id', '=', $pessoa_painel->id)->first();

                            //$param['id'] = $pessoabanda->id;

                            //AdiantiCoreApplication::loadPage('DadosRelacao', 'onVerRelacao', ['param' => $param]);

                            //TForm::sendData('form_pf', (array) $pessoa_painel->estado_civil_id);
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

    public function onDeletarelacao($param)
    {
        if (isset($param['deleterelacao']) and $param['deleterelacao'] == 1) {

            TSession::delValue('dados_relacao');
            TSession::setValue('dados_iniciais_pf', (array) $param['novoparam']);

            AdiantiCoreApplication::loadPage(__CLASS__, 'onEdit');
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
                    if (TSession::getValue('pessoa_painel')) {
                        $pessoa_painel = TSession::getValue('pessoa_painel');
                        if ($pessoa_painel->cpf == $param['cpf_cnpj']) {
                            TEntry::disableField('form_pf', 'cpf_cnpj');
                        } else {
                            $posAction = new TAction(array('DadosIniciaisPF', 'onEdit'));
                            // show the message dialog
                            new TMessage('error', 'CPF já cadastrado para: <b>' . $pessoaexistente->nome . '</b>', $posAction);
                        }
                    } else {
                        $posAction = new TAction(array('DadosIniciaisPF', 'onEdit'));
                        // show the message dialog
                        new TMessage('error', 'CPF já cadastrado para: <b>' . $pessoaexistente->nome . '</b>', $posAction);
                    }
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
                        throw new Exception('<b>Atenção!</b> Encontramos a pessoa: <b>' . $pf->nome . ' (' . $novadata->format('d/m/Y') . ')</b> REGISTRADO em outro CPF. Se acreditar que estes dados estão incorretos, entre em contato com o Administrador do sistema!');
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $pfvazia = new stdClass;
            $pfvazia->nome = '';
            $pfvazia->popular = '';
            $pfvazia->dt_nascimento = '';
            $pfvazia->genero = '';
            TForm::sendData('form_pf', $pfvazia);
        }
    }
}
