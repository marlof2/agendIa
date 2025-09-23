# Componentes Padronizados - AgendIA

Este diretório contém os componentes base reutilizáveis que seguem o design system do AgendIA.

## 🏗️ Estrutura de Layout Padrão

### BasePage.vue
Componente base para todas as páginas do sistema.

```vue
<BasePage
  title="Título da Página"
  subtitle="Subtítulo opcional"
  :breadcrumbs="breadcrumbs"
  :show-stats="true"
  :show-filters="true"
>
  <!-- Slots disponíveis -->
  <template #headerActions>...</template>
  <template #stats>...</template>
  <template #actionBar>...</template>
  <template #filters>...</template>
  <template #content>...</template>
</BasePage>
```

**Props:**
- `title` (string, obrigatório): Título da página
- `subtitle` (string, opcional): Subtítulo da página
- `breadcrumbs` (array, opcional): Array de breadcrumbs
- `showStats` (boolean, padrão: false): Mostrar seção de stats
- `showFilters` (boolean, padrão: false): Mostrar seção de filtros

### ActionBar.vue
Barra de ações padronizada com layout responsivo.

```vue
<ActionBar>
  <template #left>
    <!-- Ações do lado esquerdo -->
  </template>
  <template #right>
    <!-- Ações do lado direito -->
  </template>
</ActionBar>
```

### FiltersCard.vue
Card de filtros padronizado com animações.

```vue
<FiltersCard :show="showFilters">
  <template #filters>
    <!-- Campos de filtro -->
  </template>
  <template #actions>
    <!-- Ações dos filtros -->
  </template>
</FiltersCard>
```

**Props:**
- `show` (boolean, obrigatório): Controla visibilidade do card

### DataTable.vue
Tabela padronizada com suporte a mobile (cards).

```vue
<DataTable
  :headers="headers"
  :items="items"
  :search="searchQuery"
  :loading="loading"
  :items-per-page="10"
  no-data-text="Nenhum item encontrado"
  loading-text="Carregando..."
>
  <!-- Slots da tabela desktop -->
  <template v-slot:item.column="{ item }">...</template>
  
  <!-- Slot para cards mobile -->
  <template #mobile-card="{ item }">...</template>
</DataTable>
```

**Props:**
- `headers` (array, obrigatório): Cabeçalhos da tabela
- `items` (array, obrigatório): Dados da tabela
- `search` (string, opcional): Termo de busca
- `loading` (boolean, padrão: false): Estado de carregamento
- `itemsPerPage` (number, padrão: 10): Itens por página
- `noDataText` (string, padrão: 'Nenhum item encontrado'): Texto quando não há dados
- `loadingText` (string, padrão: 'Carregando...'): Texto de carregamento

## 🎨 Design System

### Variáveis SCSS
Arquivo: `src/styles/variables.scss`

**Breakpoints:**
- `$mobile: 480px`
- `$tablet: 768px`
- `$desktop: 1024px`
- `$wide: 1400px`

**Espaçamentos:**
- `$spacing-xs: 4px`
- `$spacing-sm: 8px`
- `$spacing-md: 16px`
- `$spacing-lg: 24px`
- `$spacing-xl: 32px`
- `$spacing-xxl: 48px`

**Transições:**
- `$transition-fast: 0.2s ease`
- `$transition-normal: 0.3s ease`
- `$transition-slow: 0.5s ease`

## 📱 Responsividade

### Desktop (≥768px)
- Layout horizontal
- Tabela com scroll
- Filtros em grid

### Mobile (<768px)
- Layout vertical
- Cards em vez de tabela
- Filtros empilhados
- Botões com largura total

## 🚀 Como Usar

### 1. Criar Nova Página
```vue
<template>
  <BasePage
    title="Minha Página"
    subtitle="Descrição da página"
    :breadcrumbs="[{ title: 'Minha Página' }]"
    :show-filters="true"
  >
    <template #actionBar>
      <ActionBar>
        <template #left>
          <v-btn>Filtros</v-btn>
        </template>
        <template #right>
          <v-btn color="success">Novo</v-btn>
        </template>
      </ActionBar>
    </template>

    <template #filters>
      <FiltersCard :show="showFilters">
        <template #filters>
          <!-- Seus filtros aqui -->
        </template>
        <template #actions>
          <!-- Ações dos filtros -->
        </template>
      </FiltersCard>
    </template>

    <template #content>
      <DataTable
        :headers="headers"
        :items="items"
      >
        <!-- Slots da tabela -->
      </DataTable>
    </template>
  </BasePage>
</template>
```

### 2. Aplicar em Página Existente
1. Importar os componentes necessários
2. Substituir estrutura atual pelos componentes
3. Mover lógica para os slots apropriados
4. Remover CSS desnecessário

## ✨ Benefícios

- **Consistência**: Layout padronizado em todas as páginas
- **Reutilização**: Componentes base reutilizáveis
- **Manutenibilidade**: Mudanças centralizadas
- **Responsividade**: Layout adaptativo automático
- **Performance**: Componentes otimizados
- **Acessibilidade**: Padrões de acessibilidade implementados

## 🔧 Customização

Para customizar estilos específicos de uma página:

```vue
<style scoped>
/* Apenas estilos específicos da página */
.minha-classe-especifica {
  /* estilos aqui */
}
</style>
```

Evite sobrescrever estilos dos componentes base. Use as props e slots disponíveis para customização.