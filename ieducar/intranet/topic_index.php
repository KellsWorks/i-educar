<?php

$desvio_diretorio = "";

class clsIndex extends clsBase
{

    function Formular()
    {
        $this->SetTitulo( "{$this->_instituicao} i-OpeTopicE" );
        $this->processoAp = "459";
    }
}

class indice
{
    function RenderHTML()
    {
        return "
                <table width='100%' height='100%'>
                    <tr align=center valign='top'><td><img src='imagens/i-pauta/splashscreen.jpg' style='padding-top: 50px'></td></tr>
                </table>
                ";
    }
}




?>
