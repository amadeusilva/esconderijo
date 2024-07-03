<?php

trait ControlePessoas
{
    public function onSalvaParenteInverso($pessoaparentegenero, $pessoa_id, $parentesco_id, $pessoa_parente_id)
    {

        $pessoaparentegenero = $pessoaparentegenero == 'Masculino' ? 'M' : 'F';

        if ($parentesco_id == 901 or $parentesco_id == 902) { // 902 - MÃE - F / 901 - PAI - M
            $novoparentesco_id = $pessoaparentegenero == 'M' ? 903 : 904; // 904 - FILHA - F / 903 - FILHO - M

        } else if ($parentesco_id == 903 or $parentesco_id == 904) { // 904 - FILHA - F / 903 - FILHO - M
            $novoparentesco_id = $pessoaparentegenero == 'M' ? 901 : 902; // 902 - MÃE - F / 901 - PAI - M

            $buscarelacao = PessoaParentesco::where('pessoa_id', '=', $pessoa_id)->where('parentesco_id', '>=', 921)->where('parentesco_id', '<=', 926)->first();
            if ($buscarelacao) {
                $pai_mae = new PessoaParentesco;
                $pai_mae->pessoa_id = $buscarelacao->pessoa_parente_id;
                $pai_mae->parentesco_id = $parentesco_id;
                $pai_mae->pessoa_parente_id = $pessoa_parente_id;
                $pai_mae->store();

                $pai_mae = new PessoaParentesco;
                $pai_mae->pessoa_id = $pessoa_parente_id;
                $pai_mae->parentesco_id = $buscarelacao->PessoaParente->PessoaFisica->genero == 'M' ? 901 : 902; // Pai e mãe
                $pai_mae->pessoa_parente_id = $buscarelacao->pessoa_parente_id;
                $pai_mae->store();
            }
        } else if ($parentesco_id == 921 or $parentesco_id == 922) { // 922 - ESPOSA - F / 921 - ESPOSO - M
            $buscaesps = PessoaFisica::where('pessoa_id', '=', $pessoa_parente_id)->first();
            if ($buscaesps) {
                $buscaesps->estado_civil_id = $parentesco_id == 922 ? 808 : 807;
                $buscaesps->store();
            }
            $novoparentesco_id = $pessoaparentegenero == 'M' ? 921 : 922;
        } else if ($parentesco_id == 923 or $parentesco_id == 924) { // 924 - COMPANHEIRA - F / 923 - COMPANHEIRO - M
            $buscaesps = PessoaFisica::where('pessoa_id', '=', $pessoa_parente_id)->first();
            if ($buscaesps) {
                $buscaesps->estado_civil_id = $parentesco_id == 924 ? 806 : 805;
                $buscaesps->store();
            }
            $novoparentesco_id = $pessoaparentegenero == 'M' ? 923 : 924;
        } else if ($parentesco_id == 925 or $parentesco_id == 926) { // 926 - CONVIVENTE - F / 925 - CONVIVENTE - M
            $novoparentesco_id = $pessoaparentegenero == 'M' ? 925 : 926;
            $buscaesps = PessoaFisica::where('pessoa_id', '=', $pessoa_parente_id)->first();
            if ($buscaesps) {
                $buscaesps->estado_civil_id = $parentesco_id == 926 ? 804 : 803;
                $buscaesps->store();
            }
        }
        PessoaParentesco::where('pessoa_id', '=', $pessoa_parente_id)->where('parentesco_id', '=', $novoparentesco_id)->where('pessoa_parente_id', '=', $pessoa_id)->delete();
        $novoparentesco = new PessoaParentesco();
        $novoparentesco->pessoa_id = $pessoa_parente_id;
        $novoparentesco->parentesco_id = $novoparentesco_id;
        $novoparentesco->pessoa_parente_id = $pessoa_id;
        $novoparentesco->store();
    }
}
