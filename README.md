<div align="center">
    <h1 align="center">Teste Backend</h1>
</div>

## A dinâmica

Esse é o teste de backend.
Ele consiste em uma API para calcular o desconto de um carrinho de compras :moneybag:

O teste foi pensado para ser rápido e dinâmico.
Por isso, toda a base já foi criada e você só deverá se preocupar com o desafio de fato.

Nosso time de backend criou esse repositório base com [PHP 8.1](https://www.php.net/releases/8_1_0.php), [Laravel 9.1](https://laravel.com/docs/9.x/).
Não configuramos nenhum banco de dados e esperamos que você não utilize nenhum.

## O Desafio

Temos uma rota `/cart/discount` que recebe um usuário e os produtos do carrinho.
O objetivo dela é o de calcular adequadamente o desconto dos produtos com base nas regras descritas abaixo.

Essa rota **já realiza o cálculo do valor total** dos itens.
E também já existem alguns testes unitários e funcionais dessa lógica.
Porém ela **não realiza nenhum cálculo de desconto** e esse será **seu desafio**.

A API deve suportar **cinco** tipos de desconto:

**1. Desconto de porcentagem com base no valor total**

Oferecemos `15%` de desconto para carrinhos a partir de `R$3000,00`.

**2. Desconto de quantidade do mesmo item**

A cada duas unidades compradas de certos produtos, a terceira unidade será gratuita, ou seja leve 3, pague 2.
Isso vale para múltiplos também. Levando 9 unidades por exemplo, o cliente pagará somente 6 unidades.
Os produtos que participam dessa promoção podem ser consultados através da config [api.php](config/api.php).

**3. Desconto de porcentagem no item mais barato de uma mesma categoria**

Ao comprar dois ou mais produtos **diferentes** de uma determinada categoria,
somente uma unidade do produto mais barato dessa categoria deve receber `40%` de desconto.
As categorias determinadas podem ser consultadas através da config [api.php](config/api.php).

**4. Desconto de porcentagem para colaboradores**

Um usuário que seja colaborador tem `20%` de desconto no total do carrinho.

**5. Desconto em valor para novos usuários**

Caso seja a primeira compra, o usuário tem um desconto fixo de `R$25,00` em compras acima de `R$50,00`.
A rota `/user/{email}` retorna 404 caso o usuário não exista e se ele não existe ele é considerado um novo usuário.

#### Importante

Esses descontos **não são cumulativos**, então **somente o maior desconto** para o cliente deverá ser considerado.
É necessário indicar na API qual foi o desconto aplicado.

Nos testes é possível ver quais IDs esperamos que tenha desconto ou não.

Para saber se determinado usuário existe e é colaborador, possuímos uma rota `/user/{email}`.
Ela já está implementada e deverá ser utilizada **como se fosse uma API externa**, de outra aplicação/serviço.
Ou seja, você deverá **fazer um _request_** para essa API.
Caso este serviço esteja indisponível, nenhum desconto de usuário deverá ser aplicado.

## Começando

Clone este repositório, crie uma nova _branch_, como por exemplo `challenge`.

Na sua máquina, você só precisa ter o `PHP 8.1` e o `composer` instalados.

Em seguida, será necessário instalar as dependências do projeto:

```bash
composer install
```

Levante o servidor:
```bash
php artisan serve
```

A partir daqui, está tudo configurado :rocket:

Assim, será possível acessar [http://localhost:8000](http://localhost:8000) - ou como preferir - e ver a documentação da API.

Para começar a fazer o teste, após ler a documentação da API,
o primeiro passo é dar uma olhada no arquivo [CartDiscountTest.php](tests/Feature/API/V1/Cart/CartDiscountTest.php) e
nas [fixtures](tests/Feature/API/V1/Cart/fixtures) relacionadas a esse teste.
Existem algumas linhas comentadas, que são testes que estão falhando,
então é seu trabalho escrever o código necessário para esses testes passarem.
Teste descomentar uma linha por vez, para você seguir um fluxo mais no estilo [TDD](https://pt.wikipedia.org/wiki/Test_Driven_Development).

## Testando

Para rodar os testes da aplicação, utilize o [phpunit](https://phpunit.de/), que já vem instalado:

```bash
composer test
```

Você vai ver os testes que passam e também os que falham.
Em seguida, é hora de acessar o [CartsController](app/Http/Controllers/CartsController.php)
e ver a lógica inicial que nós criamos.

A partir daí, é com você :sunglasses:

---

Caso deseje rodar todas as checagens de qualidade de código que rodam no [CI](.github/workflows/laravel.yml), rode o comando abaixo:

```bash
./vendor/bin/grumphp run
```

Para ativar essas checagens automaticamente a cada commit, utilize o `git:init` do _grumphp_:

```bash
./vendor/bin/grumphp git:init
```

Para checar em detalhes a cobertura de código da aplicação, após rodar o _grumphp_,
abra o arquivo `build/coverage/index.html` em seu navegador.

## Hooks (Opcional)

Há dois hooks, configurados para os commits/pushes, que executarão verificações nos testes e formatação do código. Caso queira que sejam executados no seus commits e pushes rode o seguinte comando:

```bash
npm install
```

## O que estamos procurando

Esse desafio visa avaliar sua habilidade de escrever regras de negócio de forma clara e casos de teste (unitários e funcionais),
com código que seja de fácil entendimento para outras pessoas.
Também esperamos que sua solução seja resiliente e escalável.

Sua tarefa consiste em:

- Escrever código bem estruturado, seguindo boas práticas, para fazer os testes que estão falhando passarem.
- Escrever testes unitários para cobrir o código novo criado por você.
- Se julgar necessário, escrever mais testes funcionais para cobrir casos não previstos.
- Escrever mensagens de _commit_ claras e e concisas.
- Seguir os padrões [PSR-12](https://www.php-fig.org/psr/psr-12/) e passar nos _checks_ do CI.

Lembre-se de que o código que fornecemos é apenas uma base e você poderá alterá-lo como julgar necessário.

## Entregando o teste

Você deverá abrir um _Pull Request_ do GitHub com a sua solução.
Caso você não consiga fazer todos os requisitos que pedimos, não tem problema algum.
Ao entregar, deixe explicado o que você não conseguiu fazer e o que mais achar que faz sentido.

## Dúvidas

O teste foi feito para intencionalmente deixar algumas coisas em aberto.
No entanto, sinta-se livre para tirar dúvidas a qualquer momento.
Para isso, abra _issues_ aqui mesmo no repositório e responderemos assim que possível :nerd_face:

Boa sorte! :four_leaf_clover:
