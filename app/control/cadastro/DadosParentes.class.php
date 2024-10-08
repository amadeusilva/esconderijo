<?php

use Adianti\Validator\TCPFValidator;
use Adianti\Validator\TEmailValidator;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
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
class DadosParentes extends TPage
{

    use ControlePessoas;
    protected $detail_list;


    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;
    //use Adianti\Base\AdiantiStandardListTrait;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();

        $this->setDatabase('adea'); // defines the database
        $this->setActiveRecord('PessoaParentesco'); // defines the active record

        // creates the form
        $this->form = new BootstrapFormBuilder('form_parente');
        $this->form->setFormTitle('Parentescos');

        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->style = 'width:100%';
        $this->detail_list->disableDefaultClick();
        //$this->detail_list->setMutationAction(new TAction([$this, 'onRodapePainel']));

        $col_id  = new TDataGridColumn('id', 'Cod.', 'center');
        $col_parentesco_id  = new TDataGridColumn('parentesco_id', 'Grau', 'left');
        $col_cpf            = new TDataGridColumn('cpf', 'CPF', 'center');
        $col_nome           = new TDataGridColumn('popular', 'Nome', 'left');
        $col_genero         = new TDataGridColumn('genero', 'Gênero', 'center');
        $col_dt_nascimento  = new TDataGridColumn('dt_nascimento', 'Nascimento', 'center');
        $col_moracomigo       = new TDataGridColumn('moracomigo', 'Mora comigo?', 'center');

        $this->detail_list->addColumn($col_id);
        $this->detail_list->addColumn($col_parentesco_id);
        $this->detail_list->addColumn($col_cpf);
        $this->detail_list->addColumn($col_nome);
        $this->detail_list->addColumn($col_genero);
        $this->detail_list->addColumn($col_dt_nascimento);
        $this->detail_list->addColumn($col_moracomigo);

        $col_parentesco_id->setTransformer(function ($value) {
            try {
                TTransaction::open('adea');
                $grau = ListaItens::where('id', '=', $value)->first();
                return $value = $grau->item;
                TTransaction::close();
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage());
            }
        });

        $col_cpf->setTransformer(function ($value) {
            $parte_string = substr($value, 0, 5);
            if ($parte_string == 'CPFde') {
                $div = new TElement('span');
                $div->class = "label label-danger";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add('Atualize o CPF !');
                return $div;
            } else {
                return $value;
            }
        });

        $col_moracomigo->setTransformer(function ($value) {
            if ($value == 's') {
                $div = new TElement('span');
                $div->class = "label label-success";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add('Sim');
                return $div;
            } else {
                $div = new TElement('span');
                $div->class = "label label-danger";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add('Não');
                return $div;
            }
        });

        $col_genero->setTransformer(function ($value) {
            return $value == 'F' ? 'Feminino' : 'Masculino';
        });

        /*
        $col_dt_nascimento->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        */

        // creates two datagrid actions
        $action1 = new TDataGridAction(['AddParente', 'onEditParente'], ['id' => '{id}', 'parentesco_id' => '{parentesco_id}', 'moracomigo' => '{moracomigo}', 'atualizacao' => '{atualizacao}', 'vinculo' => 3]);
        $action1->setDisplayCondition(array($this, 'displayColumnEd'));
        $action2 = new TDataGridAction([$this, 'onDelete'], ['cpf' => '{cpf}']);
        $action2->setDisplayCondition(array($this, 'displayColumnDel'));

        // add the actions to the datagrid
        $this->detail_list->addAction($action1, 'Editar', 'far:edit blue');
        $this->detail_list->addAction($action2, 'Deletar', 'far:trash-alt red');

        // create the datagrid model
        $this->detail_list->createModel();

        $panel = new TPanelGroup('Pessoas Vinculadas', '#f5f5f5');
        $panel->add($this->detail_list);
        $panel->addHeaderActionLink('Vincular',  new TAction(['AddParente', 'onEditParente'], ['vinculo' => 1, 'register_state' => 'false']), 'fa:plus green');
        $panel->getBody()->style = 'overflow-x:auto';

        $textorodape = self::onRodapePainel();
        //$label = new TLabel($textorodape, '#62a8ee', 12, 'b');
        //$label->style = 'text-align:left;border-bottom:1px solid #62a8ee;width:100%';
        $panel->addFooter($textorodape);

        $this->form->addContent([$panel]);

        // add a form action
        $this->form->addAction('Voltar', new TAction(array($this, 'onVolta')), 'far:arrow-alt-circle-left red');
        $this->form->addAction('Avançar', new TAction(array($this, 'onAvanca')), 'far:check-circle green');

        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'PessoaFisicaDataGrid'));
        $vbox->add($this->form);
        parent::add($vbox);
    }

    /**
     * Define when the action can be displayed
     */
    public function displayColumnEd($object)
    {
        if ($object->vinculo == 3) {
            return TRUE;
        }
        return FALSE;
    }

    public function displayColumnDel($object)
    {
        if ($object->vinculo == 1) {
            return TRUE;
        }
        return FALSE;
    }

    public static function onRodapePainel()
    {

        try {

            $textorodape = '';

            $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');
            $dadosparentespf = TSession::getValue('dados_parentes_pf');
            $dadosrelacao = TSession::getValue('dados_relacao');

            if ($dadosiniciaispf) {

                TTransaction::open('adea');

                //Amadeus & Elane - 16 anos de casados - 2 filhos
                //Pessoa: Amadeus | Casado com Elane | 1 filho

                $pessoa = 'Dados Relacionais: <b>' . $dadosiniciaispf['popular'] . '</b>';
                $banda_ele_ela = '';
                $tempo_relacao = ''; //$dadosrelacao['tempo'];
                $n_filhos = 0;

                if ($dadosparentespf) {
                    foreach ($dadosparentespf as $parente) {
                        if ($parente->parentesco_id >= 921 and $parente->parentesco_id <= 926) {
                            $banda_ele_ela = ' com <b>' . $parente->popular . '</b>';
                        } else if ($parente->parentesco_id >= 903 and $parente->parentesco_id <= 904) {
                            $n_filhos += 1;
                        } else if ($parente->parentesco_id >= 933 and $parente->parentesco_id <= 934) {
                            $n_filhos += 1;
                        }
                    }
                }

                $texto_filhos = $n_filhos > 1 ? ', <b>' . $n_filhos . '</b> Filhos' : ', <b>' . $n_filhos . '</b> Filho(a)';

                if ($dadosrelacao) {
                    $tempo_relacao = 'há <b>' . $dadosrelacao['tempo'] . '</b>';
                }

                $buscaestadocivil = ListaItens::where('id', '=', $dadosiniciaispf['estado_civil_id'])->first()->item;

                $textorodape = $pessoa . ', <b>' . $buscaestadocivil . '</b> ' . $tempo_relacao . $banda_ele_ela . $texto_filhos . '.';

                TTransaction::close();  // close the transaction
            }

            return $textorodape;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onEdit()
    {
        $dadosparentespf = TSession::getValue('dados_parentes_pf');

        if ($dadosparentespf) {
            TForm::sendData('form_parente', $dadosparentespf);
        }
    }

    public function onVolta()
    {
        try {
            if (TSession::getValue('dados_parentes_pf')) {
                $data = TSession::getValue('dados_parentes_pf');
                TSession::setValue('dados_parentes_pf', (array) $data);
            }

            AdiantiCoreApplication::loadPage('DadosIniciaisPF', 'onEdit');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * confirmation screen
     */
    public function onAvanca()
    {
        try {
            $dados_iniciais_pf = TSession::getValue('dados_iniciais_pf');
            $data = TSession::getValue('dados_parentes_pf');

            //inicio do bloco pra ver se tem alguem pra add cpf

            $atualiza_parente = 0;
            $id = 0;
            $parentesco_id = 0;
            $moracomigo = 0;
            $atualizacao = 0;
            $vinculo = 0;

            if ($data) {

                foreach ($data as $d) {

                    $parte_string = substr($d->cpf, 0, 5);

                    if ($parte_string == 'CPFde') {
                        $atualiza_parente = 1;
                        $id = $d->id;
                        $parentesco_id = $d->parentesco_id;
                        $moracomigo = $d->moracomigo;
                        $atualizacao = $d->atualizacao;
                        $vinculo = 3;
                    }
                }
            }

            if ($atualiza_parente != 0) {
                $this->form->add(new TAlert('danger', '<b>Atenção!</b>. Você precisa revisar <b>Dados de Pessoas Vinculadas</b> !'));

                AdiantiCoreApplication::loadPage('AddParente', 'onEditParente', [
                    'id' => $id,
                    'parentesco_id' => $parentesco_id,
                    'moracomigo' => $moracomigo,
                    'atualizacao' => $atualizacao,
                    'vinculo' => $vinculo
                ]);
            } else

                //fim do bloco pra ver se tem alguem pra add cpf

                if ($dados_iniciais_pf) {
                    $tem_esposa_esposo = 0;
                    $tem_companheira_companheiro = 0;
                    $tem_convivente = 0;
                    if ($data) {
                        foreach ($data as $d) {
                            if ($d->parentesco_id == 921 or $d->parentesco_id == 922) {
                                $tem_esposa_esposo = 1;
                            }
                            if ($d->parentesco_id == 923 or $d->parentesco_id == 924) {
                                $tem_companheira_companheiro = 1;
                            }
                            if ($d->parentesco_id == 925 or $d->parentesco_id == 926) {
                                $tem_convivente = 1;
                            }
                        }
                    }

                    if ($dados_iniciais_pf['estado_civil_id'] == 803 or $dados_iniciais_pf['estado_civil_id'] == 804) {
                        if ($tem_convivente == 0) {
                            if ($dados_iniciais_pf['genero'] == 'M') {
                                $this->form->add(new TAlert('danger', 'Estado Civil: <b>Convivente</b>. Você precisa vincular uma <b>Convivente</b>!'));
                            } else {
                                $this->form->add(new TAlert('danger', 'Estado Civil: <b>Convivente</b>. Você precisa vincular um <b>Convivente</b>!'));
                            }
                            AdiantiCoreApplication::loadPage('AddParente', 'onLoad', ['vinculo' => 1]);
                        } else {
                            TSession::setValue('dados_parentes_pf', (array) $data);
                            AdiantiCoreApplication::loadPage('DadosEndereco', 'onEdit');
                        }
                    } else if ($dados_iniciais_pf['estado_civil_id'] == 805 or $dados_iniciais_pf['estado_civil_id'] == 806) {
                        if ($tem_companheira_companheiro == 0) {
                            if ($dados_iniciais_pf['genero'] == 'M') {
                                $this->form->add(new TAlert('danger', 'Estado Civil: <b>União Estável</b>. Você precisa vincular uma <b>Companheira</b>!'));
                            } else {
                                $this->form->add(new TAlert('danger', 'Estado Civil: <b>União Estável</b>. Você precisa vincular um <b>Companheiro</b>!'));
                            }
                            AdiantiCoreApplication::loadPage('AddParente', 'onLoad', ['vinculo' => 1]);
                        } else {
                            TSession::setValue('dados_parentes_pf', (array) $data);
                            AdiantiCoreApplication::loadPage('DadosEndereco', 'onEdit');
                        }
                    } else if ($dados_iniciais_pf['estado_civil_id'] == 807 or $dados_iniciais_pf['estado_civil_id'] == 808) {
                        if ($tem_esposa_esposo == 0) {
                            if ($dados_iniciais_pf['genero'] == 'M') {
                                $this->form->add(new TAlert('danger', 'Estado Civil: <b>Casado</b>. Você precisa vincular uma <b>Esposa</b>!'));
                            } else {
                                $this->form->add(new TAlert('danger', 'Estado Civil: <b>Casada</b>. Você precisa vincular um <b>Esposo</b>!'));
                            }
                            AdiantiCoreApplication::loadPage('AddParente', 'onLoad', ['vinculo' => 1]);
                        } else {
                            TSession::setValue('dados_parentes_pf', (array) $data);
                            AdiantiCoreApplication::loadPage('DadosEndereco', 'onEdit');
                        }
                    } else {

                        TSession::setValue('dados_parentes_pf', (array) $data);
                        AdiantiCoreApplication::loadPage('DadosEndereco', 'onEdit');
                    }
                }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onReload()
    {

        $objects = array();

        if (TSession::getValue('dados_parentes_pf')) {
            $objects = TSession::getValue('dados_parentes_pf');
        }

        $this->detail_list->clear();
        if ($objects) {
            foreach ($objects as $object) {
                $this->detail_list->addItem($object);
            }
        }
    }

    public function onDelete($param)
    {
        $key = $param['cpf'];
        $objects = TSession::getValue('dados_parentes_pf');
        unset($objects[$key]);
        TSession::setValue('dados_parentes_pf', $objects);

        AdiantiCoreApplication::loadPage('DadosParentes', 'onReload');

        $this->onReload();
    }

    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded) {
            $this->onReload(func_get_arg(0));
        }
        parent::show();
    }
}
