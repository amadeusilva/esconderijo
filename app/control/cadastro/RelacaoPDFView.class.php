<?php
class RelacaoPDFView extends TWindow
{
    public function __construct($param)
    {

        parent::__construct();
        parent::setTitle(_t('Window with embedded PDF'));
        parent::setSize(0.8, 0.8);

        $object = new TElement('object');
        $object->id  = $param['id'];
        $object->data  = '/ADEA/files/documents/dadosderelacao/Relacao-N-' . $object->id . '.pdf';
        $object->type  = 'application/pdf';
        $object->style = "width: 100%; height:calc(100% - 10px)";
        $object->add('O navegador não suporta a exibição deste conteúdo, <a style="color:#007bff;" target=_newwindow href="' . $object->data . '"> clique aqui para baixar</a>...');

        parent::add($object);
    }

    public function onEdit($param)
    {
    }
}
