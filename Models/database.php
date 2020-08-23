<?php
    /**
    * Arquivo com as Funções de banco
    *
    * Esse arquivo contém todas as conexões com o BD
    * @author Pedro Serer
    * @version 1.0.
    */


    //Configurações do banco de dados
    const SQL = [
       "HOST_BD" => "127.0.0.1",
       "USER_BD" => "root",
       "PASSWORD_BD" => "",
       "BANCO_DE_DADOS" => "CSVTeste"
    ];

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
