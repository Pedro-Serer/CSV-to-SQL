<?php
    /**
    * Arquivo com as Funções gerais.
    *
    * Esse arquivo contém todas as funções que o programa
    * irá usar.
    * @author Pedro Serer
    * @version 1.0.0
    */

    error_reporting(0);

    const BARRA = DIRECTORY_SEPARATOR;
    const SQL = [
       "HOST_BD" => "127.0.0.1",
       "USER_BD" => "root",
       "PASSWORD_BD" => "",
       "BANCO_DE_DADOS" => "CSVTeste"
    ];

    $pathFiles = __DIR__ . BARRA . "php-excel-reader-2.21";
    require($pathFiles. BARRA . "excel_reader2.php");
    require($pathFiles. BARRA . "SpreadsheetReader.php");


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
    * ---------------------------------------------------------------------------------------------
    *                                     Funções de SQL
    * ---------------------------------------------------------------------------------------------
    */


    /**
    *     A linguagem usada por padrão é o MySQL.
    */



    /**
    * Método que cria a apresentação (log) do SQL
    * de criação das tabelas. É possível copiar e ajeitar
    * para a versão do gosto do usuário.
    *
    * @param array $cabCSV contém os valores do cabeçalho do arquivo CSV.
    * @param string $nome é o nome da tabela a ser criada. O valor contido
    * no retorno da função formata_nome_tabela().
    * @param array $tipo retorno da função tipo_de_dados() com os tipos de
    * dados referentes a cada coluna da tabela.
    * @param array $tamanho contém o retorno dos valores referentes a cada
    * tipo de dados, respectivamente.
    * @param int $max variável com a quantidade máxima de colunas que a
    * tabela irá conter.
    */


    function saida_cria_tabela ($cabCSV, $nome, $tipo, $tamanho, $max)
    {
        echo "\n\n";
        echo "--------------------------------------------------------------------------------\n";
        echo "CREATE TABLE `$nome`" . " (" . "\n";
        echo "\t> {$nome}_ID INT not null auto_increment,\n";

        for ($i=0; $i < $max; $i++) {
            $tamanho[$i] = $tamanho[$i] * 5;

            if ($cabCSV[$i] == null) {
                $cabCSV[$i] = "Adicional";
            }

            echo "\t> " . ltrim($cabCSV[$i]) . " $tipo[$i]($tamanho[$i]) not null,\n";
        }

        echo "\tPRIMARY KEY ({$nome}_ID)\n";
        echo ");\n";
        echo "--------------------------------------------------------------------------------\n";
    }

    /**
    * Método que cria a apresentação (log) do SQL
    * de inserção nas tabelas. É possível copiar e ajeitar
    * para a versão do gosto do usuário.
    *
    * @param array $linhasCSV contém os valores de cada registro a ser inserido.
    * @param string $nome é o nome da tabela a ser criada. O valor contido
    * no retorno da função formata_nome_tabela().
    * @param array $cabCSV contém os valores do cabeçalho do arquivo CSV.
    * @param int $max variável com a quantidade máxima de colunas que a
    * tabela irá conter.
    */


    function saida_insere_dados ($linhasCSV, $nome, $cabCSV, $max)
    {
        $maxLinhas = count($linhasCSV);
        $troca1 = [";", ",,"];
        $troca2 = [",", ",0,"];

        echo "\n\n";
        echo "--------------------------------------------------------------------------------\n";
        echo "INSERT INTO `$nome`" . "({$nome}_ID,";

        for ($i=0; $i < ($max - 1); $i++) {
            if ($i < ($max - 2)) {
                echo $cabCSV[$i] . ",";
            } else {
                echo $cabCSV[$i];
            }
        }
        echo ") VALUES\n";

        for ($i=0; $i < $maxLinhas; $i++) {
            if ($i == $maxLinhas - 1) {
                echo "\t (DEFAULT,". str_replace($troca1, $troca2, $linhasCSV[$i]) . ")\n";
            } else {
                echo "\t (DEFAULT,". str_replace($troca1, $troca2, $linhasCSV[$i]) . "),\n";
            }
        }

        echo "--------------------------------------------------------------------------------\n";
    }

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
                $tipo[$i] = "TinyInt";
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
    * @return $sucesso se caso tudo ocorrer bem a função retorna 1.
    */


    function cria_tabela ($sql, $cabCSV, $nome, $tipo, $tamanho, $max)
    {
        $conexao = mysqli_connect(
            $sql["HOST_BD"], $sql["USER_BD"], $sql["PASSWORD_BD"], $sql["BANCO_DE_DADOS"]
        );

        if (!$conexao) {
            echo "\n\n";
            echo "Erro ao conectar com o banco de dados:\n";
            echo "\t" . mysqli_connect_error() . ".\n";
        }


        $query = "CREATE TABLE $nome" . " (\n";
        $query .= "{$nome}_ID INT not null auto_increment,\n";

        for ($i=0; $i < $max; $i++) {
            $tamanho[$i] = $tamanho[$i] * 5;

            if ($cabCSV[$i] == null) {
                $cabCSV[$i] = "Adicional";
            }

            $query .= ltrim($cabCSV[$i]) . " $tipo[$i]($tamanho[$i]) not null,\n";
        }

        $query .= " PRIMARY KEY ({$nome}_ID)\n";
        $query .= ")";


        if (!mysqli_query($conexao, $query)) {
            echo "\n\n";
            echo "#" . mysqli_errno($conexao) . " - Erro ao criar tabela:\n";
            echo "\t" . mysqli_error($conexao) . ".\n";
        } else {
            $sucesso = 1;
        }

        mysqli_close($conexao);
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
    * @return $sucesso se caso tudo ocorrer bem a função retorna 1.
    */


    function insere_dados ($sql, $linhasCSV, $nome, $colunas, $tipo)
    {
        $maxColunas = count($colunas);
        $maxLinhas = count($linhasCSV);

        $conexao = mysqli_connect(
            $sql["HOST_BD"], $sql["USER_BD"], $sql["PASSWORD_BD"], $sql["BANCO_DE_DADOS"]
        );

        if (!$conexao) {
            echo "\n\n";
            echo "Erro ao conectar com o banco de dados:\n";
            echo "\t" . mysqli_connect_error() . ".\n";
        }

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
                $query .= "(DEFAULT,". $strings[$i] . ")\n";
            } else {
                $query .= "(DEFAULT,". $strings[$i] . "),\n";
            }
        }

        if (!mysqli_query($conexao, $query)) {
            echo "\n\n";
            echo "#" . mysqli_errno($conexao) . " - Erro ao inserir dados:\n";
            echo "\t" . mysqli_error($conexao) . ".\n";
        } else {
            $sucesso = 1;
        }

        mysqli_close($conexao);
        return $sucesso;
    }
