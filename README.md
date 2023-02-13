# plugin-ClaimForm
Plugin que possibilita a solicitação de recurso com anexos

# Recursos do plugin
- possibilita incluir arquivos no recurso
- Enviar email com link do arquivo para o email configurado
- Enviar e-mail e certificado de solicitação de recurso ao proponente
- Possibilita definir data de início e fim do recurso
- Possibilita subir arquivos de exemplo do recurso
- possibilita aceite do arquivo, evitando que o proponente sobrescreva ou manipule o arquivo depois do início da avaliação do recurso

# Exemplo de configuração

```
'ClaimForm' => ['namespace' => 'ClaimForm']
```

# Requisitos para o plugin de recurso funcionar
- A oportunidade deve estar com as inscrições encerradas e o resultado publicado
- Deve ser configurado na edição da oportunidade, na aba Configuração do formulario, as informações de data de abertura, arquivo de exemplo, email que recebe os recursos e se o recurso esta habilidado ou não. Ver exemplo abaixo 

![image](https://user-images.githubusercontent.com/39862175/218552745-6a54778b-1af1-414c-9d96-50b5b869f2f8.png)

