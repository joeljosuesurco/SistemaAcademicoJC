<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const cursos = ref([])
const busqueda = ref('')
const currentPage = ref(1)
const pageSize = 7
const error = ref(null)
const cursoSeleccionado = ref(null)
const estudiantesInscritos = ref([])
const loadingEstudiantes = ref(false)
const mostrarHorario = ref(false)
const horarioCurso = ref([])
const loadingHorario = ref(false)

const cargarCursos = async () => {
  try {
    const res = await api.get('/cursos')
    cursos.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo cargar la lista de cursos.'
  }
}

onMounted(() => {
  cargarCursos()
})

watch(busqueda, () => {
  cursoSeleccionado.value = null
  mostrarHorario.value = false
})

const cursosFiltrados = computed(() => {
  if (!busqueda.value) return cursos.value
  const query = busqueda.value.toLowerCase()
  return cursos.value.filter((curso) => {
    return (
      curso.grado_curso.toLowerCase().includes(query) ||
      curso.nivel_educativo?.codigo.toLowerCase().includes(query) ||
      curso.aula_curso.toLowerCase().includes(query) ||
      curso.turno_curso.toLowerCase().includes(query)
    )
  })
})

const paginatedCursos = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return cursosFiltrados.value.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  return Math.ceil(cursosFiltrados.value.length / pageSize)
})

const verCurso = async (curso) => {
  cursoSeleccionado.value = curso
  mostrarHorario.value = false
  estudiantesInscritos.value = []
  loadingEstudiantes.value = true
  try {
    const res = await api.get(`/cursos/${curso.id_curso}/estudiantes`)
    estudiantesInscritos.value = res.data.data || []
  } catch (err) {
    console.error('Error al cargar estudiantes:', err)
  } finally {
    loadingEstudiantes.value = false
  }
}

const verHorario = async () => {
  if (!cursoSeleccionado.value) return
  mostrarHorario.value = !mostrarHorario.value
  if (mostrarHorario.value) {
    loadingHorario.value = true
    try {
      const res = await api.get(`/cursos/${cursoSeleccionado.value.id_curso}/horario`)
      horarioCurso.value = res.data.data || []
    } catch (err) {
      console.error('Error al cargar horario:', err)
    } finally {
      loadingHorario.value = false
    }
  }
}

const diasOrdenados = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']

const descargarReporte = async () => {
  if (!cursoSeleccionado.value) return

  const cursoId = cursoSeleccionado.value.id_curso
  const url = `${import.meta.env.VITE_API_BASE_URL}/reportes/curso/${cursoId}/estudiantes`
  const token = localStorage.getItem('token')

  if (!token) {
    alert('Usuario no autenticado.')
    return
  }

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/pdf',
      },
    })

    if (!response.ok) {
      throw new Error(`Error ${response.status}: No se pudo generar el PDF.`)
    }

    const blob = await response.blob()
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Reporte_Estudiantes_Curso_${cursoId}.pdf`
    link.click()
  } catch (error) {
    console.error('Error al descargar el PDF:', error)
    alert('Ocurrió un error al intentar generar el reporte PDF.')
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Listado de Cursos</h1>

      <div class="mb-4 flex justify-start">
        <input
          v-model="busqueda"
          type="text"
          placeholder="Buscar curso..."
          class="px-4 py-2 border rounded shadow-sm w-full md:w-1/3"
        />
      </div>

      <div v-if="error" class="text-red-600 mb-4">{{ error }}</div>

      <div class="overflow-auto rounded border">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left">Grado</th>
              <th class="px-4 py-2 text-left">Paralelo</th>
              <th class="px-4 py-2 text-left">Nivel</th>
              <th class="px-4 py-2 text-left">Aula</th>
              <th class="px-4 py-2 text-left">Turno</th>
              <th class="px-4 py-2 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="curso in paginatedCursos" :key="curso.id_curso" class="hover:bg-gray-50">
              <td class="px-4 py-2">{{ curso.grado_curso }}</td>
              <td class="px-4 py-2">{{ curso.paralelo_curso }}</td>
              <td class="px-4 py-2">{{ curso.nivel_educativo?.codigo || '—' }}</td>
              <td class="px-4 py-2">{{ curso.aula_curso }}</td>
              <td class="px-4 py-2">{{ curso.turno_curso }}</td>
              <td class="px-4 py-2 space-x-1">
                <BaseButton label="Ver" color="info" size="sm" @click="verCurso(curso)" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-center items-center gap-2" v-if="totalPages > 1">
        <BaseButton
          label="« Anterior"
          :disabled="currentPage === 1"
          @click="currentPage--"
          size="sm"
        />
        <span class="text-sm">Página {{ currentPage }} de {{ totalPages }}</span>
        <BaseButton
          label="Siguiente »"
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          size="sm"
        />
      </div>

      <div
        v-if="cursoSeleccionado"
        class="mt-6 p-4 border rounded shadow bg-white w-full space-y-6"
      >
        <!-- Botón para alternar -->
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-semibold">
            {{ mostrarHorario ? 'Horario del Curso' : 'Información del Curso' }}
          </h2>
          <BaseButton
            :label="mostrarHorario ? 'Ver Información' : 'Ver Horario'"
            color="primary"
            size="sm"
            @click="verHorario"
          />
        </div>

        <!-- Vista: Información del Curso -->
        <div v-if="!mostrarHorario">
          <p><strong>Grado:</strong> {{ cursoSeleccionado.grado_curso }}</p>
          <p><strong>Paralelo:</strong> {{ cursoSeleccionado.paralelo_curso }}</p>
          <p><strong>Nivel:</strong> {{ cursoSeleccionado.nivel_educativo?.codigo || '—' }}</p>
          <p><strong>Aula:</strong> {{ cursoSeleccionado.aula_curso }}</p>
          <p><strong>Turno:</strong> {{ cursoSeleccionado.turno_curso }}</p>
          <p><strong>Descripción:</strong> {{ cursoSeleccionado.descripcion }}</p>

          <div class="mt-6">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-md font-semibold">Estudiantes Inscritos</h3>
              <BaseButton label="Ver Reporte" color="success" size="sm" @click="descargarReporte" />
            </div>
            <div v-if="loadingEstudiantes" class="text-gray-500">Cargando estudiantes...</div>
            <div v-else-if="estudiantesInscritos.length === 0" class="text-gray-600">
              No hay estudiantes inscritos en la gestión activa.
            </div>
            <div v-else class="overflow-auto border rounded">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Nombre completo</th>
                    <th class="px-4 py-2 text-left">RUDE</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(est, i) in estudiantesInscritos"
                    :key="est.id_estudiante"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-4 py-2">{{ i + 1 }}</td>
                    <td class="px-4 py-2">{{ est.nombre_completo }}</td>
                    <td class="px-4 py-2">{{ est.rude }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Vista: Horario -->
        <div v-else>
          <div v-if="loadingHorario" class="text-gray-500">Cargando horario...</div>
          <div v-else-if="horarioCurso.length === 0" class="text-gray-600">
            No hay horario asignado para este curso.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full border text-sm">
              <thead class="bg-blue-100 text-blue-800">
                <tr>
                  <th class="border px-4 py-2">Hora</th>
                  <th v-for="dia in diasOrdenados" :key="dia" class="border px-4 py-2">
                    {{ dia }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="bloque in horarioCurso"
                  :key="bloque.id_horario"
                  class="hover:bg-gray-50"
                >
                  <td class="border px-4 py-2">{{ bloque.hora_inicio }} - {{ bloque.hora_fin }}</td>
                  <td v-for="dia in diasOrdenados" :key="dia" class="border px-4 py-2 text-center">
                    <span v-if="bloque.dia === dia">
                      {{ bloque.materia?.sigla_materia }}<br />
                      <span class="text-xs text-gray-600">{{ bloque.materia?.area_materia }}</span>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
