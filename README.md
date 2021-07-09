# Lembretes

O app lembretes foi criado com o objetivo de ajudar as pessoas a nunca mais perderem compromissos importantes. No app será possível registrar um compromisso e ser lembrado desse compromisso períodicamente.

Estamos precisando da sua ajuda para construir a API do app lembretes. Sua tarefa será criar uma API que será consumida por uma aplicação cliente que está sendo desenvolvida por outro time.

A api será responsável pela criação de lembretes, listagem dos lembrentes, resolver um lembrete de um período, apagar um lembrete e notificar um usuário de um compromisso no dia de vencimento.

## Requisitos
- Deve ser possível criar um lembrete para qualquer data, passado, presente ou futuro. (Não deve ser informado a hora do lembrete)
- O lembrete deve ter um título, descrição e nome do usuário <username>
- O lembrete deve ter a períodicidade em que ele acontece; 
  - Lembretes recorrentes podem ser configurados para qualquer período: segundo, minuto, hora, dia, semana, mês, ano ou customizado. Mas para esse teste preocupe-se apenas em **recorrência mensal** ou lembrete **não recorrente**).
  - Exemplo: compromissos podem acontecer mensalamente todo dia 5, ou apenas uma vez no dia 14/10/21.
- Deve ser possível informar que um lembrete de um determinado período foi resolvido.
- Listagem de lembretes
  - Deve ser possível listar os lembretes que estão pendentes em um período.
  - Deve ser possível listar os lembretes resolvidos de um período.
- O app deve notificar um usuário que ele tem um compromisso na data do lembrete , caso ele não tenha sido marcado como resolvido. A notificação deve ocorrer sempre às 8 horas da manhã (padrão), preocupe-se apenas com o dia do lembrete.
  - Ps: Basta escrever num arquivo de log. Sugestão de formato: `<data lembrete>: <username> <título lembrete> <descrição lembrete>`
- Deve ser possível apagar um lembrete. Lembretes apagados devem:
  - Continuar sendo listados em períodos anteriores à data em que foi apagado
  - Não devem ser listados em períodos futuros à data em que foi apagado
  - Não deve notificar o usuário do compromisso em períodos futuros
  
## Requisitos não funcionais
Espera-se que um grande número de usuários utilize o app. Assim, se preocupe em fazer uma api confiável e resiliente.

## Cuidado no Overengineering
Não esperamos que você desenvolva nada além do que foi solicitado. Não desenvolva nenhuma solução de autenticação, registro de usuários, envio de emails ou qualquer outra coisa.

Crie uma solução simples, que seja fácil de ler, manter e adicionar funcionalidades.
  
  
--------
**Nosso objetivo é responder as seguintes perguntas:**

Você entende a linguagem PHP e o framework Laravel, e o ambiente de tecnologias web em geral?
Você é capaz de criar códigos limpos, que são fáceis de ler e alterar?
Você consegue escrever testes automatizado?
Qual seu nível de criação de abstrações?
