import { ref } from 'vue'
import { showSuccessToast, showErrorToast, showInfoToast } from '@/utils/swal'
import { useHttp } from './useHttp'

export function useExport() {
  const loading = ref(false)
  const { get } = useHttp()

  const exportToExcel = async (endpoint: string, filename: string, filters?: any) => {
    loading.value = true

    try {
      showInfoToast('Exportando para Excel...', 'Exportação')

      const params = new URLSearchParams()
      params.append('format', 'excel')

      // Adicionar filtros se existirem
      if (filters) {
        Object.entries(filters).forEach(([key, value]) => {
          if (value !== undefined && value !== null && value !== '') {
            params.append(key, value.toString())
          }
        })
      }

      const response = await get(`${endpoint}/export?${params.toString()}`, {
        responseType: 'blob',
        headers: {
          'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }
      })

      // O response.data pode estar undefined, usar o response diretamente se for um Blob
      const blobData = response.data || response

      // Criar download diretamente do response
      const blob = new Blob([blobData], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      })
      const url = window.URL.createObjectURL(blob)

      // Criar link de download
      const link = document.createElement('a')
      link.style.display = 'none'
      link.href = url
      link.download = `${filename}.xlsx`

      // Adicionar ao DOM, clicar e remover
      document.body.appendChild(link)
      link.click()

      // Limpar após download
      setTimeout(() => {
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
      }, 100)

      showSuccessToast('Exportação para Excel concluída!', 'Exportação')

    } catch (error) {
      showErrorToast('Erro ao exportar para Excel', 'Exportação')
      throw error
    } finally {
      loading.value = false
    }
  }

  const exportToPDF = async (endpoint: string, filename: string, filters?: any) => {
    loading.value = true

    try {
      showInfoToast('Exportando para PDF...', 'Exportação')

      const params = new URLSearchParams()
      params.append('format', 'pdf')

      // Adicionar filtros se existirem
      if (filters) {
        Object.entries(filters).forEach(([key, value]) => {
          if (value !== undefined && value !== null && value !== '') {
            params.append(key, value.toString())
          }
        })
      }

      const response = await get(`${endpoint}/export?${params.toString()}`, {
        responseType: 'blob',
        headers: {
          'Accept': 'application/pdf'
        }
      })

      // O response.data pode estar undefined, usar o response diretamente se for um Blob
      const blobData = response.data || response

      // Criar download diretamente do response
      const blob = new Blob([blobData], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)

      // Criar link de download
      const link = document.createElement('a')
      link.style.display = 'none'
      link.href = url
      link.download = `${filename}.pdf`

      // Adicionar ao DOM, clicar e remover
      document.body.appendChild(link)
      link.click()

      // Limpar após download
      setTimeout(() => {
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
      }, 100)

      showSuccessToast('Exportação para PDF concluída!', 'Exportação')

    } catch (error) {
      showErrorToast('Erro ao exportar para PDF', 'Exportação')
      throw error
    } finally {
      loading.value = false
    }
  }


  const handleExport = async (format: 'excel' | 'pdf', endpoint: string, filename: string, filters?: any) => {
    switch (format) {
      case 'excel':
        return await exportToExcel(endpoint, filename, filters)
      case 'pdf':
        return await exportToPDF(endpoint, filename, filters)
      default:
        throw new Error(`Formato de exportação não suportado: ${format}`)
    }
  }

  return {
    loading,
    exportToExcel,
    exportToPDF,
    handleExport
  }
}
