import { useRouter } from 'vue-router'
import axios from 'axios'

export function useAuth() {
  const router = useRouter()

  const logout = () => {
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
    router.push('/login')
  }

  return {
    logout,
  }
}
