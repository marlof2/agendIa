# Sistema Multi-Tenant - AgendIA

Documentação completa do sistema multi-tenant implementado.

## 🏗️ Arquitetura

O sistema multi-tenant do AgendIA permite que:
- ✅ Múltiplas empresas usem o mesmo sistema
- ✅ Dados sejam isolados por empresa (tenant)
- ✅ Usuários possam acessar múltiplas empresas
- ✅ Admin veja e gerencie todas as empresas
- ✅ Filtros automáticos sejam aplicados

## 📦 Componentes Implementados

### 1. **Middleware - `TenantScope`**
Captura o `tenant_id` de cada requisição e o disponibiliza globalmente.

**Localização:** `app/Http/Middleware/TenantScope.php`

**Como funciona:**
- Lê o header `X-Tenant-ID` enviado pelo frontend
- Se não houver, pega a primeira empresa do usuário autenticado
- Armazena em `app('tenant_id')` para uso global

### 2. **Global Scope - `TenantScope`**
Aplica filtro `WHERE company_id = ?` automaticamente em todas as queries.

**Localização:** `app/Scopes/TenantScope.php`

### 3. **Trait - `BelongsToTenant`**
Trait para aplicar nos models que precisam de isolamento por tenant.

**Localização:** `app/Traits/BelongsToTenant.php`

**Funcionalidades:**
- Aplica o Global Scope automaticamente
- Adiciona `company_id` ao criar novos registros
- Fornece métodos úteis: `withoutTenant()`, `forTenant()`

### 4. **Helper Functions**
Funções auxiliares para trabalhar com tenants.

**Localização:** `app/helpers.php`

**Funções disponíveis:**
- `tenant_id()` - Retorna o tenant_id atual
- `is_admin()` - Verifica se usuário é admin
- `bypass_tenant_scope()` - Executa código sem filtro de tenant

## 🎯 Como Usar

### Aplicar Multi-Tenant em um Model

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use BelongsToTenant; // ← Adiciona esta linha
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_id', // ← Campo obrigatório
    ];
}
```

**Importante:** A tabela PRECISA ter o campo `company_id`.

### Migration Exemplo

```php
Schema::create('clients', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('phone');
    $table->foreignId('company_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    $table->softDeletes();
});
```

### Queries Automáticas

```php
// Busca apenas clientes da empresa atual
$clients = Client::all();
// SELECT * FROM clients WHERE company_id = ?

// Criar cliente (company_id adicionado automaticamente)
$client = Client::create([
    'name' => 'João',
    'email' => 'joao@example.com',
]);
// company_id é preenchido automaticamente

// Atualizar
$client->update(['name' => 'João Silva']);
// Mantém o company_id original

// Deletar
$client->delete();
// Deleta apenas se pertencer ao tenant atual
```

### Queries Especiais (Admin)

```php
// Ver dados de todas as empresas (ignora filtro)
$allClients = Client::withoutTenant()->get();

// Ver dados de uma empresa específica
$clientsEmpresa2 = Client::forTenant(2)->get();

// Executar operação sem filtro de tenant
$result = bypass_tenant_scope(function() {
    return Client::all(); // Retorna de todas as empresas
});
```

## 🔒 Controle de Acesso

### No Controller

```php
public function index(Request $request)
{
    // Para usuários normais, vê apenas da empresa atual
    $clients = Client::paginate(10);
    
    // Admin pode ver todas
    if ($request->user()->isAdmin()) {
        $clients = Client::withoutTenant()->paginate(10);
    }
    
    return response()->json($clients);
}
```

### No Service

```php
public function getAllClients(?User $user = null)
{
    $query = Client::query();
    
    // Se for admin, remove o filtro de tenant
    if ($user && $user->isAdmin()) {
        $query->withoutTenant();
    }
    
    return $query->paginate(10);
}
```

## 📋 Models que DEVEM usar BelongsToTenant

Aplicar a trait `BelongsToTenant` nos seguintes models:

- ✅ **Client** (Clientes)
- ✅ **Appointment** (Agendamentos)
- ✅ **Service** (Serviços)
- ✅ **Schedule** (Horários/Agenda)
- ✅ **Holiday** (Feriados/Bloqueios)
- ✅ **Notification** (Notificações)

**NÃO aplicar em:**
- ❌ **User** (Usuários podem pertencer a múltiplas empresas)
- ❌ **Company** (É a própria empresa/tenant)
- ❌ **Profile** (Perfis são globais)
- ❌ **Ability** (Habilidades são globais)
- ❌ **Timezone** (Fusos horários são globais)

## 🔄 Fluxo Completo

```
1. Frontend → Envia header X-Tenant-ID
   ↓
2. Middleware TenantScope → Captura tenant_id
   ↓
3. app('tenant_id') → Armazena globalmente
   ↓
4. Models com BelongsToTenant → Aplicam filtro automaticamente
   ↓
5. Query → WHERE company_id = tenant_id
   ↓
6. Resultado → Apenas dados da empresa atual
```

## 🛠️ Helpers Úteis

### Verificar Tenant Atual
```php
$currentTenantId = tenant_id();

if ($currentTenantId) {
    // Há um tenant ativo
}
```

### Verificar se é Admin
```php
if (is_admin()) {
    // Código para admin
}
```

### Executar sem Filtro de Tenant
```php
$allData = bypass_tenant_scope(function() {
    return Client::with('company')->get();
});
```

## 🎨 Frontend

### Header Automático
O `useHttp` já está configurado para enviar automaticamente:

```typescript
headers: {
  'X-Tenant-ID': localStorage.getItem('agendia-tenant-id')
}
```

### Trocar de Empresa
Quando o usuário troca de empresa:

```typescript
const { switchTenant } = useTenant()
await switchTenant(empresa)
// Próximas requisições usarão o novo tenant_id
```

## ⚠️ Importantes

1. **Sempre adicione `company_id` nas migrations**
2. **Aplique a trait apenas em models que pertencem a uma empresa**
3. **Admin pode ver tudo usando `withoutTenant()`**
4. **Teste bem as queries antes de deploy**
5. **Em desenvolvimento, verifique os logs SQL**

## 🧪 Testando

### Teste 1: Filtro Automático
```php
// No Tinker
$tenantId = 1;
app()->instance('tenant_id', $tenantId);

$clients = Client::all();
// Deve retornar apenas clientes da empresa 1
```

### Teste 2: Criar Registro
```php
app()->instance('tenant_id', 1);

$client = Client::create(['name' => 'Teste']);
echo $client->company_id; // Deve ser 1
```

### Teste 3: Admin sem Filtro
```php
$allClients = Client::withoutTenant()->get();
// Retorna clientes de todas as empresas
```

## 🚀 Deploy

Após aplicar em produção:

1. ✅ Rodar migrations (adicionar company_id nas tabelas)
2. ✅ Popular company_id nos registros existentes
3. ✅ Testar com diferentes usuários e empresas
4. ✅ Verificar que admin consegue ver tudo
5. ✅ Verificar que usuários comuns veem apenas suas empresas

## 📚 Exemplos Práticos

Ver arquivo: `app/Models/Client.php` (exemplo de implementação)

