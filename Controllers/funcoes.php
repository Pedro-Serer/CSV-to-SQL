<?php
    /**
    * Arquivo com as Funções gerais.
    *
    * Esse arquivo contém todas as funções que o programa
    * irá usar.
    * @author Pedro Serer
    * @version 1.1.2
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


    function formata_nome_tabela ($diretorio)
    {
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
    * Método que cria o arquivo SQL.
    * O resultado será salvo na pasta "Arquivos-criados"
    * @param string $nome o nome de saída do arquivo.
    * @param string $conteudo o conteúdo do arquivo são
    * todas as querys.
    */

    function cria_arquivo ($nome, $conteudo)
    {
        $arquivo = fopen("../Arquivos criados" . BARRA . $nome . ".sql", "a+");

        if (!$arquivo) {
            die("\nErro ao criar o arquivo\n");
        }

        fwrite($arquivo, $conteudo);
        fclose($arquivo);
    }
