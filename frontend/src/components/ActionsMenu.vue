<template>
  <v-menu
    location="bottom end"
    :offset="offset"
    transition="scale-transition"
    :close-on-content-click="closeOnClick"
  >
    <template v-slot:activator="{ props }">
      <v-btn
        v-bind="props"
        :icon="icon"
        :size="size"
        :variant="variant"
        :color="color"
        :class="['action-menu-btn', btnClass]"
      >
        <v-icon :size="iconSize">{{ icon }}</v-icon>
      </v-btn>
    </template>

    <v-list :density="density" class="actions-list">
      <!-- Ações padrão se não passar slot -->
      <template v-if="!$slots.default">
        <!-- Visualizar -->
        <v-list-item
          v-if="canView"
          @click="handleAction('view')"
          class="action-item view-action"
        >
          <template v-slot:prepend>
            <div class="action-icon-wrapper info-icon">
              <v-icon size="18">mdi-eye-outline</v-icon>
            </div>
          </template>
          <v-list-item-title class="action-title">Visualizar</v-list-item-title>
          <v-list-item-subtitle class="action-subtitle">Ver detalhes</v-list-item-subtitle>
        </v-list-item>

        <!-- Editar -->
        <v-list-item
          v-if="canEdit"
          @click="handleAction('edit')"
          class="action-item edit-action"
        >
          <template v-slot:prepend>
            <div class="action-icon-wrapper warning-icon">
              <v-icon size="18">mdi-pencil-outline</v-icon>
            </div>
          </template>
          <v-list-item-title class="action-title">Editar</v-list-item-title>
          <v-list-item-subtitle class="action-subtitle">Modificar registro</v-list-item-subtitle>
        </v-list-item>

        <!-- Divider antes do excluir -->
        <v-divider v-if="(canView || canEdit) && canDelete" class="action-divider" />

        <!-- Excluir -->
        <v-list-item
          v-if="canDelete"
          @click="handleAction('delete')"
          class="action-item delete-action"
        >
          <template v-slot:prepend>
            <div class="action-icon-wrapper error-icon">
              <v-icon size="18">mdi-delete-outline</v-icon>
            </div>
          </template>
          <v-list-item-title class="action-title">Excluir</v-list-item-title>
          <v-list-item-subtitle class="action-subtitle">Remover permanentemente</v-list-item-subtitle>
        </v-list-item>

        <!-- Ações customizadas adicionais -->
        <template v-if="filteredCustomActions.length > 0">
          <v-divider class="action-divider" />
          <v-list-item
            v-for="action in filteredCustomActions"
            :key="action.key"
            @click="handleAction(action.key)"
            :class="['action-item', action.class || 'custom-action']"
          >
            <template v-slot:prepend>
              <div class="action-icon-wrapper custom-icon">
                <v-icon size="18">{{ action.icon }}</v-icon>
              </div>
            </template>
            <v-list-item-title class="action-title">{{ action.title }}</v-list-item-title>
            <v-list-item-subtitle v-if="action.subtitle" class="action-subtitle">
              {{ action.subtitle }}
            </v-list-item-subtitle>
          </v-list-item>
        </template>
      </template>

      <!-- Slot para total customização -->
      <slot />
    </v-list>
  </v-menu>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useAbilities } from '@/composables/useAbilities'

export interface CustomAction {
  key: string
  title: string
  subtitle?: string
  icon: string
  class?: string
  permission?: string
}

interface Props {
  // Item que será passado nos eventos
  item?: any

  // Ações padrão (mostrar/ocultar)
  showView?: boolean
  showEdit?: boolean
  showDelete?: boolean

  // Permissões para ações padrão
  viewPermission?: string
  editPermission?: string
  deletePermission?: string

  // Ações customizadas adicionais
  customActions?: CustomAction[]

  // Configurações do botão
  icon?: string
  size?: 'x-small' | 'small' | 'default' | 'large' | 'x-large'
  variant?: 'flat' | 'text' | 'outlined' | 'plain' | 'elevated' | 'tonal'
  color?: string
  iconSize?: string | number
  btnClass?: string

  // Configurações do menu
  offset?: string | number
  density?: 'default' | 'comfortable' | 'compact'
  closeOnClick?: boolean
}

interface Emits {
  (e: 'view', item: any): void
  (e: 'edit', item: any): void
  (e: 'delete', item: any): void
  (e: 'action', key: string, item: any): void
}

const props = withDefaults(defineProps<Props>(), {
  item: undefined,
  showView: true,
  showEdit: true,
  showDelete: true,
  viewPermission: undefined,
  editPermission: undefined,
  deletePermission: undefined,
  customActions: () => [],
  icon: 'mdi-dots-vertical',
  size: 'small',
  variant: 'text',
  color: 'default',
  iconSize: 20,
  btnClass: '',
  offset: 8,
  density: 'compact',
  closeOnClick: true,
})

const emit = defineEmits<Emits>()

const { hasPermission } = useAbilities()

// Verificar se cada ação deve ser exibida baseado nas permissões
const canView = computed(() => {
  if (!props.showView) return false
  if (!props.viewPermission) return true
  return hasPermission.value(props.viewPermission)
})

const canEdit = computed(() => {
  if (!props.showEdit) return false
  if (!props.editPermission) return true
  return hasPermission.value(props.editPermission)
})

const canDelete = computed(() => {
  if (!props.showDelete) return false
  if (!props.deletePermission) return true
  return hasPermission.value(props.deletePermission)
})

// Filtrar ações customizadas baseado em permissões
const filteredCustomActions = computed(() => {
  return props.customActions.filter(action => {
    if (!action.permission) return true
    return hasPermission.value(action.permission)
  })
})

const handleAction = (action: string) => {
  if (action === 'view') {
    emit('view', props.item)
  } else if (action === 'edit') {
    emit('edit', props.item)
  } else if (action === 'delete') {
    emit('delete', props.item)
  } else {
    emit('action', action, props.item)
  }
}
</script>

<style scoped>
/* Botão do Menu */
.action-menu-btn {
  border-radius: 10px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-menu-btn:hover {
  background: rgba(30, 41, 59, 0.08) !important;
  transform: scale(1.05) rotate(90deg);
}

/* Lista de Ações */
.actions-list {
  min-width: 220px;
  max-width: 280px;
  border-radius: 16px !important;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08) !important;
  border: 1px solid rgba(0, 0, 0, 0.06);
  padding: 8px !important;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.98) !important;
}

/* Item de Ação */
.action-item {
  cursor: pointer;
  border-radius: 12px !important;
  margin: 4px 0 !important;
  padding: 12px 16px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.action-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 3px;
  height: 100%;
  background: transparent;
  transition: all 0.3s ease;
}

.action-item:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Wrapper do Ícone */
.action-icon-wrapper {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-item:hover .action-icon-wrapper {
  transform: scale(1.1) rotate(5deg);
}

/* Cores dos Ícones */
.info-icon {
  background: rgba(33, 150, 243, 0.1);
  color: #2196F3;
}

.warning-icon {
  background: rgba(255, 152, 0, 0.1);
  color: #FF9800;
}

.error-icon {
  background: rgba(244, 67, 54, 0.1);
  color: #F44336;
}

.custom-icon {
  background: rgba(103, 58, 183, 0.1);
  color: #673AB7;
}

/* Hover específico por ação */
.view-action:hover {
  background: rgba(33, 150, 243, 0.08);
}

.view-action:hover::before {
  background: #2196F3;
}

.view-action:hover .info-icon {
  background: rgba(33, 150, 243, 0.2);
  box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.1);
}

.edit-action:hover {
  background: rgba(255, 152, 0, 0.08);
}

.edit-action:hover::before {
  background: #FF9800;
}

.edit-action:hover .warning-icon {
  background: rgba(255, 152, 0, 0.2);
  box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.1);
}

.delete-action:hover {
  background: rgba(244, 67, 54, 0.08);
}

.delete-action:hover::before {
  background: #F44336;
}

.delete-action:hover .error-icon {
  background: rgba(244, 67, 54, 0.2);
  box-shadow: 0 0 0 4px rgba(244, 67, 54, 0.1);
}

.custom-action:hover {
  background: rgba(103, 58, 183, 0.08);
}

.custom-action:hover::before {
  background: #673AB7;
}

.custom-action:hover .custom-icon {
  background: rgba(103, 58, 183, 0.2);
  box-shadow: 0 0 0 4px rgba(103, 58, 183, 0.1);
}

/* Títulos */
.action-title {
  font-weight: 600 !important;
  font-size: 0.9375rem !important;
  color: #1e293b;
  line-height: 1.4 !important;
}

.action-subtitle {
  font-size: 0.75rem !important;
  color: #64748b !important;
  margin-top: 2px !important;
  line-height: 1.3 !important;
}

/* Divider */
.action-divider {
  margin: 8px 0 !important;
  opacity: 0.5;
}

/* Animação de entrada */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.action-item {
  animation: slideIn 0.3s ease-out backwards;
}

.action-item:nth-child(1) {
  animation-delay: 0.05s;
}

.action-item:nth-child(2) {
  animation-delay: 0.1s;
}

.action-item:nth-child(3) {
  animation-delay: 0.15s;
}

.action-item:nth-child(4) {
  animation-delay: 0.2s;
}

/* Dark Theme */
.v-theme--dark .action-menu-btn:hover {
  background: rgba(255, 255, 255, 0.1) !important;
}

.v-theme--dark .actions-list {
  background: rgba(30, 41, 59, 0.98) !important;
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 2px 8px rgba(0, 0, 0, 0.2) !important;
}

.v-theme--dark .action-title {
  color: #f1f5f9;
}

.v-theme--dark .action-subtitle {
  color: #94a3b8 !important;
}

.v-theme--dark .info-icon {
  background: rgba(33, 150, 243, 0.2);
}

.v-theme--dark .warning-icon {
  background: rgba(255, 152, 0, 0.2);
}

.v-theme--dark .error-icon {
  background: rgba(244, 67, 54, 0.2);
}

.v-theme--dark .custom-icon {
  background: rgba(103, 58, 183, 0.2);
}

.v-theme--dark .view-action:hover {
  background: rgba(33, 150, 243, 0.15);
}

.v-theme--dark .edit-action:hover {
  background: rgba(255, 152, 0, 0.15);
}

.v-theme--dark .delete-action:hover {
  background: rgba(244, 67, 54, 0.15);
}

.v-theme--dark .custom-action:hover {
  background: rgba(103, 58, 183, 0.15);
}

/* Responsive */
@media (max-width: 768px) {
  .actions-list {
    min-width: 200px;
  }

  .action-icon-wrapper {
    width: 32px;
    height: 32px;
  }

  .action-title {
    font-size: 0.875rem !important;
  }

  .action-subtitle {
    font-size: 0.6875rem !important;
  }
}
</style>

