# CSV-to-SQL

## Conversor de arquivos CSV para SQL

O programa foi criado para ajudar na conversão de arquivos no formato CSV (texto) para a linguagem
SQL. Ele cria as tabelas e insere automaticamente os dados no banco. 

Está em sua primeira versão, é muito simples e fácil de usar, basta ter o PHP instalado e rodar o programa pela 
linha de comandos.

## Exemplo do uso:
- *~~php CSqlV.php obrigatorio:[caminho_do_CSV] opcional: -v[modo_verboso]~~*
- *php CSqlV.php obrigatorio:[caminho_do_CSV] opcional: ---file[cria_arquivo_sql]]*

### Atualização para a versão 1.1.2

- [x] Remoção do modo verboso;
- [x] Adição do modo de criação de arquivo .sql;
- [x] Adição do tempo de execução;
- [x] Remoção de Overflows de dados;
- [x] Performance excelente mantida.
