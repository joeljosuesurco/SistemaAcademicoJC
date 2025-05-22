import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useMainStore = defineStore('main', () => {
  const userId = ref(null)
  const userName = ref('')
  const userRole = ref('Sin rol')

  const userAvatar = computed(
    () =>
      `https://api.dicebear.com/7.x/avataaars/svg?seed=${userName.value.replace(/[^a-z0-9]+/gi, '-')}`,
  )

  function setUser(payload) {
    if (payload.id) userId.value = payload.id
    if (payload.name) userName.value = payload.name
    if (payload.role) userRole.value = payload.role
  }

  async function fetchPerfil() {
    try {
      const res = await api.get('/perfil')
      const data = res.data.data

      const nombre = data?.persona_rol?.persona?.nombres_persona || ''
      const apellido = data?.persona_rol?.persona?.apellidos_pat || ''
      const rol = data?.persona_rol?.rol?.nombre || 'Sin rol'

      setUser({
        id: data.id_user,
        name: `${nombre} ${apellido}`.trim(),
        role: rol,
      })
    } catch (error) {
      console.error('Error al obtener el perfil', error)
    }
  }

  const comunicadosVisibles = ref([])

  async function fetchComunicadosVisibles() {
    try {
      if (!userId.value) return
      const res = await api.get(`/notificacion-usuario/${userId.value}/no-leidas`)
      comunicadosVisibles.value = res.data.data
    } catch (error) {
      console.error('Error al cargar comunicados no leídos', error)
    }
  }

  function marcarComunicadosComoVistos() {
    comunicadosVisibles.value = []
  }

  const notificacionesRecientes = ref([])

  async function cargarYMarcarNotificacionesRecientes() {
    try {
      const res = await api.get(`/notificacion-usuario/${userId.value}/ultimos`)
      const asignados = res.data.data || []

      notificacionesRecientes.value = asignados
        .map((n) => n.notificacion)
        .filter(Boolean)
        .sort((a, b) => b.fecha_notificacion.localeCompare(a.fecha_notificacion))

      await Promise.all(
        asignados.map(async (n) => {
          try {
            await api.put(`/notificaciones/${n.notificacion.id_notificacion}/marcar-leido`)
          } catch {
            console.warn(`Error marcando como leído: ${n.notificacion.id_notificacion}`)
          }
        }),
      )

      marcarComunicadosComoVistos()
    } catch (err) {
      console.error('Error al cargar o marcar comunicados recientes', err)
    }
  }

  return {
    userId,
    userName,
    userRole,
    userAvatar,
    setUser,
    fetchPerfil,
    comunicadosVisibles,
    fetchComunicadosVisibles,
    marcarComunicadosComoVistos,
    notificacionesRecientes,
    cargarYMarcarNotificacionesRecientes,
  }
})
