# Componente `<claim-form>`
Adiciona botão para solicitação de recurso para inscrição ou fase

### Eventos
- **sent** - disparado quando a solicitação de recurso é enviada
  
## Propriedades
- *Entity **registration*** - a instância Entity da inscrição

### Importando componente
```PHP
<?php 
$this->import('claim-form');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<claim-form :registration="registration"></claim-form>

```