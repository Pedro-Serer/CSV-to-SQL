<?php
    /**
    * Arquivo principal.
    *
    * Esse arquivo é o responsável pela execução do programa.
    * @author Pedro Serer
    * @version 1.0.0
    */

    require "../Controllers/funcoes.php";

    //Apresenta erro se caso o primeiro parâmetro for omitido.
    if (!isset($argv[1])) {
        die("\nPor favor, informe o caminho do arquivo CSV!\n");
    }

    if ($argc > 3) {
        echo "\n\n";
        echo "--------------------------------------------------------------------------------\n";
        echo "Numero de argumentos invalido!\n\n";
        echo "USO: \n";
        echo "php CSqlV.php obrigatorio:[caminho_do_CSV] opcional: -v[modo_verboso]\n";
        echo "--------------------------------------------------------------------------------\n";
        die;
    }

    $diretorio = $argv[1];
    $verboso   = false;

    /**
    * Verifica se o arquivo existe no caminho indicado. Se o
    * arquivo existir ele verifica se deve mostrar a saída,
    * se não existir o programa retorna uma mensagem de erro
    * e finaliza.
    */

    if (file_exists($diretorio)) {
        list($linhasCSV, $cabCSV) = excel($diretorio);

        $cabCSV = formata_cabecalho($cabCSV);
        $primeiraLinha = $linhasCSV[0];
        $max = count($cabCSV);

        list($tipo, $tamanho) = tipo_de_dados($primeiraLinha);


        $tabela = formata_nome_tabela($diretorio);

        if(isset($argv[2])){
            if ($argv[2] == "-v") {
                $verboso = true;
            } else {
                die("\nParametro com valor \"{$argv[2]}\" desconhecido\n");
            }
        }

        list($sucesso1, $colunas) = cria_tabela(SQL, $cabCSV, $tabela, $tipo, $tamanho, $max, $verboso);
        $sucesso2 = insere_dados(SQL, $linhasCSV, $tabela, $colunas, $tipo, $verboso);

        //Apresenta as mensagens de sucesso se tudo ocorrer bem.
        if ($sucesso1 == 1) {
            echo "\n\n";
            echo "Tabela criada com sucesso!\n";
        }

        if ($sucesso2 == 1) {
            echo "Dados inseridos com sucesso!\n";
        }
    } else {
        echo "\nArquivo nao existe!\n";
        $existe = false;
    }
