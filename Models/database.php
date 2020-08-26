<?php
    /**
    * Arquivo com as Funções de banco
    *
    * Esse arquivo contém todas as conexões com o BD
    * @author Pedro Serer
    * @version 1.1.2
    */


    /**
    *     A linguagem usada por padrão é o MySQL.
    */


    //Configurações do banco de dados
    const SQL = [
       "HOST_BD" => "127.0.0.1",
       "USER_BD" => "root",
       "PASSWORD_BD" => "",
       "BANCO_DE_DADOS" => "CSVTeste"
    ];

    //Abre conexão com o banco
    $conexao = mysqli_connect(SQL["HOST_BD"], SQL["USER_BD"], SQL["PASSWORD_BD"], SQL["BANCO_DE_DADOS"]);

    /**
    * Método que verifica se houve erros de conexão com o BD.
    *
    * @param resource $conexao contém a configuração de conexao com
    * o banco de dados.
    */


    function verifica_conexao ($conexao)
    {
        if (!$conexao) {
            echo "\n\n";
            echo "Erro ao conectar com o banco de dados:\n";
            echo "\t" . mysqli_connect_error() . ".\n";
        }
    }

    /**
    * Função que verifica se houve erros de sintaxe na query.
    *
    * @param resource $conexao contém a configuração de conexao com
    * o banco de dados.
    * @param string $query contém a query a ser enviada para o banco.
    * @return $sucesso se caso tudo ocorrer bem a função retorna 1.
    */


    function verifica_query ($conexao, $query)
    {
        $sucesso = 0;
        if (!mysqli_query($conexao, $query)) {
            echo "\n\n";
            echo "#" . mysqli_errno($conexao) . " - Erro ao criar tabela:\n";
            echo "\t" . mysqli_error($conexao) . ".\n";
        } else {
            $sucesso = 1;
        }

      return $sucesso;
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


    function cria_tabela ($sql, $cabCSV, $nome, $tipo, $tamanho, $max, $arquivo = false, $conexao)
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

        if ($arquivo == true) {
            cria_arquivo($nome, $query);
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


    function insere_dados ($sql, $linhasCSV, $nome, $colunas, $tipo, $arquivo = false, $conexao)
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

        if ($arquivo == true) {
            cria_arquivo($nome, $query);
        }

        $sucesso = verifica_query($conexao, $query);

        return $sucesso;
    }

    return $conexao;
