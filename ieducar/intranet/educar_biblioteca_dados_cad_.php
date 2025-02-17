<?php

return new class extends clsCadastro {
    /**
     * Referencia pega da session para o idpes do usuario atual
     *
     * @var int
     */
    public $pessoa_logada;

    public $cod_biblioteca;
    public $ref_cod_instituicao;
    public $ref_cod_escola;
    public $nm_biblioteca;
    public $valor_multa;
    public $max_emprestimo;
    public $valor_maximo_multa;
    public $data_cadastro;
    public $data_exclusao;
    public $requisita_senha;
    public $ativo;
    public $dias_espera;

    public $dias_da_semana = [ '' => 'Selecione', 1 => 'Domingo', 2 => 'Segunda', 3 => 'Ter&ccedil;a', 4 => 'Quarta', 5 => 'Quinta', 6 => 'Sexta', 7 => 'S&aacute;bado' ];
    public $dia;
    public $biblioteca_dia_semana;
    public $incluir_dia_semana;
    public $excluir_dia_semana;

    public $nm_feriado;
    public $data_feriado;
    public $biblioteca_feriado;
    public $incluir_feriado;
    public $excluir_feriado;

    public function Inicializar()
    {
//      $retorno = "Novo";

        $this->cod_biblioteca=$_GET['cod_biblioteca'];

        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(629, $this->pessoa_logada, 11, 'educar_biblioteca_dados_lst.php');

        $nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);
        if ($nivel_usuario <= 3) {
            $permitido = true;
        } else {
            $obj_usuario_bib = new clsPmieducarBibliotecaUsuario();
            $lista_bib = $obj_usuario_bib->lista(null, $this->pessoa_logada);
            $permitido = false;
            if ($lista_bib) {
                foreach ($lista_bib as $biblioteca) {
                    if ($this->cod_biblioteca == $biblioteca['ref_cod_biblioteca']) {
                        $permitido = true;
                    }
                }
            }
        }

        if (!$permitido) {
            $this->simpleRedirect('educar_biblioteca_dados_lst.php');
        }
        if (is_numeric($this->cod_biblioteca)) {
            $obj = new clsPmieducarBiblioteca($this->cod_biblioteca);
            $registro  = $obj->detalhe();
            if ($registro) {
                foreach ($registro as $campo => $val) {  // passa todos os valores obtidos no registro para atributos do objeto
                    $this->$campo = $val;
                }

                if ($obj_permissoes->permissao_excluir(629, $this->pessoa_logada, 11)) {
                    $this->fexcluir = true;
                }
                $retorno = 'Editar';
            }
        }
        $this->url_cancelar = ($retorno == 'Editar') ? "educar_biblioteca_dados_det.php?cod_biblioteca={$registro['cod_biblioteca']}" : 'educar_biblioteca_dados_lst.php';
        $this->nome_url_cancelar = 'Cancelar';

        return $retorno;
    }

    public function Gerar()
    {
        // primary keys
        $this->campoOculto('cod_biblioteca', $this->cod_biblioteca);

        if ($_POST) {
            foreach ($_POST as $campo => $val) {
                $this->$campo = ($this->$campo) ? $this->$campo : $val;
            }
        }

        // foreign keys

        // text
        $this->campoTexto('nm_biblioteca', 'Biblioteca', $this->nm_biblioteca, 30, 255, true, false, false, '', '', '', '', true);
        $this->campoMonetario('valor_multa', 'Valor Multa', $this->valor_multa, 8, 8, true);
        $this->campoNumero('max_emprestimo', 'M&aacute;ximo Empr&eacute;stimo', $this->max_emprestimo, 8, 8, true);
        $this->campoMonetario('valor_maximo_multa', 'Valor M&aacute;ximo Multa', $this->valor_maximo_multa, 8, 8, true);

//      $opcoes = array( "" => "Selecione", 1 => "n&atilde;o", 2 => "sim" );
//      $this->campoLista( "requisita_senha", "Requisita Senha", $opcoes, $this->requisita_senha );
        $this->campoCheck('requisita_senha', 'Requisita Senha', $this->requisita_senha);
        $this->campoNumero('dias_espera', 'Dias Espera', $this->dias_espera, 2, 2, true);

        //-----------------------INCLUI DIA SEMANA------------------------//
        $this->campoQuebra();

        if ($_POST['biblioteca_dia_semana']) {
            $this->biblioteca_dia_semana = unserialize(urldecode($_POST['biblioteca_dia_semana']));
        }
        if (is_numeric($this->cod_biblioteca) && !$_POST) {
            $obj = new clsPmieducarBibliotecaDia();
            $registros = $obj->lista($this->cod_biblioteca);
            if ($registros) {
                foreach ($registros as $campo) {
                    $this->biblioteca_dia_semana['dia_'][] = $campo['dia'];
                }
            }
        }
        if ($_POST['dia']) {
            $this->biblioteca_dia_semana['dia_'][] = $_POST['dia'];
            unset($this->dia);
        }

        $this->campoOculto('excluir_dia_semana', '');
        unset($aux);

        if ($this->biblioteca_dia_semana) {
            foreach ($this->biblioteca_dia_semana as $key => $campo) {
                if ($campo) {
                    foreach ($campo as $chave => $dias) {
                        if ($this->excluir_dia_semana == $dias) {
                            $this->biblioteca_dia_semana[$chave] = null;
                            $this->excluir_dia_semana = null;
                        } else {
                            $this->campoTextoInv("dia_{$dias}", '', $this->dias_da_semana[$dias], 8, 8, false, false, false, '', "<a href='#' onclick=\"getElementById('excluir_dia_semana').value = '{$dias}'; getElementById('tipoacao').value = ''; {$this->__nome}.submit();\"><img src='imagens/nvp_bola_xis.gif' title='Excluir' border=0></a>");
                            $aux['dia_'][] = $dias;
                        }
                    }
                }
            }
            unset($this->biblioteca_dia_semana);
            $this->biblioteca_dia_semana = $aux;
        }

        $this->campoOculto('biblioteca_dia_semana', serialize($this->biblioteca_dia_semana));

        $opcoes = $this->dias_da_semana;

        if ($aux) {
            $this->campoLista('dia', 'Dia da Semana', $opcoes, $this->dia, '', false, '', "<a href='#' onclick=\"getElementById('incluir_dia_semana').value = 'S'; getElementById('tipoacao').value = ''; {$this->__nome}.submit();\"><img src='imagens/nvp_bot_adiciona.gif' title='Incluir' border=0></a>", false, false);
        } else {
            $this->campoLista('dia', 'Dia da Semana', $opcoes, $this->dia, '', false, '', "<a href='#' onclick=\"getElementById('incluir_dia_semana').value = 'S'; getElementById('tipoacao').value = ''; {$this->__nome}.submit();\"><img src='imagens/nvp_bot_adiciona.gif' title='Incluir' border=0></a>");
        }

        $this->campoOculto('incluir_dia_semana', '');
//      $this->campoRotulo( "bt_incluir_dia_semana", "Dia da Semana", "<a href='#' onclick=\"getElementById('incluir_dia_semana').value = 'S'; getElementById('tipoacao').value = ''; {$this->__nome}.submit();\"><img src='imagens/nvp_bot_incluir2.gif' title='Incluir' border=0></a>" );

        $this->campoQuebra();
        //-----------------------FIM INCLUI DIA SEMANA------------------------//

        //-----------------------INCLUI FERIADO------------------------//
        $this->campoQuebra();

        if ($_POST['biblioteca_feriado']) {
            $this->biblioteca_feriado = unserialize(urldecode($_POST['biblioteca_feriado']));
        }
        if (is_numeric($this->cod_biblioteca) && !$_POST) {
            $obj = new clsPmieducarBibliotecaFeriados();
            $registros = $obj->lista(null, $this->cod_biblioteca);
            if ($registros) {
                foreach ($registros as $campo) {
                    $aux['nm_feriado_']= $campo['nm_feriado'];
                    $aux['data_feriado_']= dataFromPgToBr($campo['data_feriado']);
                    $this->biblioteca_feriado[] = $aux;
                }
            }
        }

        unset($aux);

        if ($_POST['nm_feriado'] && $_POST['data_feriado']) {
            $aux['nm_feriado_'] = $_POST['nm_feriado'];
            $aux['data_feriado_'] = $_POST['data_feriado'];
            $this->biblioteca_feriado[] = $aux;
            unset($this->nm_feriado);
            unset($this->data_feriado);
        }

        $this->campoOculto('excluir_feriado', '');
        unset($aux);

        if ($this->biblioteca_feriado) {
            foreach ($this->biblioteca_feriado as $key => $feriado) {
                if ($this->excluir_feriado == $feriado['nm_feriado_']) {
                    unset($this->biblioteca_feriado[$key]);
                    unset($this->excluir_feriado);
                } else {
                    $this->campoTextoInv("nm_feriado_{$feriado['nm_feriado_']}", '', $feriado['nm_feriado_'], 30, 255, false, false, true);
                    $this->campoTextoInv("data_feriado_{$feriado['nm_feriado_']}", '', $feriado['data_feriado_'], 10, 10, false, false, false, '', "<a href='#' onclick=\"getElementById('excluir_feriado').value = '{$feriado['nm_feriado_']}'; getElementById('tipoacao').value = ''; {$this->__nome}.submit();\"><img src='imagens/nvp_bola_xis.gif' title='Excluir' border=0></a>");
                    $aux['nm_feriado_'] = $feriado['nm_feriado_'];
                    $aux['data_feriado_'] = $feriado['data_feriado_'];
                }
            }
        }
        $this->campoOculto('biblioteca_feriado', serialize($this->biblioteca_feriado));

        $this->campoTexto('nm_feriado', 'Feriado', $this->nm_feriado, 30, 255);
        $this->campoData('data_feriado', ' Data Feriado', $this->data_feriado);

        $this->campoOculto('incluir_feriado', '');
        $this->campoRotulo('bt_incluir_feriado', 'Feriado', "<a href='#' onclick=\"getElementById('incluir_feriado').value = 'S'; getElementById('tipoacao').value = ''; {$this->__nome}.submit();\"><img src='imagens/nvp_bot_adiciona.gif' title='Incluir' border=0></a>");

        $this->campoQuebra();
        //-----------------------FIM INCLUI FERIADO------------------------//
    }

    public function Editar()
    {
        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(629, $this->pessoa_logada, 11, 'educar_biblioteca_dados_lst.php');

        $this->valor_multa = str_replace('.', '', $this->valor_multa);
        $this->valor_multa = str_replace(',', '.', $this->valor_multa);
        $this->valor_maximo_multa = str_replace('.', '', $this->valor_maximo_multa);
        $this->valor_maximo_multa = str_replace(',', '.', $this->valor_maximo_multa);

        if ($this->requisita_senha == 'on') {
            $this->requisita_senha = 1;
        } else {
            $this->requisita_senha = 0;
        }

        $this->biblioteca_dia_semana = unserialize(urldecode($this->biblioteca_dia_semana));
        if ($this->biblioteca_dia_semana) {
            $obj = new clsPmieducarBiblioteca($this->cod_biblioteca, null, null, null, $this->valor_multa, $this->max_emprestimo, $this->valor_maximo_multa, null, null, $this->requisita_senha, 1, $this->dias_espera);
            $editou = $obj->edita();
            if ($editou) {
                //-----------------------EDITA DIA DA SEMANA------------------------//
                $obj  = new clsPmieducarBibliotecaDia($this->cod_biblioteca);
                $excluiu = $obj->excluirTodos();
                if ($excluiu) {
                    foreach ($this->biblioteca_dia_semana as $campo) {
                        for ($i = 0; $i < sizeof($campo) ; $i++) {
                            $obj = new clsPmieducarBibliotecaDia($this->cod_biblioteca, $campo[$i]);
                            $cadastrou1  = $obj->cadastra();
                            if (!$cadastrou1) {
                                $this->mensagem = 'Edi&ccedil;&atilde;o n&atilde;o realizada.<br>';

                                return false;
                            }
                        }
                    }
                }
                //-----------------------FIM EDITA DIA DA SEMANA------------------------//

                //-----------------------EDITA FERIADO------------------------//
                $obj  = new clsPmieducarBibliotecaFeriados();
                $excluiu = $obj->excluirTodos($this->cod_biblioteca);
                if ($excluiu) {
                    $this->biblioteca_feriado = unserialize(urldecode($this->biblioteca_feriado));
                    if ($this->biblioteca_feriado) {
                        foreach ($this->biblioteca_feriado as $feriado) {
                            $feriado['data_feriado_'] = dataToBanco($feriado['data_feriado_']);
                            $obj = new clsPmieducarBibliotecaFeriados(null, $this->cod_biblioteca, $feriado['nm_feriado_'], null, $feriado['data_feriado_'], null, null, 1);
                            $cadastrou2  = $obj->cadastra();
                            if (!$cadastrou2) {
                                $this->mensagem = 'Cadastro n&atilde;o realizado.<br>';

                                return false;
                            }
                        }
                    }
                }
                $this->mensagem .= 'Edi&ccedil;&atilde;o efetuada com sucesso.<br>';
                $this->simpleRedirect('educar_biblioteca_dados_lst.php');
                //-----------------------FIM EDITA FERIADO------------------------//
            }
            $this->mensagem = 'Edi&ccedil;&atilde;o n&atilde;o realizada.<br>';

            return false;
        }
        echo '<script> alert(\'É necessário adicionar pelo menos 1 Dia da Semana\') </script>';
        $this->mensagem = 'Edi&ccedil;&atilde;o n&atilde;o realizada.<br>';

        return false;
    }

    public function Excluir()
    {
        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_excluir(629, $this->pessoa_logada, 11, 'educar_biblioteca_dados_lst.php');

        $obj = new clsPmieducarBiblioteca($this->cod_biblioteca, null, null, null, 'NULL', 'NULL', 'NULL', null, null, 'NULL', 1, 'NULL');
        $editou = $obj->edita();
        if ($editou) {
            $obj  = new clsPmieducarBibliotecaDia($this->cod_biblioteca);
            $excluiu1 = $obj->excluirTodos();
            if ($excluiu1) {
                $obj  = new clsPmieducarBibliotecaFeriados();
                $excluiu2 = $obj->excluirTodos($this->cod_biblioteca);
                if ($excluiu2) {
                    $this->mensagem .= 'Exclus&atilde;o efetuada com sucesso.<br>';
                    $this->simpleRedirect('educar_biblioteca_dados_lst.php');
                }
            }
        }

        $this->mensagem = 'Exclus&atilde;o n&atilde;o realizada.<br>';

        return false;
    }

    public function Formular()
    {
        $this->title = 'SoftagonEducation - Dados Biblioteca';
        $this->processoAp = '629';
    }
};
