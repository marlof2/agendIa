# Como Aplicar Multi-Tenant nos Models

Exemplos práticos de como adicionar o sistema multi-tenant em seus models.

## 📝 Exemplo 1: Model Client (Clientes)

### Passo 1: Migration
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('cpf')->nullable();
            
            // ⭐ Campo obrigatório para multi-tenant
            $table->foreignId('company_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
```

### Passo 2: Model
```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant; // ← Importar trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant; // ← Usar trait
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf',
        'company_id', // ← Incluir no fillable
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Relacionamento com Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
```

### Passo 3: Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        // Automaticamente filtra pela empresa atual
        $clients = Client::paginate(10);
        
        // Se for admin e quiser ver tudo
        if ($request->user()->isAdmin() && $request->get('all_tenants')) {
            $clients = Client::withoutTenant()->paginate(10);
        }
        
        return response()->json($clients);
    }
    
    public function store(Request $request)
    {
        // company_id é adicionado automaticamente
        $client = Client::create($request->validated());
        
        return response()->json($client, 201);
    }
}
```

## 📝 Exemplo 2: Model Appointment (Agendamentos)

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes, BelongsToTenant;
    
    protected $fillable = [
        'client_id',
        'professional_id',
        'service_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'company_id', // ← Campo obrigatório
    ];
    
    // Relacionamentos
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
```

## 📝 Exemplo 3: Model Service (Serviços)

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes, BelongsToTenant;
    
    protected $fillable = [
        'name',
        'description',
        'duration', // em minutos
        'price',
        'is_active',
        'company_id',
    ];
    
    protected $casts = [
        'duration' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
```

## 🔧 Migrations para Adicionar company_id em Tabelas Existentes

Se você já tem tabelas criadas, use estas migrations:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar company_id em tabela existente
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('company_id')
                  ->nullable() // Temporariamente nullable
                  ->after('id')
                  ->constrained()
                  ->onDelete('cascade');
        });
        
        // Popular company_id (exemplo: pegar primeira empresa)
        \DB::statement('UPDATE clients SET company_id = (SELECT id FROM companies LIMIT 1) WHERE company_id IS NULL');
        
        // Remover nullable
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
```

## 🎯 Casos de Uso Comuns

### 1. Listar Dados (Filtrado Automaticamente)
```php
// No controller ou service
$data = Client::with('company')->paginate(10);
// Retorna apenas clientes da empresa atual
```

### 2. Admin Ver Todas as Empresas
```php
if ($user->isAdmin()) {
    $data = Client::withoutTenant()->paginate(10);
}
```

### 3. Criar Registro
```php
// company_id é adicionado automaticamente
$client = Client::create([
    'name' => 'João Silva',
    'email' => 'joao@example.com',
]);
// company_id = tenant_id atual
```

### 4. Relatórios Consolidados (Admin)
```php
$report = bypass_tenant_scope(function() {
    return DB::table('clients')
        ->select('company_id', DB::raw('COUNT(*) as total'))
        ->groupBy('company_id')
        ->get();
});
```

### 5. Export com Filtro por Empresa
```php
public function export(Request $request)
{
    $query = Client::query();
    
    // Admin pode exportar de empresa específica
    if ($request->user()->isAdmin() && $request->company_id) {
        $query->forTenant($request->company_id);
    }
    
    return Excel::download(new ClientsExport($query->get()), 'clients.xlsx');
}
```

## ⚙️ Configuração Avançada

### Desabilitar Temporariamente
```php
// Em um comando artisan, seeder, ou teste
app()->instance('tenant_id', null);
// Queries não terão filtro de tenant
```

### Forçar Tenant Específico
```php
app()->instance('tenant_id', 5);
// Todas as queries filtrarão por company_id = 5
```

### Em Testes
```php
public function test_client_belongs_to_tenant()
{
    app()->instance('tenant_id', 1);
    
    $client = Client::factory()->create();
    
    $this->assertEquals(1, $client->company_id);
}
```

## 🐛 Troubleshooting

### Problema: Query não filtra por tenant
**Solução:** Verifique se:
1. A trait `BelongsToTenant` está no model
2. O middleware está registrado
3. O frontend está enviando `X-Tenant-ID`
4. A tabela tem o campo `company_id`

### Problema: Erro "company_id cannot be null"
**Solução:** 
- Certifique-se que o tenant_id está sendo passado
- Verifique se o middleware TenantScope está executando
- Em seeds/comandos, defina manualmente: `app()->instance('tenant_id', 1)`

### Problema: Admin não consegue ver outras empresas
**Solução:** Use `withoutTenant()` nas queries do admin:
```php
if ($user->isAdmin()) {
    $query->withoutTenant();
}
```

## 📚 Referências

- Trait: `app/Traits/BelongsToTenant.php`
- Scope: `app/Scopes/TenantScope.php`
- Middleware: `app/Http/Middleware/TenantScope.php`
- Helpers: `app/helpers.php`
- Documentação: `MULTI_TENANT.md`

