<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                        *
*   @author Prefeitura Municipal de Itajaí                               *
*   @updated 29/03/2007                                                  *
*   Pacote: i-PLB Software Público Livre e Brasileiro                    *
*                                                                        *
*   Copyright (C) 2006  PMI - Prefeitura Municipal de Itajaí             *
*                       ctima@itajai.sc.gov.br                           *
*                                                                        *
*   Este  programa  é  software livre, você pode redistribuí-lo e/ou     *
*   modificá-lo sob os termos da Licença Pública Geral GNU, conforme     *
*   publicada pela Free  Software  Foundation,  tanto  a versão 2 da     *
*   Licença   como  (a  seu  critério)  qualquer  versão  mais  nova.    *
*                                                                        *
*   Este programa  é distribuído na expectativa de ser útil, mas SEM     *
*   QUALQUER GARANTIA. Sem mesmo a garantia implícita de COMERCIALI-     *
*   ZAÇÃO  ou  de ADEQUAÇÃO A QUALQUER PROPÓSITO EM PARTICULAR. Con-     *
*   sulte  a  Licença  Pública  Geral  GNU para obter mais detalhes.     *
*                                                                        *
*   Você  deve  ter  recebido uma cópia da Licença Pública Geral GNU     *
*   junto  com  este  programa. Se não, escreva para a Free Software     *
*   Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA     *
*   02111-1307, USA.                                                     *
*                                                                        *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/**
* @author Prefeitura Municipal de Itajaí
*
* Criado em 02/08/2006 14:41 pelo gerador automatico de classes
*/

require_once("include/pmieducar/geral.inc.php");

class clsPmieducarEscolaCandidatoFilaUnica
{
    var $ref_cod_candidato_fila_unica;
    var $ref_cod_escola;
    var $sequencial;

    // Armazena o total de resultados obtidos na ultima chamada ao metodo lista
    var $_total;

    // Nome do schema
    var $_schema;

    // Nome da tabela
    var $_tabela;

    // Lista separada por virgula, com os campos que devem ser selecionados na proxima chamado ao metodo lista
    var $_campos_lista;

    // Lista com todos os campos da tabela separados por virgula, padrao para selecao no metodo lista
    var $_todos_campos;

    // Valor que define a quantidade de registros a ser retornada pelo metodo lista
    var $_limite_quantidade;

    // Define o valor de offset no retorno dos registros no metodo lista
    var $_limite_offset;

    // Define o campo padrao para ser usado como padrao de ordenacao no metodo lista
    var $_campo_order_by;


    /**
     * Construtor (PHP 4)
     *
     * @return object
     */
    function __construct($ref_cod_candidato_fila_unica = null,
                                                  $ref_cod_escola = null,
                                                  $sequencial = null)
    {
        $db = new clsBanco();
        $this->_schema = "pmieducar.";
        $this->_tabela = "{$this->_schema}escola_candidato_fila_unica";

        $this->_campos_lista = $this->_todos_campos = "ref_cod_candidato_fila_unica,
                                                       ref_cod_escola,
                                                       sequencial";

        if( is_numeric( $ref_cod_candidato_fila_unica ) )
        {
            $this->ref_cod_candidato_fila_unica = $ref_cod_candidato_fila_unica;
        }
        if( is_numeric( $ref_cod_escola ) )
        {
            $this->ref_cod_escola = $ref_cod_escola;
        }
        if( is_numeric( $sequencial ) )
        {
            $this->sequencial = $sequencial;
        }

    }

    /**
     * Cria um novo registro
     *
     * @return bool
     */
    function cadastra()
    {
        if( is_numeric($this->ref_cod_candidato_fila_unica) &&
            is_numeric($this->ref_cod_escola) &&
            is_numeric($this->sequencial))
        {
            $db = new clsBanco();

            $campos = "";
            $valores = "";
            $gruda = "";

            $campos .= "{$gruda}ref_cod_candidato_fila_unica";
            $valores .= "{$gruda}'{$this->ref_cod_candidato_fila_unica}'";
            $gruda = ", ";

            $campos .= "{$gruda}ref_cod_escola";
            $valores .= "{$gruda}'{$this->ref_cod_escola}'";
            $gruda = ", ";

            $campos .= "{$gruda}sequencial";
            $valores .= "{$gruda}'{$this->sequencial}'";
            $gruda = ", ";
            
            $db->Consulta("INSERT INTO {$this->_tabela} ($campos) VALUES($valores)");
            return true;
        }
        return false;
    }

    /**
     * Retorna uma lista filtrados de acordo com os parametros
     *
     * @return array
     */
    function lista()
    {
        $sql = "SELECT {$this->_campos_lista} FROM {$this->_tabela}";
        $filtros = "";

        $whereAnd = " WHERE ";

        if(is_numeric($this->ref_cod_candidato_fila_unica))
        {
            $filtros .= "{$whereAnd} ref_cod_candidato_fila_unica = {$this->ref_cod_candidato_fila_unica}";
            $whereAnd = " AND ";
        }
        if(is_numeric($this->ref_cod_escola))
        {
            $filtros .= "{$whereAnd} ref_cod_escola = {$this->ref_cod_escola}";
            $whereAnd = " AND ";
        }
        if(is_numeric($this->sequencial))
        {
            $filtros .= "{$whereAnd} sequencial = {$this->sequencial}";
            $whereAnd = " AND ";
        }


        $db = new clsBanco();
        $countCampos = count( explode( ",", $this->_campos_lista ) );
        $resultado = array();

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico( "SELECT COUNT(0) FROM {$this->_tabela} {$filtros}" );

        $db->Consulta( $sql );

        if( $countCampos > 1 )
        {
            while ( $db->ProximoRegistro() )
            {
                $tupla = $db->Tupla();

                $tupla["_total"] = $this->_total;
                $resultado[] = $tupla;
            }
        }
        else
        {
            while ( $db->ProximoRegistro() )
            {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }
        if( count( $resultado ) )
        {
            return $resultado;
        }
        return false;
    }

    /**
     * Exclui todos os registros referentes a uma turma
     */
    function  excluirTodos()
    {
        if (is_numeric($this->ref_cod_candidato_fila_unica))
        {
            $db = new clsBanco();
            $db->Consulta("DELETE FROM {$this->_tabela} WHERE ref_cod_candidato_fila_unica = {$this->ref_cod_candidato_fila_unica}");
            return true;
        }
        return false;
    }

    /**
     * Define quais campos da tabela serao selecionados na invocacao do metodo lista
     *
     * @return null
     */
    function setCamposLista( $str_campos )
    {
        $this->_campos_lista = $str_campos;
    }

    /**
     * Define que o metodo Lista devera retornoar todos os campos da tabela
     *
     * @return null
     */
    function resetCamposLista()
    {
        $this->_campos_lista = $this->_todos_campos;
    }

    /**
     * Define limites de retorno para o metodo lista
     *
     * @return null
     */
    function setLimite( $intLimiteQtd, $intLimiteOffset = null )
    {
        $this->_limite_quantidade = $intLimiteQtd;
        $this->_limite_offset = $intLimiteOffset;
    }

    /**
     * Retorna a string com o trecho da query resposavel pelo Limite de registros
     *
     * @return string
     */
    function getLimite()
    {
        if( is_numeric( $this->_limite_quantidade ) )
        {
            $retorno = " LIMIT {$this->_limite_quantidade}";
            if( is_numeric( $this->_limite_offset ) )
            {
                $retorno .= " OFFSET {$this->_limite_offset} ";
            }
            return $retorno;
        }
        return "";
    }

    /**
     * Define campo para ser utilizado como ordenacao no metolo lista
     *
     * @return null
     */
    function setOrderby( $strNomeCampo )
    {
        // limpa a string de possiveis erros (delete, insert, etc)
        //$strNomeCampo = eregi_replace();

        if( is_string( $strNomeCampo ) && $strNomeCampo )
        {
            $this->_campo_order_by = $strNomeCampo;
        }
    }

    /**
     * Retorna a string com o trecho da query resposavel pela Ordenacao dos registros
     *
     * @return string
     */
    function getOrderby()
    {
        if( is_string( $this->_campo_order_by ) )
        {
            return " ORDER BY {$this->_campo_order_by} ";
        }
        return "";
    }
}