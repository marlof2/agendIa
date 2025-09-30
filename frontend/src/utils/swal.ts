/**
 * Utilitários básicos de Toast para o AgendIA
 * Funções simples usando SweetAlert2
 */

import Swal from 'sweetalert2'

/**
 * Toast de sucesso
 */
export const showSuccessToast = (
  message: string,
  title?: string,
  timer: number = 4000
): void => {
  Swal.mixin({
    icon: 'success',
    title,
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer,
    timerProgressBar: true,
    allowOutsideClick: false,
    iconColor: '#10b981',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    },
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire()
}

/**
 * Toast de erro
 */
export const showErrorToast = (
  message: string,
  title?: string,
  timer: number = 5000
): void => {
  Swal.mixin({
    title: title,
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer,
    timerProgressBar: true,
    allowOutsideClick: false,
    icon: 'error',
    iconColor: '#ef4444',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    },
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire()
}

/**
 * Toast de aviso
 */
export const showWarningToast = (
  message: string,
  title?: string,
  timer: number = 4000
): void => {
  Swal.mixin({
    title: title,
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer,
    timerProgressBar: true,
    allowOutsideClick: false,
    icon: 'warning',
    iconColor: '#f59e0b',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    },
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire()
}

/**
 * Toast de informação
 */
export const showInfoToast = (
  message: string,
  title?: string,
  timer: number = 4000
): void => {
  Swal.mixin({
    title: title,
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer,
    timerProgressBar: true,
    allowOutsideClick: false,
    icon: 'info',
    iconColor: '#3b82f6',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    },
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire()
}

/**
 * Alerta de sucesso (modal)
 */
export const showSuccess = async (
  title: string,
  text?: string,
  confirmText: string = 'OK'
): Promise<boolean> => {
  const result = await Swal.fire({
    icon: 'success',
    title,
    text,
    confirmButtonText: confirmText,
    confirmButtonColor: '#10b981',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    }
  })
  return result.isConfirmed
}

/**
 * Alerta de erro (modal)
 */
export const showError = async (
  title: string,
  text?: string,
  confirmText: string = 'OK'
): Promise<boolean> => {
  const result = await Swal.fire({
    icon: 'error',
    title,
    text,
    confirmButtonText: confirmText,
    confirmButtonColor: '#ef4444',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    }
  })
  return result.isConfirmed
}

/**
 * Alerta de aviso (modal)
 */
export const showWarning = async (
  title: string,
  text?: string,
  confirmText: string = 'OK'
): Promise<boolean> => {
  const result = await Swal.fire({
    icon: 'warning',
    title,
    text,
    confirmButtonText: confirmText,
    confirmButtonColor: '#f59e0b',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    }
  })
  return result.isConfirmed
}

/**
 * Alerta de informação (modal)
 */
export const showInfo = async (
  title: string,
  text?: string,
  confirmText: string = 'OK'
): Promise<boolean> => {
  const result = await Swal.fire({
    icon: 'info',
    title,
    text,
    confirmButtonText: confirmText,
    confirmButtonColor: '#3b82f6',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    }
  })
  return result.isConfirmed
}

/**
 * Confirmação (modal)
 */
export const showConfirm = async (
  title: string,
  text?: string,
  confirmText: string = 'Sim',
  cancelText: string = 'Cancelar'
): Promise<boolean> => {
  const result = await Swal.fire({
    icon: 'question',
    title,
    text,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    }
  })
  return result.isConfirmed
}

/**
 * Loading
 */
export const showLoading = (
  title: string = 'Carregando...',
  text?: string
): void => {
  Swal.fire({
    title,
    text,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading()
    },
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    }
  })
}

/**
 * Fechar loading
 */
export const hideLoading = (): void => {
  Swal.close()
}

/**
 * Mostrar erros de validação em modal
 */
export const showValidationErrors = (errors: Record<string, string[]>): void => {
  // Converter erros para HTML formatado simples
  let errorsHtml = '<div style="text-align: left;">'

  Object.entries(errors).forEach(([field, fieldErrors]) => {
    fieldErrors.forEach(error => {
      errorsHtml += `<div style="margin-bottom: 10px; color: #ef4444; display: flex; align-items: center;">`
      errorsHtml += `<span style="margin-right: 8px;">❌</span>${error}`
      errorsHtml += `</div>`
    })
  })

  errorsHtml += '</div>'

  Swal.fire({
    icon: 'error',
    title: 'Erros de Validação',
    html: errorsHtml,
    confirmButtonText: 'Entendi',
    confirmButtonColor: '#ef4444',
    background: '#ffffff',
    color: '#1f2937',
    customClass: {
      popup: 'swal-font-roboto'
    },
    width: '500px'
  })
}
