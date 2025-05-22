<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const padres = ref([])
const estudiantes = ref([])
const hijosAsignados = ref([])
const padreSeleccionado = ref(null)
const estudianteSeleccionado = ref(null)
const mensaje = ref('')
const error = ref('')
const searchPadres = ref('')
const searchEstudiantes = ref('')

const currentPagePadres = ref(1)
const currentPageEstudiantes = ref(1)
const pageSize = 5

onMounted(async () => {
  try {
    const resPadres = await api.get('/padres')
    const resEstudiantes = await api.get('/info/estudiantes')
    padres.value = resPadres.data.data
    estudiantes.value = resEstudiantes.data.estudiantes || resEstudiantes.data
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo cargar la información'
  }
})

watch(padreSeleccionado, async (nuevoPadre) => {
  if (nuevoPadre) {
    try {
      const res = await api.get(`/padres/${nuevoPadre.id_padre}/hijos`)
      hijosAsignados.value = res.data.data
    } catch (err) {
      console.error(err)
      hijosAsignados.value = []
    }
  } else {
    hijosAsignados.value = []
  }
})

const padresFiltrados = computed(() => {
  const filtered = searchPadres.value
    ? padres.value.filter((padre) => {
        const p = padre.persona_rol.persona
        return (
          p.nombres_persona.toLowerCase().includes(searchPadres.value.toLowerCase()) ||
          p.apellidos_pat.toLowerCase().includes(searchPadres.value.toLowerCase()) ||
          p.apellidos_mat.toLowerCase().includes(searchPadres.value.toLowerCase())
        )
      })
    : padres.value

  const start = (currentPagePadres.value - 1) * pageSize
  return filtered.slice(start, start + pageSize)
})

const estudiantesFiltrados = computed(() => {
  const filtered = searchEstudiantes.value
    ? estudiantes.value.filter((est) => {
        const p = est.persona_rol.persona
        return (
          p.nombres_persona.toLowerCase().includes(searchEstudiantes.value.toLowerCase()) ||
          p.apellidos_pat.toLowerCase().includes(searchEstudiantes.value.toLowerCase()) ||
          p.apellidos_mat.toLowerCase().includes(searchEstudiantes.value.toLowerCase())
        )
      })
    : estudiantes.value

  const start = (currentPageEstudiantes.value - 1) * pageSize
  return filtered.slice(start, start + pageSize)
})

const totalPagesPadres = computed(() => {
  const filtered = searchPadres.value
    ? padres.value.filter((padre) => {
        const p = padre.persona_rol.persona
        return (
          p.nombres_persona.toLowerCase().includes(searchPadres.value.toLowerCase()) ||
          p.apellidos_pat.toLowerCase().includes(searchPadres.value.toLowerCase()) ||
          p.apellidos_mat.toLowerCase().includes(searchPadres.value.toLowerCase())
        )
      })
    : padres.value
  return Math.ceil(filtered.length / pageSize)
})

const totalPagesEstudiantes = computed(() => {
  const filtered = searchEstudiantes.value
    ? estudiantes.value.filter((est) => {
        const p = est.persona_rol.persona
        return (
          p.nombres_persona.toLowerCase().includes(searchEstudiantes.value.toLowerCase()) ||
          p.apellidos_pat.toLowerCase().includes(searchEstudiantes.value.toLowerCase()) ||
          p.apellidos_mat.toLowerCase().includes(searchEstudiantes.value.toLowerCase())
        )
      })
    : estudiantes.value
  return Math.ceil(filtered.length / pageSize)
})

const asignarHijo = async () => {
  if (!padreSeleccionado.value || !estudianteSeleccionado.value) {
    error.value = 'Debes seleccionar tanto un padre como un estudiante'
    return
  }
  try {
    const payload = {
      padres_id_padre: padreSeleccionado.value.id_padre,
      estudiantes_id_estudiante: estudianteSeleccionado.value.id_estudiante,
    }
    const res = await api.post(`/padres/${payload.padres_id_padre}/asignar-hijos`, payload)
    mensaje.value = res.data.message
    error.value = ''
    const recarga = await api.get(`/padres/${payload.padres_id_padre}/hijos`)
    hijosAsignados.value = recarga.data.data
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Error al asignar hijo'
    mensaje.value = ''
  }
}

const quitarHijoAsignado = async (estudianteId) => {
  if (!padreSeleccionado.value) return
  try {
    await api.delete(`/padres/${padreSeleccionado.value.id_padre}/quitar-hijo/${estudianteId}`)
    mensaje.value = 'Relación eliminada correctamente.'
    error.value = ''
    const res = await api.get(`/padres/${padreSeleccionado.value.id_padre}/hijos`)
    hijosAsignados.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Error al quitar hijo'
    mensaje.value = ''
  }
}

const resetSeleccion = () => {
  padreSeleccionado.value = null
  estudianteSeleccionado.value = null
  mensaje.value = ''
  error.value = ''
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Asignar Hijos a Padres</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Seleccionar Padre</h2>
          <input
            v-model="searchPadres"
            type="text"
            placeholder="Buscar padre..."
            class="mb-2 w-full px-4 py-2 border rounded shadow-sm"
          />
          <div class="overflow-auto max-h-96 border rounded">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2">Nombre</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="padre in padresFiltrados"
                  :key="padre.id_padre"
                  @click="padreSeleccionado = padre"
                  :class="[
                    'cursor-pointer',
                    padreSeleccionado?.id_padre === padre.id_padre
                      ? 'bg-blue-100'
                      : 'hover:bg-gray-50',
                  ]"
                >
                  <td class="px-4 py-2">
                    {{ padre.persona_rol.persona.nombres_persona }}
                    {{ padre.persona_rol.persona.apellidos_pat }}
                    {{ padre.persona_rol.persona.apellidos_mat }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex justify-between items-center mt-2">
            <BaseButton
              label="« Anterior"
              :disabled="currentPagePadres === 1"
              @click="currentPagePadres--"
              small
            />
            <span>Página {{ currentPagePadres }} de {{ totalPagesPadres }}</span>
            <BaseButton
              label="Siguiente »"
              :disabled="currentPagePadres === totalPagesPadres"
              @click="currentPagePadres++"
              small
            />
          </div>
        </div>

        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Seleccionar Estudiante</h2>
          <input
            v-model="searchEstudiantes"
            type="text"
            placeholder="Buscar estudiante..."
            class="mb-2 w-full px-4 py-2 border rounded shadow-sm"
          />
          <div class="overflow-auto max-h-96 border rounded">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2">Nombre</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="est in estudiantesFiltrados"
                  :key="est.id_estudiante"
                  @click="estudianteSeleccionado = est"
                  :class="[
                    'cursor-pointer',
                    estudianteSeleccionado?.id_estudiante === est.id_estudiante
                      ? 'bg-green-100'
                      : 'hover:bg-gray-50',
                  ]"
                >
                  <td class="px-4 py-2">
                    {{ est.persona_rol.persona.nombres_persona }}
                    {{ est.persona_rol.persona.apellidos_pat }}
                    {{ est.persona_rol.persona.apellidos_mat }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex justify-between items-center mt-2">
            <BaseButton
              label="« Anterior"
              :disabled="currentPageEstudiantes === 1"
              @click="currentPageEstudiantes--"
              small
            />
            <span>Página {{ currentPageEstudiantes }} de {{ totalPagesEstudiantes }}</span>
            <BaseButton
              label="Siguiente »"
              :disabled="currentPageEstudiantes === totalPagesEstudiantes"
              @click="currentPageEstudiantes++"
              small
            />
          </div>
        </div>
      </div>

      <div class="mt-6 flex items-center gap-4">
        <BaseButton color="success" label="Asignar hijo" @click="asignarHijo" />
        <BaseButton color="gray" label="Cancelar" @click="resetSeleccion" />
      </div>

      <div v-if="padreSeleccionado" class="mt-6 bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-semibold mb-4">
          Hijos Asignados a
          <span class="text-blue-600 font-bold">
            {{ padreSeleccionado.persona_rol.persona.nombres_persona }}
            {{ padreSeleccionado.persona_rol.persona.apellidos_pat }}
          </span>
        </h2>
        <div class="overflow-auto max-h-96 border rounded">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2">Nombre</th>
                <th class="px-4 py-2">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="est in hijosAsignados" :key="est.id_estudiante" class="hover:bg-gray-50">
                <td class="px-4 py-2">
                  {{ est.persona_rol.persona.nombres_persona }}
                  {{ est.persona_rol.persona.apellidos_pat }}
                  {{ est.persona_rol.persona.apellidos_mat }}
                </td>
                <td class="px-4 py-2">
                  <BaseButton
                    label="Quitar"
                    color="danger"
                    small
                    @click="quitarHijoAsignado(est.id_estudiante)"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="mensaje" class="mt-4 text-green-600">{{ mensaje }}</div>
      <div v-if="error" class="mt-4 text-red-600">{{ error }}</div>
    </div>
  </LayoutAuthenticated>
</template>
