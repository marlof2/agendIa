import axios, { type AxiosInstance, type AxiosRequestConfig, type AxiosResponse } from 'axios'
import { API_CONFIG, ERROR_MESSAGES } from '@/config/api'
import { showErrorToast, showWarningToast, showInfoToast } from '@/utils/swal'

// Configuração base do axios
const createAxiosInstance = (): AxiosInstance => {
  const instance = axios.create({
    baseURL: API_CONFIG.BASE_URL,
    timeout: API_CONFIG.TIMEOUT,
    headers: API_CONFIG.HEADERS
  })

  // Interceptor para adicionar token de autenticação
  instance.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem('auth_token')
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }

      // Log de requisições em desenvolvimento
      if (showDevLogs) {
        showInfoToast(`Requisição: ${config.method?.toUpperCase()} ${config.url}`, 'HTTP')
      }

      return config
    },
    (error) => {
      if (showDevLogs) {
        showErrorToast('Erro na configuração da requisição', 'HTTP')
      }
      return Promise.reject(error)
    }
  )

  // Interceptor para tratamento de respostas
  instance.interceptors.response.use(
    (response) => {
      // Log de sucesso em desenvolvimento
      if (showDevLogs && response.status >= 200 && response.status < 300) {
        showInfoToast(`Sucesso: ${response.status} ${response.config.method?.toUpperCase()} ${response.config.url}`, 'HTTP')
      }
      return response
    },
    (error) => {
      // Tratamento de erros global com toasts
      if (error.code === 'ECONNABORTED') {
        error.message = ERROR_MESSAGES.TIMEOUT_ERROR
        if (showErrorToasts) {
          showErrorToast(ERROR_MESSAGES.TIMEOUT_ERROR, 'Timeout')
        }
      } else if (error.code === 'ERR_NETWORK') {
        error.message = ERROR_MESSAGES.NETWORK_ERROR
        if (showErrorToasts) {
          showErrorToast(ERROR_MESSAGES.NETWORK_ERROR, 'Conexão')
        }
      } else if (error.response?.status === 401) {
        error.message = ERROR_MESSAGES.UNAUTHORIZED
        if (showErrorToasts) {
          showWarningToast(ERROR_MESSAGES.UNAUTHORIZED, 'Sessão Expirada')
        }
        localStorage.removeItem('auth_token')
        window.location.href = '/login'
      } else if (error.response?.status === 403) {
        error.message = ERROR_MESSAGES.FORBIDDEN
        if (showErrorToasts) {
          showErrorToast(ERROR_MESSAGES.FORBIDDEN, 'Permissão')
        }
      } else if (error.response?.status === 404) {
        error.message = ERROR_MESSAGES.NOT_FOUND
        if (showErrorToasts) {
          showWarningToast(ERROR_MESSAGES.NOT_FOUND, 'Não Encontrado')
        }
      } else if (error.response?.status === 422) {
        error.message = ERROR_MESSAGES.VALIDATION_ERROR
        if (showErrorToasts) {
          showErrorToast(ERROR_MESSAGES.VALIDATION_ERROR, 'Validação')
        }
      } else if (error.response?.status >= 500) {
        error.message = ERROR_MESSAGES.SERVER_ERROR
        if (showErrorToasts) {
          showErrorToast(ERROR_MESSAGES.SERVER_ERROR, 'Servidor')
        }
      } else {
        error.message = error.message || ERROR_MESSAGES.UNKNOWN_ERROR
        if (showErrorToasts) {
          showErrorToast(error.message, 'Erro')
        }
      }
      return Promise.reject(error)
    }
  )

  return instance
}

// Instância do axios
const http = createAxiosInstance()

export interface ApiResponse<T = any> {
  data: T
  message?: string
  status: number
  success: boolean
}

export interface PaginatedResponse<T = any> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

// Configuração para logs de desenvolvimento
let showDevLogs = false

// Configuração para mostrar erros como toasts
let showErrorToasts = true

export function useHttp() {
  // Função para configurar logs de desenvolvimento
  const setDevLogs = (enabled: boolean) => {
    showDevLogs = enabled && import.meta.env.DEV
  }

  // Função para configurar exibição de erros como toasts
  const setErrorToasts = (enabled: boolean) => {
    showErrorToasts = enabled
  }
  // GET request
  const get = async <T = any>(
    url: string,
    config?: AxiosRequestConfig
  ): Promise<ApiResponse<T>> => {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await http.get(url, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  // POST request
  const post = async <T = any>(
    url: string,
    data?: any,
    config?: AxiosRequestConfig
  ): Promise<ApiResponse<T>> => {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await http.post(url, data, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  // PUT request
  const put = async <T = any>(
    url: string,
    data?: any,
    config?: AxiosRequestConfig
  ): Promise<ApiResponse<T>> => {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await http.put(url, data, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  // PATCH request
  const patch = async <T = any>(
    url: string,
    data?: any,
    config?: AxiosRequestConfig
  ): Promise<ApiResponse<T>> => {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await http.patch(url, data, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  // DELETE request
  const del = async <T = any>(
    url: string,
    config?: AxiosRequestConfig
  ): Promise<ApiResponse<T>> => {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await http.delete(url, config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  // Upload de arquivo com FormData
  const uploadFile = async <T = any>(
    url: string,
    file: File | FormData,
    onUploadProgress?: (progressEvent: any) => void
  ): Promise<ApiResponse<T>> => {
    try {
      const formData = file instanceof FormData ? file : new FormData()
      if (file instanceof File) {
        formData.append('file', file)
      }

      const response: AxiosResponse<ApiResponse<T>> = await http.post(url, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress
      })
      return response.data
    } catch (error) {
      throw error
    }
  }

  // Download de arquivo (blob)
  const downloadFile = async (
    url: string,
    filename?: string,
    config?: AxiosRequestConfig
  ): Promise<void> => {
    try {
      const response: AxiosResponse<Blob> = await http.get(url, {
        ...config,
        responseType: 'blob'
      })

      // Criar link para download
      const blob = new Blob([response.data])
      const downloadUrl = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = downloadUrl
      link.download = filename || 'download'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(downloadUrl)
    } catch (error) {
      throw error
    }
  }

  // Download de arquivo como blob (retorna o blob)
  const getBlob = async (
    url: string,
    config?: AxiosRequestConfig
  ): Promise<Blob> => {
    try {
      const response: AxiosResponse<Blob> = await http.get(url, {
        ...config,
        responseType: 'blob'
      })
      return response.data
    } catch (error) {
      throw error
    }
  }

  // Request genérico
  const request = async <T = any>(
    config: AxiosRequestConfig
  ): Promise<ApiResponse<T>> => {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await http.request(config)
      return response.data
    } catch (error) {
      throw error
    }
  }

  return {
    get,
    post,
    put,
    patch,
    del,
    uploadFile,
    downloadFile,
    getBlob,
    request,
    http, // Instância do axios para casos específicos
    setDevLogs, // Função para configurar logs de desenvolvimento
    setErrorToasts // Função para configurar exibição de erros como toasts
  }
}
