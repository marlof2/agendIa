/**
 * Utilitários para gerenciamento de localStorage
 * Centraliza todas as operações de armazenamento local
 */

import { encryptData, decryptData } from './encryption'

/**
 * Salva dados criptografados no localStorage
 */
export async function saveWithEncrypted(key: string, data: any): Promise<void> {
  try {
    const encryptedData = await encryptData(data)
    localStorage.setItem(key, encryptedData)
  } catch (error) {
    console.error('Erro ao salvar dados no localStorage:', error)
    throw error
  }
}

/**
 * Carrega dados descriptografados do localStorage
 */
export async function loadWithDecrypt(key: string): Promise<any | null> {
  try {
    const encryptedData = localStorage.getItem(key)
    if (!encryptedData) {
      return null
    }

    return await decryptData(encryptedData)
  } catch (error) {
    console.error('Erro ao carregar dados do localStorage:', error)
    // Se falhar na descriptografia, limpar o localStorage
    localStorage.removeItem(key)
    return null
  }
}

/**
 * Remove dados do localStorage
 */
export function clearFromStorage(key: string): void {
  localStorage.removeItem(key)
}

/**
 * Verifica se há dados salvos no localStorage
 */
export function hasInStorage(key: string): boolean {
  return localStorage.getItem(key) !== null
}

/**
 * Salva dados não criptografados no localStorage
 */
export function saveWithoutEncrypted(key: string, data: any): void {
  try {
    localStorage.setItem(key, JSON.stringify(data))
  } catch (error) {
    console.error('Erro ao salvar dados simples no localStorage:', error)
    throw error
  }
}

/**
 * Carrega dados não criptografados do localStorage
 */
export function loadWithoutDecrypt(key: string): any | null {
  try {
    const data = localStorage.getItem(key)
    if (!data) {
      return null
    }
    return JSON.parse(data)
  } catch (error) {
    console.error('Erro ao carregar dados simples do localStorage:', error)
    localStorage.removeItem(key)
    return null
  }
}

/**
 * Remove todos os dados do localStorage
 */
export function clearAllStorage(): void {
  localStorage.clear()
}

/**
 * Obtém todas as chaves do localStorage
 */
export function getAllStorageKeys(): string[] {
  const keys: string[] = []
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i)
    if (key) {
      keys.push(key)
    }
  }
  return keys
}

// /**
//  * Obtém o tamanho do localStorage em bytes (aproximado)
//  */
// export function getStorageSize(): number {
//   let total = 0
//   for (let key in localStorage) {
//     if (localStorage.hasOwnProperty(key)) {
//       total += localStorage[key].length + key.length
//     }
//   }
//   return total
// }

// /**
//  * Verifica se o localStorage está disponível
//  */
// export function isStorageAvailable(): boolean {
//   try {
//     const test = '__storage_test__'
//     localStorage.setItem(test, test)
//     localStorage.removeItem(test)
//     return true
//   } catch (e) {
//     return false
//   }
// }
