# Gerador e Enviador de Certificados de Eventos

Este projeto é uma aplicação web em PHP projetada para automatizar o processo de criação e envio de certificados de participação para eventos. Administradores podem selecionar um evento através de uma interface simples, e o sistema gera certificados em PDF para os participantes elegíveis, enviando-os automaticamente por e-mail.

## Funcionalidades Principais

*   **Seleção de Evento via Web:** Interface amigável para escolher para qual evento os certificados devem ser gerados.
*   **Geração Dinâmica de PDF:** Criação de certificados personalizados em formato PDF. Cada certificado inclui o nome do participante, nome do evento, data e a organização responsável.
*   **Envio Automatizado por E-mail:** Utiliza a biblioteca PHPMailer para enviar os certificados em PDF como anexo diretamente para os e-mails dos participantes.
*   **Critério de Elegibilidade:** O sistema verifica a elegibilidade dos participantes com base em regras de negócio, como a presença mínima em atividades. No código, o critério é ter participado de pelo menos 3 atividades do evento.
*   **Feedback de Envio:** Exibe um status de sucesso ou erro para cada e-mail enviado, permitindo acompanhar o processo.

## Tecnologias Utilizadas

*   **Linguagem:** PHP
*   **Banco de Dados:** MySQL (acessado via PDO)
*   **Bibliotecas PHP:**
    *   FPDF: Para a geração de arquivos PDF.
    *   PHPMailer: Para o envio de e-mails via SMTP.

## Estrutura do Banco de Dados

O sistema requer a seguinte estrutura de banco de dados para funcionar corretamente.

### Tabela: `usuario`

```sql
CREATE TABLE usuario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  login VARCHAR(100) NOT NULL UNIQUE,
  senha VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  admin TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabela: `evento`

```sql
CREATE TABLE evento (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  data_inicio DATE NOT NULL,
  data_final DATE NOT NULL,
  local VARCHAR(100) NOT NULL,
  organizacao VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabela: `atividade`

```sql
CREATE TABLE atividade (
  id INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(200) NOT NULL,
  responsavel VARCHAR(100) NOT NULL,
  data DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fim TIME NOT NULL,
  local VARCHAR(100) NOT NULL,
  tipo VARCHAR(50) NOT NULL,
  id_evento INT NOT NULL,
  FOREIGN KEY (id_evento) REFERENCES evento(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabela: `usuario_atividade` (presenças)

```sql
CREATE TABLE usuario_atividade (
  id_usuario INT NOT NULL,
  id_atividade INT NOT NULL,
  data DATE NOT NULL,
  hora TIME NOT NULL,
  presenca BOOLEAN NOT NULL,
  PRIMARY KEY (id_usuario, id_atividade),
  FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
  FOREIGN KEY (id_atividade) REFERENCES atividade(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Pré-requisitos

*   Servidor Web (Apache, Nginx, etc.)
*   PHP (versão 7.4 ou superior recomendada)
*   Servidor de Banco de Dados MySQL ou MariaDB

## Instalação e Configuração

1.  **Copiar os Arquivos:** Faça o download ou clone o repositório para o diretório raiz do seu servidor web (ex: `/var/www/html/` ou `htdocs/`).

2.  **Criar o Banco de Dados:** Use um cliente de banco de dados (como phpMyAdmin) para criar a base de dados (`eventq`) e as tabelas usando os scripts SQL fornecidos acima.

3.  **Configurar a Conexão com o Banco de Dados:**

    Abra o arquivo `conexao.php`.
    Altere os valores de `host`, `dbname`, `usuário` e `senha` para corresponderem às suas credenciais do MySQL.

    ```php
    // Arquivo: conexao.php
    $conn = new PDO("mysql:host=localhost;dbname=eventq;charset=utf8mb4", "root", "sua_senha");
    ```

4.  **Configurar o Serviço de E-mail:**

    Abra o arquivo `index.php`.
    Localize as configurações do PHPMailer.
    Insira as credenciais da conta de e-mail que fará o envio.

    ```php
    // Arquivo: index.php
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Altere se não for Gmail
    $mail->SMTPAuth = true;
    $mail->Username = 'seu_email@gmail.com'; // Insira seu e-mail
    $mail->Password = 'sua_senha_de_app'; // Insira sua senha
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    ```


## Como Usar

1.  **Povoar o Banco de Dados:** Certifique-se de que suas tabelas `evento`, `usuario`, `atividade` e `usuario_atividade` estejam preenchidas com os dados corretos. Para um usuário receber um certificado, ele deve ter `presenca = 1` nos registros da tabela `usuario_atividade`.

2.  **Acessar a Aplicação:** Abra seu navegador e navegue até a página `frontindex.php`.

    `http://localhost/caminho_do_projeto/frontindex.php`

3.  **Selecionar e Gerar:** Escolha um evento na lista suspensa e clique no botão "Gerar Certificados".

4.  **Verificar o Resultado:** A página `index.php` será carregada e exibirá o status de envio para cada participante elegível. Os participantes receberão os e-mails com o certificado em PDF anexado.

## Arquitetura do Código

*   `conexao.php`: Script responsável unicamente por estabelecer a conexão com o banco de dados.
*   `frontindex.php`: A interface do usuário (frontend). Exibe um formulário com a lista de eventos para o administrador escolher.
*   `index.php`: O script de processamento (backend). Recebe o ID do evento, busca os participantes elegíveis, gera o PDF para cada um e dispara os e-mails.


