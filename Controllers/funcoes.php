<?php
    /**
    * Arquivo com as Funções gerais.
    *
    * Esse arquivo contém todas as funções que o programa
    * irá usar.
    * @author Pedro Serer
    * @version 1.0.1
    */

    const BARRA = DIRECTORY_SEPARATOR;

    $pathFiles = "../php-excel-reader-2.21" . BARRA;
    require($pathFiles . "excel_reader2.php");
    require($pathFiles . "SpreadsheetReader.php");

    /**
    * Função que lê o arquivo Excel
    * @param string $diretorio caminho absoluto do arquivo.
    * @return array $CSV tem as linhas do arquivo; $cabecalho[0] tem
    * o cabeçalho do arquivo CSV.
    */


    function excel ($diretorio)
    {
        $excelCSV  = fopen($diretorio, "r");
        $cabecalho = fgetcsv($excelCSV, 10500, ",");
        $i = 0;

        while (($linha = fgetcsv($excelCSV, 10500, ",")) !== false) {
            $CSV[$i] = $linha[0];
            $i++;
        }

        fclose($excelCSV);

        return [$CSV, $cabecalho[0]];
    }

    /**
    * Função que formata o cabeçalho. Sua funcionalidade
    * se assemelha a função nativa str_replace() do PHP
    * com a diferença dele somente deletar o delimitador.
    *
    * @param array $cabecalho vetor com os campos do cabeçalho do CSV.
    * @param string $delimitador por padrão é o ";" mas pode ser
    * ajustado de acordo com o CSV escolhido. No final da operação
    * o delimitador é deletado.
    * @return array $cab_formatado é um vetor com os campos formatado
    * pronto para usar nas tabelas SQL.
    */


    function formata_cabecalho ($cabecalho, $delimitador = ";")
    {
        $tam = strlen($cabecalho);
        $pos = 0;
        $j   = 0;

        for ($i=0; $i < $tam; $i++) {
            $cab_formatado[$pos] = null;                     //Evia erros de array.
            if ($cabecalho[$i] == $delimitador) {
                while ($j < $i) {
                    $cab_formatado[$pos] .= $cabecalho[$j];
                    $j++;
                }
                $pos++;
                $cabecalho[$i] = null;                       //Deleta o delimitador
            }
        }

        return $cab_formatado;
    }

    /**
    * Função que fomata o nome da tabela.
    *
    * @param string $diretorio o primeiro parâmetro do terminal, diretório.
    * @return string o nome que será usado nas operações de criação
    * da tabela.
    */


    function formata_nome_tabela ($diretorio) {
        $deletar = [BARRA, ".csv"];

        $tabela = strrchr($diretorio, BARRA);
        $tabela = str_replace($deletar, " ", $tabela);
        $tabela = trim($tabela);

        return $tabela;
    }

    /**
    * Método que apresenta na tela o modo verboso.
    * @param string $query o valor com a query SQL criada.
    */


    function verboso ($query)
    {
        echo "\n\n";
        echo "--------------------------------------------------------------------------------\n";
        echo $query;
        echo "--------------------------------------------------------------------------------\n";
    }

    /**
    * ---------------------------------------------------------------------------------------------
    *                                     Funções de SQL
    * ---------------------------------------------------------------------------------------------
    */


    /**
    *     A linguagem usada por padrão é o MySQL.
    */

    /**
    * Função que determina o tipo de dados das colunas
    * da tabela.
    *
    * @param array $primeiraLinha tem todos os dados da primeira linha
    * do arquivo CSV para fazer a validação do tipo de dados.
    * @return array $tipo, $tamanho onde $tipo é um array com todos os
    * tipos de cada coluna e $tamanho é um array com todos os valores de
    * tamanho de suas respectivas colunas.
    */


    function tipo_de_dados ($primeiraLinha)
    {
        $primeiraLinha = formata_cabecalho($primeiraLinha);

        for ($i=0; $i < count($primeiraLinha); $i++) {
            $tamanho[$i] = strlen($primeiraLinha[$i]);

            //Verificação a nível de bit. Se for inteiro o valor recebe TinyInt, senão recebe Varchar.
            for ($j=0; $j < $tamanho[$i]; $j++) {
                if (ord($primeiraLinha[$i][$j]) > 47 && ord($primeiraLinha[$i][$j]) < 58) {
                    $string = false;
                } else {
                    $string = true;
                }
            }

            if ($string == true || gettype($primeiraLinha[$i]) == 'NULL') {
                $tipo[$i] = "VarChar";
            } else {
                if (strlen($primeiraLinha[$i]) > 2 && strlen($primeiraLinha[$i]) < 8) {
                    $tipo[$i] = "INT";
                } else if (strlen($primeiraLinha[$i]) > 8) {
                    $tipo[$i] = "BigInt";
                } else {
                    $tipo[$i] = "TinyInt";
                }
            }

            if ($primeiraLinha[$i] == 'true' || $primeiraLinha[$i] == 'false') {
                $tipo[$i] = "Boolean";
            }
        }

        return [$tipo, $tamanho];
    }

    /**
    * Função que cria a query e a tabela no banco de dados.
    *
    * @param array $sql configurações de login no banco de dados
    * @param array $cabCSV contém os valores do cabeçalho do arquivo CSV.
    * @param string $nome é o nome da tabela a ser criada. O valor contido
    * no retorno da função formata_nome_tabela().
    * @param array $tipo retorno da função tipo_de_dados() com os tipos de
    * dados referentes a cada coluna da tabela.
    * @param array $tamanho contém o retorno dos valores referentes a cada
    * tipo de dados, respectivamente.
    * @param int $max variável com a quantidade máxima de colunas que a
    * tabela irá conter.
    * @param bool $verboso verifica se deve mostrar a saída para o usuário.
    * @return $sucesso se caso tudo ocorrer bem a função retorna 1.
    */


    function cria_tabela ($sql, $cabCSV, $nome, $tipo, $tamanho, $max, $verboso = false, $conexao)
    {
        verifica_conexao($conexao);

        $query = "CREATE TABLE $nome" . " (\n";
        $query .= "\t {$nome}_ID INT not null auto_increment,\n";

        for ($i=0; $i < $max; $i++) {
            $tamanho[$i] = $tamanho[$i] * 5;

            if ($cabCSV[$i] == null) {
                $cabCSV[$i] = "Adicional";
            }

            $query .= "\t" . ltrim($cabCSV[$i]) . " $tipo[$i]($tamanho[$i]) not null,\n";
        }

        $query .= "\tPRIMARY KEY ({$nome}_ID)\n";
        $query .= ");\n";

        if ($verboso == true) {
            verboso($query);
        }

        $sucesso = verifica_query($conexao, $query);

        return [$sucesso, $cabCSV];
    }

    /**
    * Função que a query e insere os dados no banco.
    *
    * @param array $sql configurações de login no banco de dados
    * @param array $linhasCSV contém os valores de cada registro a ser inserido.
    * @param string $nome é o nome da tabela a ser criada. O valor contido
    * no retorno da função formata_nome_tabela().
    * @param array $cabCSV contém os valores do cabeçalho do arquivo CSV.
    * @param int $max variável com a quantidade máxima de colunas que a
    * tabela irá conter.
    * @param bool $verboso verifica se deve mostrar a saída para o usuário.
    * @return $sucesso se caso tudo ocorrer bem a função retorna 1.
    */


    function insere_dados ($sql, $linhasCSV, $nome, $colunas, $tipo, $verboso = false, $conexao)
    {
        $maxColunas = count($colunas);
        $maxLinhas = count($linhasCSV);

        verifica_conexao($conexao);

        $query = "INSERT INTO $nome " . "({$nome}_ID, ";

        for ($i=0; $i < $maxColunas; $i++) {
            if ($i < ($maxColunas - 1)) {
                $query .= ltrim($colunas[$i]) . ",";
            } else {
                $query .= ltrim($colunas[$i]);
            }
        }

        $query .= ") VALUES\n";

        for ($i=0; $i < $maxLinhas; $i++) {

            $k = 0;
            $strings[$i] = explode(";", $linhasCSV[$i]);

            for ($j=0; $j < count($strings[$i]); $j++) {

                //Formata todo Varchar com aspas
                if ($tipo[$k] == "VarChar") {
                    $strings[$i][$j] = str_replace($strings[$i][$j], "'" . $strings[$i][$j] . "'", $strings[$i][$j]);
                }

                 //Impede dos campos ficarem nulos
                if ($strings[$i][$j] == null) {
                    $strings[$i][$j] = 0;
                }

                $k++;
            }

            $strings[$i] = implode(",", $strings[$i]);

            //Última formatação da query
            if ($i == $maxLinhas - 1) {
                $query .= "\t (DEFAULT,". $strings[$i] . ")\n";
            } else {
                $query .= "\t (DEFAULT,". $strings[$i] . "),\n";
            }
        }

        if ($verboso == true) {
            verboso($query);
        }

        $sucesso = verifica_query($conexao, $query);

        return $sucesso;
    }
