# ActionsMenu - Componente de Menu de Ações

Componente genérico para menu de ações com três pontos (⋮).

## Importação

```vue
<script setup>
import ActionsMenu from '@/components/ActionsMenu.vue'
</script>
```

## Uso Básico (Padrão)

```vue
<template>
  <v-data-table :items="items">
    <template v-slot:item.actions="{ item }">
      <!-- Menu com ações padrão: Visualizar, Editar, Excluir -->
      <ActionsMenu 
        :item="item"
        @view="handleView"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </template>
  </v-data-table>
</template>

<script setup>
const handleView = (item) => {
  console.log('Visualizar:', item)
}

const handleEdit = (item) => {
  console.log('Editar:', item)
}

const handleDelete = (item) => {
  console.log('Excluir:', item)
}
</script>
```

## Mostrar/Ocultar Ações Padrão

```vue
<template>
  <!-- Apenas visualizar e editar -->
  <ActionsMenu 
    :item="item"
    :show-delete="false"
    @view="handleView"
    @edit="handleEdit"
  />
  
  <!-- Apenas editar e excluir -->
  <ActionsMenu 
    :item="item"
    :show-view="false"
    @edit="handleEdit"
    @delete="handleDelete"
  />
  
  <!-- Apenas excluir -->
  <ActionsMenu 
    :item="item"
    :show-view="false"
    :show-edit="false"
    @delete="handleDelete"
  />
</template>
```

## Adicionar Ações Customizadas

```vue
<template>
  <ActionsMenu 
    :item="item"
    :custom-actions="customActions"
    @view="handleView"
    @edit="handleEdit"
    @delete="handleDelete"
    @action="handleCustomAction"
  />
</template>

<script setup>
import type { CustomAction } from '@/components/ActionsMenu.vue'

const customActions: CustomAction[] = [
  {
    key: 'duplicate',
    title: 'Duplicar',
    icon: 'mdi-content-copy',
    class: 'info-action'
  },
  {
    key: 'archive',
    title: 'Arquivar',
    icon: 'mdi-archive',
    class: 'secondary-action'
  },
  {
    key: 'export',
    title: 'Exportar',
    icon: 'mdi-download',
  }
]

const handleCustomAction = (key: string, item: any) => {
  switch (key) {
    case 'duplicate':
      console.log('Duplicar:', item)
      break
    case 'archive':
      console.log('Arquivar:', item)
      break
    case 'export':
      console.log('Exportar:', item)
      break
  }
}
</script>
```

## Customização Total com Slot

```vue
<template>
  <ActionsMenu :item="item">
    <!-- Slot customizado - substitui todas as ações padrão -->
    <v-list-item
      prepend-icon="mdi-star"
      title="Favoritar"
      @click="favorite(item)"
    />
    <v-list-item
      prepend-icon="mdi-share-variant"
      title="Compartilhar"
      @click="share(item)"
    />
    <v-divider />
    <v-list-item
      prepend-icon="mdi-download"
      title="Baixar"
      @click="download(item)"
    />
  </ActionsMenu>
</template>
```

## Customizar Aparência do Botão

```vue
<template>
  <!-- Botão maior -->
  <ActionsMenu 
    :item="item"
    size="default"
    @view="handleView"
  />
  
  <!-- Botão com cor -->
  <ActionsMenu 
    :item="item"
    color="primary"
    variant="tonal"
    @view="handleView"
  />
  
  <!-- Ícone diferente -->
  <ActionsMenu 
    :item="item"
    icon="mdi-menu"
    :icon-size="24"
    @view="handleView"
  />
</template>
```

## Exemplo Completo em Tabela

```vue
<template>
  <v-data-table 
    :items="companies" 
    :headers="headers"
  >
    <template v-slot:item.actions="{ item }">
      <ActionsMenu 
        :item="item"
        :custom-actions="companyActions"
        @view="viewCompany"
        @edit="editCompany"
        @delete="deleteCompany"
        @action="handleCompanyAction"
      />
    </template>
  </v-data-table>
</template>

<script setup>
import { ref } from 'vue'
import ActionsMenu, { type CustomAction } from '@/components/ActionsMenu.vue'

const companies = ref([
  { id: 1, name: 'Empresa A' },
  { id: 2, name: 'Empresa B' },
])

const companyActions: CustomAction[] = [
  {
    key: 'manage-users',
    title: 'Gerenciar Usuários',
    icon: 'mdi-account-multiple',
    class: 'primary-action'
  },
  {
    key: 'settings',
    title: 'Configurações',
    icon: 'mdi-cog',
  }
]

const viewCompany = (company) => {
  console.log('Ver:', company.name)
}

const editCompany = (company) => {
  console.log('Editar:', company.name)
}

const deleteCompany = (company) => {
  console.log('Excluir:', company.name)
}

const handleCompanyAction = (key: string, company: any) => {
  if (key === 'manage-users') {
    console.log('Gerenciar usuários de:', company.name)
  } else if (key === 'settings') {
    console.log('Configurações de:', company.name)
  }
}
</script>
```

## Props Disponíveis

| Prop | Tipo | Padrão | Descrição |
|------|------|--------|-----------|
| `item` | `any` | `undefined` | Item que será passado nos eventos |
| `showView` | `boolean` | `true` | Mostrar ação "Visualizar" |
| `showEdit` | `boolean` | `true` | Mostrar ação "Editar" |
| `showDelete` | `boolean` | `true` | Mostrar ação "Excluir" |
| `customActions` | `CustomAction[]` | `[]` | Ações customizadas adicionais |
| `icon` | `string` | `'mdi-dots-vertical'` | Ícone do botão |
| `size` | `string` | `'small'` | Tamanho do botão |
| `variant` | `string` | `'text'` | Variante do botão |
| `color` | `string` | `'default'` | Cor do botão |
| `iconSize` | `string\|number` | `20` | Tamanho do ícone |
| `btnClass` | `string` | `'action-btn'` | Classes CSS do botão |
| `offset` | `string\|number` | `8` | Offset do menu |
| `density` | `string` | `'compact'` | Densidade da lista |

## Eventos

| Evento | Payload | Descrição |
|--------|---------|-----------|
| `@view` | `item` | Disparado ao clicar em "Visualizar" |
| `@edit` | `item` | Disparado ao clicar em "Editar" |
| `@delete` | `item` | Disparado ao clicar em "Excluir" |
| `@action` | `key, item` | Disparado ao clicar em ação customizada |

## Interface CustomAction

```typescript
interface CustomAction {
  key: string        // Identificador único da ação
  title: string      // Título exibido
  icon: string       // Ícone MDI
  class?: string     // Classes CSS opcionais
}
```

## Classes CSS Disponíveis

- `primary-action` - Cor info (azul)
- `warning-action` - Cor warning (laranja)
- `danger-action` - Cor error (vermelho)
- `info-action` - Cor info (azul)
- `secondary-action` - Cor padrão

## Dicas

1. Use `:show-view="false"` para ocultar ações que não fazem sentido
2. Adicione ações customizadas com `custom-actions` em vez de reescrever tudo
3. Use o slot apenas quando precisar de algo totalmente diferente
4. O `item` é automaticamente passado em todos os eventos
5. Classes CSS customizadas ajudam a diferenciar visualmente as ações

