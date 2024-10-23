<?php

/**
 * SaleList
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EncontristaDataGrid extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;

    use Adianti\Base\AdiantiStandardListTrait;

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('adea');          // defines the database
        $this->setActiveRecord('ViewEncontrista');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('encontro_id', '=', 'encontro_id'); // filterField, operator, formField
        $this->addFilterField('circulo_id', '=', 'circulo_id'); // filterField, operator, formField
        $this->addFilterField('casal_id', '=', 'casal_id'); // filterField, operator, formField

        $this->addFilterField('casal_id', '=', 'ele_id'); // filterField, operator, formField
        $this->addFilterField('casal_id', '=', 'ela_id'); // filterField, operator, formField

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_EncontristaDataGrid');
        $this->form->setFormTitle('Lista de Encontristas (Filtros)');

        // create the form fields
        $id        = new TEntry('id');

        $encontro_id = new TDBCombo('encontro_id', 'adea', 'ViewEncontro', 'id', '{sigla} ({id})', 'id');
        $encontro_id->enableSearch();

        $filterCirculo = new TCriteria;
        $filterCirculo->add(new TFilter('lista_id', '=', '18'));
        $circulo_id = new TDBCombo('circulo_id', 'adea', 'ListaItens', 'id', 'item', 'id', $filterCirculo);
        $circulo_id->enableSearch();

        $casal_id = new TDBCombo('casal_id', 'adea', 'ViewCasal', 'relacao_id', '{casal} ({Casamento})', 'relacao_id');
        $casal_id->enableSearch();

        $ele_id = new TDBCombo('ele_id', 'adea', 'ViewCasal', 'relacao_id', '{Ele->nome} ({Ele->Nascimento})', 'ele_id');
        $ele_id->enableSearch();

        $ela_id = new TDBCombo('ela_id', 'adea', 'ViewCasal', 'relacao_id', '{Ela->nome} ({Ela->Nascimento})', 'ela_id');
        $ela_id->enableSearch();

        //$ele_id = new TDBUniqueSearch('ele_id', 'adea', 'ViewCasal', 'Ele->nome', 'Ele->nome');
        //$ele_id->setMinLength(1);
        //$ele_id->setMask('{Ele->nome} ({Ele->dt_nascimento})');

        // add the fields
        $this->form->addFields([new TLabel('Id')],          [$id]);
        /*
        $this->form->addFields(
            [new TLabel('Data (Inicial)')],
            [$date_from],
            [new TLabel('Data (Final)')],
            [$date_to]
        );
        */
        $this->form->addFields([new TLabel('Encontro')],    [$encontro_id]);
        $this->form->addFields([new TLabel('Círculo')],    [$circulo_id]);
        $this->form->addFields([new TLabel('Casal')],    [$casal_id]);
        $this->form->addFields([new TLabel('Ele')],    [$ele_id]);
        $this->form->addFields([new TLabel('Ela')],    [$ela_id]);

        $id->setSize('50%');
        //$date_from->setSize('100%');
        //$date_to->setSize('100%');
        //$date_from->setMask('dd/mm/yyyy');
        //$date_to->setMask('dd/mm/yyyy');
        $casal_id->setSize('100%');
        $ele_id->setSize('100%');
        $ela_id->setSize('100%');

        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue('EncontristaDataGrid_filter_data'));

        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Avulso',  new TAction(['AddEncontrista', 'onClear'], ['register_state' => 'false']), 'fa:plus orange');
        $this->form->addActionLink('Novo',  new TAction(['EncontristaForm', 'onClear'], ['register_state' => 'false']), 'fa:plus green');
        $this->form->addActionLink('Círculos',  new TAction(['CirculosForm', 'onClear'], ['register_state' => 'false']), 'fa:plus black');
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        //$this->datagrid->disableDefaultClick();

        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'center');
        $col_casal_id    = new TDataGridColumn('casal_id', 'Nº', 'center');
        $col_encontro_id = new TDataGridColumn('encontro_id', 'Encontro', 'center');
        $col_encontro = new TDataGridColumn('encontro', 'Evento', 'left');
        $col_casal = new TDataGridColumn('casal', 'Casal', 'left');
        $col_secretario_s_n = new TDataGridColumn('secretario_s_n', 'Secretário?', 'center');
        $col_circulo = new TDataGridColumn('CirculoCor', 'Círculo', 'left');
        $col_casal_convite = new TDataGridColumn('casal_convite', 'Casal Convite', 'left');

        $col_casal_id->setTransformer(function ($value) {
            if ($value) {
                //$icon  = "<i class='far fa-envelope' aria-hidden='true'></i>"; //{$icon} 
                return "<a generator='adianti' href='index.php?class=CasalPanel&method=onView&relacao_id=$value'>$value</a>";
            }
            return $value;
        });

        $col_encontro_id->setTransformer(function ($value) {
            if ($value) {
                //$icon  = "<i class='far fa-envelope' aria-hidden='true'></i>"; //{$icon} 
                return "<a generator='adianti' href='index.php?class=EncontroPanel&method=onView&key=$value'>$value</a>";
            }
            return $value;
        });

        // add the columns to the DataGrid
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_casal_id);
        $this->datagrid->addColumn($col_encontro_id);
        $this->datagrid->addColumn($col_encontro);
        $this->datagrid->addColumn($col_casal);
        $this->datagrid->addColumn($col_secretario_s_n);
        $this->datagrid->addColumn($col_circulo);
        $this->datagrid->addColumn($col_casal_convite);

        $col_secretario_s_n->setTransformer(function ($value) {
            if ($value == 1) {
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

        // creates the datagrid column actions
        $col_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_casal_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'casal_id']);
        $col_encontro_id->setAction(new TAction([$this, 'onReload']), ['order' => 'encontro_id']);
        $col_encontro->setAction(new TAction([$this, 'onReload']), ['order' => 'encontro']);
        $col_casal->setAction(new TAction([$this, 'onReload']), ['order' => 'casal']);
        $col_secretario_s_n->setAction(new TAction([$this, 'onReload']), ['order' => 'secretario_s_n']);
        $col_circulo->setAction(new TAction([$this, 'onReload']), ['order' => 'circulo']);
        $col_casal_convite->setAction(new TAction([$this, 'onReload']), ['order' => 'casal_convite']);

        // define the transformer method over date
        /*
        $col_dt_inicial->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });


        $col_dt_final->setTransformer(function ($value, $object, $row) {
            $date = new DateTime($value);
            if ($value == '0000-00-00') {
                return '-';
            } else {
                return $date->format('d/m/Y');
            }
        });
        */

        $action1 = new TDataGridAction(['EncontristaForm', 'onEdit'],   ['key' => '{id}', 'register_state' => 'false']);
        $action3 = new TDataGridAction(['AddEncontrista', 'onEdit'],   ['id' => '{id}', 'casal_id' => '{casal_id}', 'register_state' => 'false']);

        $this->datagrid->addAction($action1, 'Editar',   'far:edit blue');
        $this->datagrid->addAction($action3, 'Editar',   'far:edit orange');

        // create the datagrid model
        $this->datagrid->createModel();

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }

    /**
     *
     */
    public function changeStatus($param)
    {
        try {
            TTransaction::open('samples');
            $sale = Sale::find($param['id']);
            $sale->status_id = $param['status_id'];
            $sale->store();
            TTransaction::close();

            $this->onReload($param);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Clear filters
     */
    function clear()
    {
        $this->clearFilters();
        $this->onReload();
    }
}
