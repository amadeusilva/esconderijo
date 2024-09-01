<?php

use Adianti\Widget\Form\TEntry;

trait ControleEndereco
{

    public static function onCEPAction($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['cep'])) {
                $buscacep = Endereco::where('cep', '=', $param['cep'])->first();
                if ($buscacep) {
                    $ceptela = new stdClass;
                    $ceptela->estado_id      = $buscacep->Bairro->Cidade->Estado->id;
                    $ceptela->cidade_id      = $buscacep->Bairro->Cidade->id;
                    $ceptela->tipo_id        = $buscacep->Logradouro->Tipo->id;
                    $ceptela->logradouro_id  = $buscacep->logradouro_id;
                    $ceptela->bairro_id      = $buscacep->bairro_id;
                    $ceptela->ponto_referencia      = $buscacep->ponto_referencia;
                    // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                    TForm::sendData('form_endereco', $ceptela);
                    TForm::sendData('form_pessoa', $ceptela);
                    TForm::sendData('form_AddEncontrista', $ceptela);
                }
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onStateChange($param)
    {
        try {
            TTransaction::open('adea');
            if (!empty($param['estado_id'])) {
                $criteria = TCriteria::create(['estado_id' => $param['estado_id']]);

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_endereco', 'cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $criteria, TRUE);
                TDBCombo::reloadFromModel('form_pessoa', 'cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $criteria, TRUE);
                TDBCombo::reloadFromModel('form_AddEncontrista', 'cidade_id', 'adea', 'Cidade', 'id', 'cidade', 'cidade', $criteria, TRUE);

            } else {
                TCombo::clearField('form_endereco', 'cidade_id');
                TCombo::clearField('form_pessoa', 'cidade_id');
                TCombo::clearField('form_AddEncontrista', 'cidade_id');
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
                TDBCombo::reloadFromModel('form_endereco', 'bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $criteria, TRUE);
                TDBCombo::enableField('form_endereco', 'tipo_id');
                TDBCombo::reloadFromModel('form_pessoa', 'bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $criteria, TRUE);
                TDBCombo::enableField('form_pessoa', 'tipo_id');
                TDBCombo::reloadFromModel('form_AddEncontrista', 'bairro_id', 'adea', 'Bairro', 'id', 'bairro', 'bairro', $criteria, TRUE);
                TDBCombo::enableField('form_AddEncontrista', 'tipo_id');
            } else {
                TCombo::clearField('form_endereco', 'bairro_id');
                TCombo::clearField('form_pessoa', 'bairro_id');
                TCombo::clearField('form_AddEncontrista', 'bairro_id');
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
                TCombo::reload('form_endereco', 'logradouro_id', $logradourosrray);
                TCombo::reload('form_pessoa', 'logradouro_id', $logradourosrray);
                TCombo::reload('form_AddEncontrista', 'logradouro_id', $logradourosrray);
                TTransaction::close();
            } else {
                TCombo::clearField('form_endereco', 'logradouro_id');
                TCombo::clearField('form_pessoa', 'logradouro_id');
                TCombo::clearField('form_AddEncontrista', 'logradouro_id');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onCompletaCEPclick($data)
    {
        if (isset($data['cep']) and !empty($data['cep'])) {
            TButton::enableField('form_endereco', 'completar');
        } else {
            TButton::disableField('form_endereco', 'completar');
        }
    }
}
