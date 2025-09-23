/**
 * Sistema de criptografia genérico para localStorage
 * Usa Web Crypto API para criptografar/descriptografar dados
 */

const ENCRYPTION_KEY = 'agendia_encryption_key_2025'

/**
 * Gera uma chave de criptografia a partir de uma string
 */
async function generateKey(password: string): Promise<CryptoKey> {
  const encoder = new TextEncoder()
  const data = encoder.encode(password)

  const hashBuffer = await crypto.subtle.digest('SHA-256', data)
  const keyBuffer = hashBuffer.slice(0, 32) // 256 bits

  return crypto.subtle.importKey(
    'raw',
    keyBuffer,
    { name: 'AES-GCM' },
    false,
    ['encrypt', 'decrypt']
  )
}

/**
 * Criptografa dados usando AES-GCM
 */
export async function encryptData(data: any): Promise<string> {
  try {
    const key = await generateKey(ENCRYPTION_KEY)
    const encoder = new TextEncoder()
    const jsonString = JSON.stringify(data)
    const dataBuffer = encoder.encode(jsonString)

    // Gerar IV aleatório
    const iv = crypto.getRandomValues(new Uint8Array(12))

    // Criptografar dados
    const encryptedBuffer = await crypto.subtle.encrypt(
      { name: 'AES-GCM', iv },
      key,
      dataBuffer
    )

    // Combinar IV e dados criptografados
    const combined = new Uint8Array(iv.length + encryptedBuffer.byteLength)
    combined.set(iv)
    combined.set(new Uint8Array(encryptedBuffer), iv.length)

    // Converter para base64
    return btoa(String.fromCharCode(...combined))
  } catch (error) {
    console.error('Erro ao criptografar dados:', error)
    throw new Error('Falha na criptografia')
  }
}

/**
 * Descriptografa dados usando AES-GCM
 */
export async function decryptData(encryptedData: string): Promise<any> {
  try {
    const key = await generateKey(ENCRYPTION_KEY)

    // Converter de base64
    const combined = Uint8Array.from(atob(encryptedData), c => c.charCodeAt(0))

    // Extrair IV e dados criptografados
    const iv = combined.slice(0, 12)
    const encrypted = combined.slice(12)

    // Descriptografar dados
    const decryptedBuffer = await crypto.subtle.decrypt(
      { name: 'AES-GCM', iv },
      key,
      encrypted
    )

    // Converter de volta para string
    const decoder = new TextDecoder()
    const jsonString = decoder.decode(decryptedBuffer)

    return JSON.parse(jsonString)
  } catch (error) {
    console.error('Erro ao descriptografar dados:', error)
    throw new Error('Falha na descriptografia')
  }
}



