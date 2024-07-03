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

        $col_parentesco_id  = new TDataGridColumn('parentesco_id', 'Grau', 'left');
        $col_cpf            = new TDataGridColumn('cpf', 'CPF', 'left');
        $col_nome           = new TDataGridColumn('popular', 'Nome', 'left');
        $col_genero         = new TDataGridColumn('genero', 'Gênero', 'center');
        $col_dt_nascimento  = new TDataGridColumn('dt_nascimento', 'Nascimento', 'center');
        $col_endereco_id       = new TDataGridColumn('endereco_id', 'Mora comigo?', 'center');

        $this->detail_list->addColumn($col_parentesco_id);
        $this->detail_list->addColumn($col_cpf);
        $this->detail_list->addColumn($col_nome);
        $this->detail_list->addColumn($col_genero);
        $this->detail_list->addColumn($col_dt_nascimento);
        $this->detail_list->addColumn($col_endereco_id);

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

        $col_endereco_id->setTransformer(function ($value) {
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

        // creates two datagrid actions
        $action2 = new TDataGridAction([$this, 'onDelete'], ['cpf' => '{cpf}']);
        //$action2->setDisplayCondition(array($this, 'displayColumn'));

        // add the actions to the datagrid
        $this->detail_list->addAction($action2, 'Delete', 'far:trash-alt red');

        // create the datagrid model
        $this->detail_list->createModel();

        $panel = new TPanelGroup('Pessoas Vinculadas', '#f5f5f5');
        $panel->add($this->detail_list);
        $panel->addHeaderActionLink('Vincular',  new TAction(['AddParente', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
        $panel->getBody()->style = 'overflow-x:auto';

        $dadosiniciaispf = TSession::getValue('dados_iniciais_pf');
        $dadosparentespf = TSession::getValue('dados_parentes_pf');
        $dadosrelacao = TSession::getValue('dados_relacao');

        $ele_ela = '';
        $filho_filha = 0;
        $sing_plur = 'filho';

        if ($dadosparentespf) {
            foreach ($dadosparentespf as $banda) {
                if ($banda->parentesco_id >= 921 and $banda->parentesco_id <= 926) {
                    $ele_ela = $banda->popular;
                } else if ($banda->parentesco_id >= 903 and $banda->parentesco_id <= 904) {
                    $filho_filha += 1;
                }
            }

            if ($filho_filha > 1) {
                $sing_plur = 'filhos';
            }


            if ($dadosiniciaispf['genero'] == 'M') {
                $eles = $dadosiniciaispf['popular'] . '</b> & <b>' . $ele_ela;
            } else {
                $eles = $ele_ela  . '</b> & <b>' . $dadosiniciaispf['popular'];
            }

            $panel->addFooter('<b>' . $eles . '</b> - <b>' . $dadosrelacao['tempo'] . ' - <b>' . $filho_filha . '</b> ' . $sing_plur . '!');
        }

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
                        AdiantiCoreApplication::loadPage('AddParente', 'onLoad');
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
                        AdiantiCoreApplication::loadPage('AddParente', 'onLoad');
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
                        AdiantiCoreApplication::loadPage('AddParente', 'onLoad');
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
        $objects = TSession::getValue('dados_parentes_pf');

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
