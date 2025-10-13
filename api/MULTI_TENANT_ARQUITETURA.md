# Arquitetura Multi-Tenant - AgendIA

## 🎯 Estrutura do Sistema

### Entidades Principais

```
┌─────────────────────────────────────────────────────────┐
│                         USER                            │
│  - id                                                   │
│  - name                                                 │
│  - email                                                │
│  - profile_id (admin, secretary, client, etc)          │
│  - NÃO TEM company_id direto                           │
└─────────────────────────────────────────────────────────┘
                         │
                         │ Many-to-Many
                         ↓
┌─────────────────────────────────────────────────────────┐
│                   COMPANY_USER (pivot)                  │
│  - user_id                                              │
│  - company_id                                           │
└─────────────────────────────────────────────────────────┘
                         │
                         ↓
┌─────────────────────────────────────────────────────────┐
│                       COMPANY                           │
│  - id                                                   │
│  - name                                                 │
│  - slug                                                 │
└─────────────────────────────────────────────────────────┘
```

## 📋 Models e Relacionamentos

### 1. **User** (NÃO usa BelongsToTenant)
```php
class User extends Model
{
    // Relacionamento many-to-many com Company
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
                    ->withTimestamps();
    }
    
    // Verifica se é admin
    public function isAdmin(): bool
    {
        return $this->profile && $this->profile->name === 'admin';
    }
}
```

### 2. **Company** (NÃO usa BelongsToTenant)
```php
class Company extends Model
{
    // Relacionamento many-to-many com User
    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user')
                    ->withTimestamps();
    }
}
```

### 3. **Appointment** (USA BelongsToTenant)
```php
use App\Traits\BelongsToTenant;

class Appointment extends Model
{
    use BelongsToTenant;
    
    protected $fillable = [
        'client_id',      // FK para User (cliente)
        'professional_id', // FK para User (profissional)
        'service_id',
        'date',
        'start_time',
        'company_id',     // ← PERTENCE a uma empresa
    ];
    
    // Cliente é um User com profile "client"
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    
    // Profissional é um User vinculado à empresa
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

### 4. **Service** (USA BelongsToTenant)
```php
use App\Traits\BelongsToTenant;

class Service extends Model
{
    use BelongsToTenant;
    
    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
        'company_id', // ← PERTENCE a uma empresa
    ];
}
```

## 🔍 Quando Usar BelongsToTenant

### ✅ **USA BelongsToTenant:**
- Appointment (agendamentos da empresa)
- Service (serviços oferecidos pela empresa)
- Schedule (horários de atendimento)
- Holiday (feriados/bloqueios da empresa)
- Notification (notificações específicas da empresa)

### ❌ **NÃO USA BelongsToTenant:**
- **User** - pode pertencer a múltiplas empresas (many-to-many)
- **Company** - é o próprio tenant
- **Profile** - perfis são globais do sistema
- **Ability** - habilidades são globais
- **Timezone** - fusos horários são globais

## 🎯 Filtros por Tipo

### Company (Filtro Manual)
```php
// CompanyService.php
public function getAllCompanies(?User $user = null)
{
    $query = Company::query();
    
    if ($user && !$user->isAdmin()) {
        // Filtra empresas onde o usuário está vinculado
        $companyIds = $user->companies()->pluck('companies.id')->toArray();
        $query->whereIn('id', $companyIds);
    }
    
    return $query->paginate();
}
```

### User (Filtro Manual Complexo)
```php
// UserService.php
public function getUsers(?User $authUser = null, ?int $companyId = null)
{
    $query = User::with('profile');
    
    if ($authUser && !$authUser->isAdmin()) {
        // Filtra usuários que estão nas mesmas empresas
        $companyIds = $authUser->companies()->pluck('companies.id')->toArray();
        $query->whereHas('companies', function($q) use ($companyIds) {
            $q->whereIn('companies.id', $companyIds);
        });
    }
    
    // Se especificar empresa, filtra por ela
    if ($companyId) {
        $query->whereHas('companies', function($q) use ($companyId) {
            $q->where('companies.id', $companyId);
        });
    }
    
    return $query->paginate();
}
```

### Appointment (Filtro Automático)
```php
// AppointmentService.php
public function getAllAppointments()
{
    // BelongsToTenant filtra automaticamente
    // WHERE company_id = tenant_id
    return Appointment::with(['client', 'professional'])
                      ->paginate();
}
```

## 💡 Exemplo Prático Completo

### Cenário: Buscar Agendamentos

```php
// AppointmentController.php
public function index(Request $request)
{
    // tenant_id vem do header X-Tenant-ID (empresa selecionada)
    
    // 1. Busca agendamentos da empresa atual
    $appointments = Appointment::with(['client', 'professional', 'service'])
                               ->paginate(10);
    // SQL: SELECT * FROM appointments WHERE company_id = {tenant_id}
    
    // 2. O client e professional são Users vinculados à empresa
    foreach ($appointments as $appointment) {
        echo $appointment->client->name;      // User com profile "client"
        echo $appointment->professional->name; // User com profile "professional"
    }
    
    return response()->json($appointments);
}
```

### Criar Agendamento

```php
public function store(Request $request)
{
    $appointment = Appointment::create([
        'client_id' => $request->client_id,      // User (cliente)
        'professional_id' => $request->professional_id, // User (profissional)
        'service_id' => $request->service_id,
        'date' => $request->date,
        'start_time' => $request->start_time,
        // company_id é adicionado AUTOMATICAMENTE pelo BelongsToTenant
    ]);
    
    return response()->json($appointment, 201);
}
```

## 🔑 Resumo

**Company:**
- ✅ Mantém filtro manual (relacionamento com User)
- ✅ Admin vê todas, outros veem apenas as suas

**User:**
- ✅ Filtro manual por relacionamento
- ✅ Sem company_id direto
- ✅ Many-to-many com Company

**Appointment, Service, etc:**
- ✅ Usam BelongsToTenant
- ✅ Têm company_id
- ✅ Filtro automático pelo tenant selecionado

Sua arquitetura está correta e as configurações são necessárias! 🎯

