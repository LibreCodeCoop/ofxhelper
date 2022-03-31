# Converte JSON do PagSeguro para OFX

Passos:

Baixe o JSON do intervalo de dada desejado, a URL é a que segue.

Exemplo:

https://api.ibanking.pagseguro.uol.com.br/checkingaccount/v2/statement?initialDate=2022-02-01&finalDate=2022-02-28&page=1

Você pode encontrar a requisição para a URL acima indo no Network Manager em https://minhaconta.pagseguro.uol.com.br/extratos/conta

Substitua no comando que segue onde está numerodaconta pelo número da sua conta no PagSeguro.

Pegue o conteúdo do json e salve em um arquivo de entrada com extensão `.json`, pode ser por exemplo seguindo o formato de nome MMDD.json (MM= mês com 2 dítigos, DD = dia com dois dígigos).

Identifique qual nome deseja para a saída, pode ser o mesmo nome do arquivo de entrada só que com extensão ofx.

```bash
docker-compose up -d
docker-comopse exec php bash
php pagseguro.php numerodaconta inputfile.json > output.ofx
```
Fim.