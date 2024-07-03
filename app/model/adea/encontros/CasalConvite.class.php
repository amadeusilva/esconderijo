<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class CasalConvite extends TRecord
{
    const TABLENAME = 'casal_convite';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('encontro_id');
        parent::addAttribute('filho_casal_id');
        parent::addAttribute('pai_casal_id');
    }
    
    public function get_Cidade()
    {
        return Encontro::find($this->encontro_id);
    }
    
}