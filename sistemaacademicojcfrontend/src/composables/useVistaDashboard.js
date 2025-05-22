import { ref } from 'vue'

const vistaActual = ref('dashboard')

export function useVistaDashboard() {
  const setVistaActual = (vista) => {
    vistaActual.value = vista
  }

  const getVistaActual = () => vistaActual

  return {
    setVistaActual,
    getVistaActual,
  }
}
