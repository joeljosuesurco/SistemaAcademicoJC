<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-4">Mis Cursos Asignados</h2>

      <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <input
          type="text"
          v-model="search"
          placeholder="Buscar por curso..."
          class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
        />
      </div>

      <div
        v-if="cursosAgrupados.length && !mostrarEstudiantes && !mostrarHorario"
        class="overflow-x-auto"
      >
        <table class="min-w-full bg-white border">
          <thead class="bg-slate-100">
            <tr>
              <th class="px-4 py-2 border">Curso</th>
              <th class="px-4 py-2 border">Nivel</th>
              <th class="px-4 py-2 border">Turno</th>
              <th class="px-4 py-2 border">Aula</th>
              <th class="px-4 py-2 border">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="curso in cursosFiltrados" :key="curso.id_curso" class="hover:bg-slate-50">
              <td class="px-4 py-2 border">{{ curso.grado_curso }} {{ curso.paralelo_curso }}</td>
              <td class="px-4 py-2 border">{{ curso.nivel_educativo.nombre }}</td>
              <td class="px-4 py-2 border">{{ curso.turno_curso }}</td>
              <td class="px-4 py-2 border">{{ curso.aula_curso }}</td>
              <td class="px-4 py-2 border space-x-2">
                <BaseButton label="Ver Horario" color="info" @click="mostrarHorarios(curso)" />
                <BaseButton
                  label="Ver Estudiantes"
                  color="success"
                  @click="cargarEstudiantes(curso)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estudiantes: tabla simplificada -->
      <div v-else-if="mostrarEstudiantes" class="mt-10">
        <div class="bg-white border rounded shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold">
              Estudiantes del curso {{ cursoSeleccionado.grado_curso }}
              {{ cursoSeleccionado.paralelo_curso }}
            </h3>
            <BaseButton label="Volver al listado" color="warning" @click="resetVista" />
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
              <thead class="bg-green-100">
                <tr>
                  <th class="px-4 py-2 border">#</th>
                  <th class="px-4 py-2 border">Apellido Paterno</th>
                  <th class="px-4 py-2 border">Apellido Materno</th>
                  <th class="px-4 py-2 border">Nombres</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(est, i) in estudiantesCurso"
                  :key="est.id_estudiante"
                  class="hover:bg-green-50"
                >
                  <td class="px-4 py-2 border text-center">{{ i + 1 }}</td>
                  <td class="px-4 py-2 border">{{ est.apellidos_pat }}</td>
                  <td class="px-4 py-2 border">{{ est.apellidos_mat }}</td>
                  <td class="px-4 py-2 border">{{ est.nombres }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-4 flex justify-end">
            <BaseButton
              label="Descargar PDF"
              color="success"
              small
              @click="descargarPDFEstudiantes"
            />
          </div>
        </div>
      </div>

      <!-- Horario -->
      <div v-else-if="mostrarHorario" class="mt-10" ref="horarioRef">
        <div class="bg-white border rounded shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold">
              Horario de {{ cursoSeleccionado.grado_curso }} {{ cursoSeleccionado.paralelo_curso }}
            </h3>
            <BaseButton label="Volver al listado" color="warning" @click="resetVista" />
          </div>
          <p class="text-gray-700 mb-4">
            Nivel educativo: <strong>{{ cursoSeleccionado.nivel_educativo.nombre }}</strong>
          </p>

          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded">
              <thead class="bg-blue-100">
                <tr>
                  <th class="px-4 py-2 border">Hora</th>
                  <th v-for="dia in diasOrdenados" :key="dia" class="px-4 py-2 border">
                    {{ dia }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(bloque, i) in bloquesCurso" :key="i" class="hover:bg-blue-50">
                  <td class="px-4 py-2 border bg-blue-50">
                    {{ bloque.hora_inicio }} - {{ bloque.hora_fin }}
                  </td>
                  <td v-for="dia in diasOrdenados" :key="dia" class="px-4 py-2 border text-center">
                    <span
                      v-if="bloque.dia === dia && bloque.materia"
                      class="inline-block bg-blue-200 text-blue-900 px-2 py-1 rounded"
                    >
                      {{ bloque.materia.sigla_materia }}
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

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const asignaciones = ref([])
const horarios = ref([])
const estudiantesCurso = ref([])
const search = ref('')
const cursoSeleccionado = ref(null)
const horarioRef = ref(null)
const mostrarEstudiantes = ref(false)
const mostrarHorario = ref(false)

const diasOrdenados = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']

onMounted(async () => {
  try {
    const resCursos = await api.get('/profesor-auth/cursos')
    asignaciones.value = resCursos.data.data || []

    const resHorarios = await api.get('/profesor-auth/horarios')
    horarios.value = resHorarios.data.data || []
  } catch (err) {
    console.error('Error al cargar los datos del profesor:', err)
  }
})

const cursosAgrupados = computed(() => {
  const map = new Map()
  for (const a of asignaciones.value) {
    if (!map.has(a.curso.id_curso)) {
      map.set(a.curso.id_curso, a.curso)
    }
  }
  return Array.from(map.values())
})

const cursosFiltrados = computed(() => {
  if (!search.value) return cursosAgrupados.value
  return cursosAgrupados.value.filter((c) => {
    const nombre = `${c.grado_curso} ${c.paralelo_curso}`.toLowerCase()
    return nombre.includes(search.value.toLowerCase())
  })
})

const mostrarHorarios = async (curso) => {
  cursoSeleccionado.value = curso
  mostrarHorario.value = true
  mostrarEstudiantes.value = false
  await nextTick()
  horarioRef.value?.scrollIntoView({ behavior: 'smooth' })
}

const cargarEstudiantes = async (curso) => {
  cursoSeleccionado.value = curso
  mostrarEstudiantes.value = true
  mostrarHorario.value = false
  estudiantesCurso.value = []

  try {
    const res = await api.get(`/profesor-auth/estudiantes-curso/${curso.id_curso}`)
    estudiantesCurso.value = res.data.data || []
  } catch (err) {
    console.error('Error al cargar estudiantes del curso:', err)
  }
}

const resetVista = () => {
  cursoSeleccionado.value = null
  mostrarEstudiantes.value = false
  mostrarHorario.value = false
}

const bloquesCurso = computed(() => {
  if (!cursoSeleccionado.value) return []

  const idCurso = cursoSeleccionado.value.id_curso
  const asignadas = asignaciones.value.filter((a) => a.curso.id_curso === idCurso)
  const materiasIds = asignadas.map((a) => a.materia.id_materia)

  return horarios.value
    .filter((h) => h.cursos_id_curso === idCurso && materiasIds.includes(h.materias_id_materia))
    .map((h) => ({
      dia: h.dia,
      hora_inicio: h.hora_inicio,
      hora_fin: h.hora_fin,
      materia: h.materia,
    }))
})

const descargarPDFEstudiantes = async () => {
  if (!cursoSeleccionado.value || !estudiantesCurso.value.length) {
    alert('No hay estudiantes para exportar.')
    return
  }

  const token = localStorage.getItem('token')
  const url = `${import.meta.env.VITE_API_BASE_URL}/reportes/estudiantes-curso`

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/pdf',
      },
      body: JSON.stringify({
        curso: {
          ...cursoSeleccionado.value,
          nivel: cursoSeleccionado.value.nivel_educativo?.nombre || '—',
        },
        estudiantes: estudiantesCurso.value,
      }),
    })

    const contentType = res.headers.get('content-type')
    if (!res.ok || !contentType.includes('application/pdf')) {
      const text = await res.text()
      console.error('Respuesta inesperada:', text)
      alert('No se pudo generar el PDF. Revisa consola.')
      return
    }

    const blob = await res.blob()
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Estudiantes_${cursoSeleccionado.value.grado_curso}_${cursoSeleccionado.value.paralelo_curso}.pdf`
    link.click()
  } catch (err) {
    console.error('Error al descargar PDF:', err)
    alert('Ocurrió un error al generar el reporte.')
  }
}
</script>
