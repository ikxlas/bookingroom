import { ref } from 'vue'

const API_URL = 'http://localhost:8000/api'
const user = ref(null)
const token = ref(null)

export function useAuth() {
  const initAuth = () => {
    const storedUser = localStorage.getItem('currentUser')
    const storedToken = localStorage.getItem('auth_token')
    if (storedUser && storedToken) {
      user.value = JSON.parse(storedUser)
      token.value = storedToken
    }
  }

  const login = async (username, password) => {
    try {
      const response = await fetch(`${API_URL}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ username, password })
      })
      
      const data = await response.json()
      if (response.ok && data.success) {
        token.value = data.token
        user.value = { role: data.role, username: data.username }
        
        localStorage.setItem('auth_token', token.value)
        localStorage.setItem('currentUser', JSON.stringify(user.value))
        
        return { success: true, role: data.role }
      }
      return { success: false, message: data.message || 'Invalid credentials' }
    } catch (e) {
      return { success: false, message: 'Server error' }
    }
  }

  const logout = async () => {
    if (token.value) {
      await fetch(`${API_URL}/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json'
        }
      }).catch(() => {})
    }
    user.value = null
    token.value = null
    localStorage.removeItem('currentUser')
    localStorage.removeItem('auth_token')
  }

  const getAuthHeaders = () => {
    return {
      'Authorization': `Bearer ${token.value}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  }

  return {
    user,
    token,
    initAuth,
    login,
    logout,
    getAuthHeaders,
    API_URL
  }
}
