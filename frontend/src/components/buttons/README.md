# Componentes de Botões

Esta pasta contém componentes de botões reutilizáveis para todo o sistema.

## Componentes Disponíveis

### BtnSearch
Botão para ações de busca/filtro
```vue
<BtnSearch @click="performSearch" />
<BtnSearch text="Filtrar" color="info" @click="filterData" />
```

### BtnSave
Botão para salvar/atualizar dados
```vue
<BtnSave @click="saveData" />
<BtnSave :is-editing="true" @click="updateData" />
```

### BtnCancel
Botão para cancelar/fechar
```vue
<BtnCancel @click="closeModal" />
<BtnCancel text="Voltar" @click="goBack" />
```

### BtnEdit
Botão para editar
```vue
<BtnEdit @click="editItem" />
<BtnEdit :icon-only="true" @click="editItem" />
```

### BtnDelete
Botão para excluir
```vue
<BtnDelete @click="deleteItem" />
<BtnDelete :icon-only="true" @click="deleteItem" />
```

### BtnNew
Botão para criar novo item
```vue
<BtnNew @click="createNew" />
<BtnNew text="Adicionar" @click="addItem" />
```

### BtnExport
Botão para exportar dados
```vue
<BtnExport @click="exportData" />
<BtnExport text="Download" @click="downloadFile" />
```

### BtnFilter
Botão para limpar filtros
```vue
<BtnFilter @click="clearFilters" />
<BtnFilter text="Resetar" @click="resetFilters" />
```

### BtnView
Botão para visualizar
```vue
<BtnView @click="viewItem" />
<BtnView :icon-only="true" @click="viewItem" />
```

## Props Comuns

Todos os botões compartilham as seguintes props:

- `text`: Texto do botão
- `color`: Cor do botão (primary, secondary, success, info, warning, error)
- `variant`: Variante do botão (flat, outlined, text, plain, elevated, tonal)
- `size`: Tamanho do botão (x-small, small, default, large, x-large)
- `prependIcon`: Ícone antes do texto
- `appendIcon`: Ícone depois do texto
- `loading`: Estado de carregamento
- `disabled`: Estado desabilitado
- `rounded`: Bordas arredondadas
- `buttonClass`: Classes CSS adicionais

## Uso em Modais

Para modais, use BtnSave e BtnCancel juntos:

```vue
<template #actions>
  <BtnCancel @click="closeModal" />
  <BtnSave :loading="loading" :disabled="!isValid" @click="handleSubmit" />
</template>
```

## Uso em Cards

Para cards, use BtnView, BtnEdit e BtnDelete:

```vue
<v-card-actions>
  <BtnView @click="view(item)" />
  <BtnEdit @click="edit(item)" />
  <v-spacer />
  <BtnDelete :icon-only="true" @click="delete(item)" />
</v-card-actions>
```
