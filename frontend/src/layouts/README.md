# Sistema de Layouts - AgendIA

Este documento explica como usar o sistema de layouts no projeto AgendIA.

## Layouts Disponíveis

### 1. **default** (Layout Principal)
- **Arquivo**: `layouts/default.vue`
- **Uso**: Páginas do sistema que precisam de sidebar e app-bar
- **Inclui**: 
  - Sidebar com navegação
  - App-bar com ações do usuário
  - Área de conteúdo principal
- **Exemplos**: Dashboard, Agendamentos, Clientes, etc.

### 2. **public** (Layout Público)
- **Arquivo**: `layouts/public.vue`
- **Uso**: Páginas que não precisam de sidebar e app-bar
- **Inclui**: Apenas área de conteúdo com fundo gradiente
- **Exemplos**: Login, Registro, Recuperação de senha

### 3. **error** (Layout de Erro)
- **Arquivo**: `layouts/error.vue`
- **Uso**: Páginas de erro e acesso negado
- **Inclui**: Layout minimalista com fundo de erro
- **Exemplos**: 404, 500, Unauthorized

## Como Usar

### Definir Layout em uma Página

Adicione o bloco `<route>` no topo do seu componente Vue:

```vue
<route lang="yaml">
meta:
  layout: 'default'  # ou 'public' ou 'error'
  requiresAuth: true
  title: 'Nome da Página'
  description: 'Descrição da página'
</route>

<template>
  <!-- Seu conteúdo aqui -->
</template>
```

### Exemplos de Uso

#### Página do Sistema (com sidebar)
```vue
<route lang="yaml">
meta:
  layout: 'default'
  requiresAuth: true
  title: 'Dashboard'
</route>
```

#### Página Pública (sem sidebar)
```vue
<route lang="yaml">
meta:
  layout: 'public'
  requiresAuth: false
  title: 'Login'
</route>
```

#### Página de Erro
```vue
<route lang="yaml">
meta:
  layout: 'error'
  requiresAuth: false
  title: 'Página não encontrada'
</route>
```

## Estrutura dos Layouts

### Layout Default
```
┌─────────────────────────────────────┐
│ App Bar (TopbarActions)            │
├─────────┬───────────────────────────┤
│         │                           │
│ Sidebar │     Conteúdo Principal    │
│         │     (router-view)         │
│         │                           │
└─────────┴───────────────────────────┘
```

### Layout Public/Error
```
┌─────────────────────────────────────┐
│                                     │
│                                     │
│        Conteúdo Centralizado        │
│        (router-view)                │
│                                     │
│                                     │
└─────────────────────────────────────┘
```

## Customização

### Adicionar Novo Layout

1. Crie um novo arquivo em `src/layouts/meu-layout.vue`
2. Use o layout nas páginas com `layout: 'meu-layout'`

```vue
<!-- src/layouts/meu-layout.vue -->
<template>
  <v-app>
    <!-- Seu layout personalizado -->
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>
```

### Modificar Layout Existente

Edite diretamente os arquivos em `src/layouts/`:
- `default.vue` - Layout principal
- `public.vue` - Layout público
- `error.vue` - Layout de erro

## Integração com Guards

Os layouts funcionam perfeitamente com o sistema de guards:

```vue
<route lang="yaml">
meta:
  layout: 'default'
  requiresAuth: true
  title: 'Configurações'
</route>
```

O sistema automaticamente:
1. Verifica autenticação
2. Verifica permissões
3. Aplica o layout correto
4. Redireciona se necessário

## Responsividade

Todos os layouts são responsivos e se adaptam a diferentes tamanhos de tela:
- **Desktop**: Layout completo com sidebar
- **Tablet**: Sidebar colapsível
- **Mobile**: Sidebar em overlay

## Temas

Os layouts suportam temas claro e escuro automaticamente através do Vuetify.