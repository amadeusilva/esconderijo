<?php

use Adianti\Widget\Form\TEntry;

trait ControlePessoas
{
    public function onSalvaParente(object $pessoaparente)
    {

        $relacaoexistente = PessoaParentesco::where('pessoa_id', '=', $pessoaparente->pessoa_id)->where('pessoa_parente_id', '=', $pessoaparente->pessoa_parente_id)->first();

        $novoparente = new PessoaParentesco();
        $novoparente->pessoa_id = $pessoaparente->pessoa_id;

        if ($pessoaparente->parentesco_id == 903 or $pessoaparente->parentesco_id == 904) { // 904 - FILHA - F / 903 - FILHO - M // entra como meu filho ou filha
            $verificapaimae = PessoaParentesco::where('parentesco_id', '=', $pessoaparente->parentesco_id)->where('pessoa_parente_id', '=', $pessoaparente->pessoa_parente_id)->load();
            if ($verificapaimae) {
                $tem_pai_mae = 0;
                $genero_filho_filha = 'M';
                foreach ($verificapaimae as $paimae) {
                    if ($novoparente->Pessoa->genero == $paimae->Pessoa->genero) {
                        $tem_pai_mae = 1;
                        $genero_filho_filha = $paimae->PessoaParente->genero;
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

        if (!$relacaoexistente) {
            $novoparente->pessoa_parente_id = $pessoaparente->pessoa_parente_id;
            $novoparente->store();
            $this->onSalvaParenteInversoTeste($novoparente);
        } else if ($pessoaparente->parentesco_id >= 927 and $pessoaparente->parentesco_id <= 932) {
            $relacaoexistente->parentesco_id = $novoparente->parentesco_id;
            $relacaoexistente->store();
            $this->onSalvaParenteInversoTeste($relacaoexistente);
        }
    }

    public function onSalvaParenteInversoTeste(object $pessoaparente)
    {

        $pessoa = ViewPessoaFisica::find($pessoaparente->pessoa_id);
        $generodapessoa = $pessoa->genero;

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
        } else if ($pessoaparente->parentesco_id == 927 or $pessoaparente->parentesco_id == 928) { // 927 - EX-ESPOSO - M / 928 - EX-ESPOSA - F
            $novoparentesco_id = $generodapessoa == 'M' ? 927 : 928;
        } else if ($pessoaparente->parentesco_id == 929 or $pessoaparente->parentesco_id == 930) { // 929 - EX-COMPANHEIRO - M / 930 - EX-COMPANHEIRA - F
            $novoparentesco_id = $generodapessoa == 'M' ? 929 : 930;
        } else if ($pessoaparente->parentesco_id == 931 or $pessoaparente->parentesco_id == 932) { // 931 - EX-CONVIVENTE - M / 932 - EX-CONVIVENTE - F
            $novoparentesco_id = $generodapessoa == 'M' ? 931 : 932;
        }

        $relacaoexistente = PessoaParentesco::where('pessoa_id', '=', $pessoaparente->pessoa_parente_id)->where('pessoa_parente_id', '=', $pessoaparente->pessoa_id)->first();

        if (!$relacaoexistente) {
            $novoparente = new PessoaParentesco();
            $novoparente->pessoa_id = $pessoaparente->pessoa_parente_id;
            $novoparente->parentesco_id = $novoparentesco_id;
            $novoparente->pessoa_parente_id = $pessoaparente->pessoa_id;
            $novoparente->store();
        } else if ($pessoaparente->parentesco_id >= 927 and $pessoaparente->parentesco_id <= 932) {
            $relacaoexistente->parentesco_id = $novoparentesco_id;
            $relacaoexistente->store();
        }
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
        } else if ($param == 803 or $param == 804 or $param == 809 or $param == 810) {
            $tipo_vinculo = 'Sem documento de registro em cartório';
            TQuickForm::hideField('form_dados_relacao', 'doc_imagem');
        } else if ($param == 807 or $param == 808) {
            $tipo_vinculo = 'Certidão de Casamento';
            TQuickForm::showField('form_dados_relacao', 'doc_imagem');
        } else if ($param == 811 or $param == 812) {
            $tipo_vinculo = '(Certidão de Divórcio)';
            TQuickForm::showField('form_dados_relacao', 'doc_imagem');
        } else if ($param == 813 or $param == 814) {
            $tipo_vinculo = '(Certidão de óbito do cônjuge)';
            TQuickForm::showField('form_dados_relacao', 'doc_imagem');
        }

        return $tipo_vinculo;
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
                        }
                    } else {
                        AdiantiCoreApplication::loadPage('DadosRelacao', 'onEdit', ['param' => $param]);
                        $param['estado_civil_id'] = '';
                        TForm::sendData('form_pf', $param);
                    }
                    // caso onde tem pessoa painel e tem dados de relação
                } else if ($param['estado_civil_id'] >= 809 and $param['estado_civil_id'] <= 814) {

                    if ($dados_relacao) {
                        if ($dados_relacao['estado_civil_id'] != $param['estado_civil_id']) {
                            AdiantiCoreApplication::loadPage('DadosRelacao', 'onEdit', ['param' => $param]);
                            $param['estado_civil_id'] = '';
                            TForm::sendData('form_pf', $param);
                        }
                    } else

                    if (TSession::getValue('pessoa_painel')) {
                        $pessoa_painel = TSession::getValue('pessoa_painel');
                        if ($pessoa_painel->estado_civil_id != $param['estado_civil_id']) {
                            AdiantiCoreApplication::loadPage('DadosRelacao', 'onEdit', ['param' => $param]);
                            $param['estado_civil_id'] = '';
                            TForm::sendData('form_pf', $param);
                        }
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

    public static function onDadosRelacao($param)
    {

        try {

            TTransaction::open('adea');   // open a transaction with database 'samples'

            $pessoabanda = PessoaParentesco::where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->where('pessoa_id', '=', $param)->first();

            $object = '';

            if ($pessoabanda) {

                $object = PessoasRelacao::where('relacao_id', '=', $pessoabanda->id)->first();        // instantiates object City
                $object->id_relacao = $pessoabanda->id;
                $object->estado_civil_id = $object->PessoaParentesco->Pessoa->estado_civil_id;
                $object->tipo_vinculo = self::onVinculo($object->estado_civil_id);
                $object->dt_inicial =  TDate::date2br($object->dt_inicial);
                if (!$object->dt_final or $object->dt_final = '0000-00-00') {
                    $object->dt_final = date('d/m/Y');
                }
                $object->tempo = self::onCalculaDieferencaTempo($object);
            }

            return $object;   // fill the form with the active record data

            TTransaction::close();           // close the transaction
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public static function onCalculaDieferencaTempo($object)
    {

        $object = (object) $object;

        if (isset($object) and !empty($object)) {

            if (!$object->dt_final or $object->dt_final = '0000-00-00') {
                $object->dt_final = date('d/m/Y');
            }

            $dt_inicial = DateTime::createFromFormat('d/m/Y', $object->dt_inicial);
            $dt_final = DateTime::createFromFormat('d/m/Y', $object->dt_final);

            $intervalo = $dt_inicial->diff($dt_final);

            $tempo_cauculado = new stdClass;
            $tempo_cauculado->dt_final = $object->dt_final;
            $tempo_cauculado->tempo = $intervalo->y . " ano(s), " . $intervalo->m . " mese(s) e " . $intervalo->d . " dia(s) ";

            TForm::sendData('form_dados_relacao', $tempo_cauculado);

            return $intervalo->y . " ano(s), " . $intervalo->m . " mese(s) e " . $intervalo->d . " dia(s) ";
        }
    }

    public static function onGeneroChange($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['genero'])) {

                $repo = new TRepository('ListaItens');
                $criteria = new TCriteria;
                if (isset($param['genero'])) {
                    $criteria->add(new TFilter('lista_id', '=',  17));
                    $criteria->add(new TFilter('obs', '=',  $param['genero']));
                    if (TSession::getValue('pessoa_painel')) {
                        $pessoa_painel = TSession::getValue('pessoa_painel');
                        //casados
                        if ($pessoa_painel->estado_civil_id == 807 or $pessoa_painel->estado_civil_id == 808) {
                            $criteria->add(new TFilter('id', '!=',  801));
                            $criteria->add(new TFilter('id', '!=',  802));
                            $criteria->add(new TFilter('id', '!=',  803));
                            $criteria->add(new TFilter('id', '!=',  804));
                            $criteria->add(new TFilter('id', '!=',  805));
                            $criteria->add(new TFilter('id', '!=',  806));
                            $criteria->add(new TFilter('id', '!=',  809));
                            $criteria->add(new TFilter('id', '!=',  810));
                            //convivente
                        } else if ($pessoa_painel->estado_civil_id >= 803 and $pessoa_painel->estado_civil_id <= 804) {
                            $criteria->add(new TFilter('id', '!=',  801));
                            $criteria->add(new TFilter('id', '!=',  802));
                            $criteria->add(new TFilter('id', '!=',  811));
                            $criteria->add(new TFilter('id', '!=',  812));
                            $criteria->add(new TFilter('id', '!=',  813));
                            $criteria->add(new TFilter('id', '!=',  814));
                            //uniao estável 
                        } else if ($pessoa_painel->estado_civil_id >= 805 and $pessoa_painel->estado_civil_id <= 806) {
                            $criteria->add(new TFilter('id', '!=',  801));
                            $criteria->add(new TFilter('id', '!=',  802));
                            $criteria->add(new TFilter('id', '!=',  803));
                            $criteria->add(new TFilter('id', '!=',  804));
                            $criteria->add(new TFilter('id', '!=',  811));
                            $criteria->add(new TFilter('id', '!=',  812));
                            $criteria->add(new TFilter('id', '!=',  813));
                            $criteria->add(new TFilter('id', '!=',  814));
                            //separados
                        } else if ($pessoa_painel->estado_civil_id >= 809 and $pessoa_painel->estado_civil_id <= 810) {
                            $criteria->add(new TFilter('id', '!=',  801));
                            $criteria->add(new TFilter('id', '!=',  802));
                            $criteria->add(new TFilter('id', '!=',  811));
                            $criteria->add(new TFilter('id', '!=',  812));
                            $criteria->add(new TFilter('id', '!=',  813));
                            $criteria->add(new TFilter('id', '!=',  814));
                            //Divorciado
                        } else if ($pessoa_painel->estado_civil_id >= 811 and $pessoa_painel->estado_civil_id <= 812) {
                            $criteria->add(new TFilter('id', '!=',  801));
                            $criteria->add(new TFilter('id', '!=',  802));
                            $criteria->add(new TFilter('id', '!=',  809));
                            $criteria->add(new TFilter('id', '!=',  810));
                            $criteria->add(new TFilter('id', '!=',  813));
                            $criteria->add(new TFilter('id', '!=',  814));
                            //viuvo
                        } else if ($pessoa_painel->estado_civil_id >= 813 and $pessoa_painel->estado_civil_id <= 814) {
                            $criteria->add(new TFilter('id', '!=',  801));
                            $criteria->add(new TFilter('id', '!=',  802));
                            $criteria->add(new TFilter('id', '!=',  809));
                            $criteria->add(new TFilter('id', '!=',  810));
                            $criteria->add(new TFilter('id', '!=',  811));
                            $criteria->add(new TFilter('id', '!=',  812));
                        } else {
                            $criteria->add(new TFilter('id', '!=',  814));
                            $criteria->add(new TFilter('id', '!=',  813));
                            $criteria->add(new TFilter('id', '!=',  812));
                            $criteria->add(new TFilter('id', '!=',  811));
                            $criteria->add(new TFilter('id', '!=',  810));
                            $criteria->add(new TFilter('id', '!=',  809));
                        }
                    } else {
                        $criteria->add(new TFilter('id', '!=',  814));
                        $criteria->add(new TFilter('id', '!=',  813));
                        $criteria->add(new TFilter('id', '!=',  812));
                        $criteria->add(new TFilter('id', '!=',  811));
                        $criteria->add(new TFilter('id', '!=',  810));
                        $criteria->add(new TFilter('id', '!=',  809));
                    }
                }

                $itens = $repo->load($criteria);

                $options = array();
                $options[0] = '';
                foreach ($itens as $item) {
                    $options[$item->id] = $item->item . ' ' . $item->abrev;
                }

                TCombo::reload('form_pf', 'estado_civil_id', $options);
                //TCombo::reload('form_dynamic_filter', 'customers', $options);
                //TDBCombo::reloadFromModel('form_pf', 'estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id', $options, TRUE);

                /*
                $criteria = TCriteria::create(['lista_id' => 17, 'abrev' => 'GP', 'obs' => $param['genero']]);

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_pf', 'estado_civil_id', 'adea', 'ListaItens', 'id', 'item', 'id', $criteria, TRUE);
                */
                TButton::enableField('form_pf', 'avançar');
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

    public static function CPFCadastrado($param)
    {

        if (isset($param) and !empty($param)) {
            $pessoaexistente = Pessoa::where('cpf_cnpj', '=', $param)->first();
            if ($pessoaexistente) {
                return $pessoaexistente;
            } else {
                return false;
            }
        }
    }

    public static function onConsultaCPF($param)
    {
        try {
            TTransaction::open('adea');

            if (isset($param['cpf_cnpj']) and !empty($param['cpf_cnpj'])) {
                $cpfsimnao = self::CPFCadastrado($param['cpf_cnpj']);
                if ($cpfsimnao) {
                    if (TSession::getValue('pessoa_painel')) {
                        $pessoa_painel = TSession::getValue('pessoa_painel');
                        if ($pessoa_painel->cpf == $param['cpf_cnpj']) {
                            TEntry::disableField('form_pf', 'cpf_cnpj');
                        } else {
                            $posAction = new TAction(array('DadosIniciaisPF', 'onEdit'));
                            // show the message dialog
                            new TMessage('error', 'CPF já cadastrado para: <b>' . $cpfsimnao->nome . '</b>', $posAction);
                        }
                    } else {
                        $posAction = new TAction(array('DadosIniciaisPF', 'onEdit'));
                        // show the message dialog
                        new TMessage('error', 'CPF já cadastrado para: <b>' . $cpfsimnao->nome . '</b>', $posAction);
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
                    if ($pf->id != $param['id']) {
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
            //$pfvazia->id = '';
            $pfvazia->nome = '';
            $pfvazia->popular = '';
            $pfvazia->dt_nascimento = '';
            $pfvazia->idade = '';
            $pfvazia->genero = '';
            TForm::sendData('form_pf', $pfvazia);
        }
    }
}
