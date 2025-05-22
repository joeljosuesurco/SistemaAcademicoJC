<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const cursos = ref([])
const profesores = ref([])
const materias = ref([])
const gestiones = ref([])
const gestionActiva = ref(null)
const asignaciones = ref([])

const form = ref({
  cursos_id_curso: '',
  profesores_id_profesor: '',
  materias_id_materia: '',
  gestiones_id_gestion: '',
})

const mensaje = ref(null)
const error = ref(null)
const loading = ref(false)

onMounted(async () => {
  try {
    const [resCursos, resProfesores, resMaterias, resGestiones, resAsignaciones] =
      await Promise.all([
        api.get('/cursos'),
        api.get('/info/profesores'),
        api.get('/materias'),
        api.get('/gestiones'),
        api.get('/asignaciones'),
      ])

    cursos.value = resCursos.data.data
    profesores.value = resProfesores.data.data
    materias.value = resMaterias.data.data
    gestiones.value = resGestiones.data.data
    asignaciones.value = resAsignaciones.data.data

    gestionActiva.value = gestiones.value.find((g) => g.estado_gestion === 'activa')
    if (gestionActiva.value) {
      form.value.gestiones_id_gestion = gestionActiva.value.id_gestion
    }
  } catch (err) {
    error.value = 'Error al cargar datos del formulario'
    console.error(err)
  }
})

async function asignarMateria() {
  mensaje.value = null
  error.value = null
  loading.value = true

  try {
    const res = await api.post('/asignaciones', form.value)
    asignaciones.value.push(res.data.data)

    mensaje.value = 'Asignación registrada correctamente.'
    form.value = {
      cursos_id_curso: '',
      profesores_id_profesor: '',
      materias_id_materia: '',
      gestiones_id_gestion: gestionActiva.value?.id_gestion || '',
    }
  } catch (err) {
    error.value = 'Error al registrar la asignación'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const cursoSeleccionado = computed(() =>
  cursos.value.find((c) => c.id_curso === form.value.cursos_id_curso),
)

const profesorSeleccionado = computed(() =>
  profesores.value.find((p) => p.id_profesor === form.value.profesores_id_profesor),
)

const materiaSeleccionada = computed(() =>
  materias.value.find((m) => m.id_materia === form.value.materias_id_materia),
)

const asignacionesSimilares = computed(() => {
  const curso = cursoSeleccionado.value
  const profesorID = form.value.profesores_id_profesor
  const materiaID = form.value.materias_id_materia

  if (!curso && !profesorID && !materiaID) return []

  return asignaciones.value.filter((a) => {
    const mismoCurso = curso
      ? a.curso?.grado_curso === curso.grado_curso &&
        a.curso?.paralelo_curso === curso.paralelo_curso &&
        a.curso?.nivel_educativo?.codigo === curso.nivel_educativo?.codigo
      : true

    const mismoProfesor = profesorID ? a.profesor?.id_profesor === profesorID : true
    const mismaMateria = materiaID ? a.materia?.id_materia === materiaID : true

    return mismoCurso && mismoProfesor && mismaMateria
  })
})
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6 max-w-7xl mx-auto">
      <h2 class="text-xl font-bold mb-6">Asignar Materia a Profesor</h2>

      <div v-if="mensaje" class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 shadow">
        {{ mensaje }}
      </div>
      <div v-if="error" class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 shadow">
        {{ error }}
      </div>

      <!-- FORMULARIO -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Curso -->
        <div class="bg-white p-6 rounded shadow border border-blue-200">
          <h3 class="text-lg font-semibold text-blue-700 mb-4">📘 Curso</h3>
          <label class="block mb-1 font-medium">Seleccionar curso</label>
          <select v-model="form.cursos_id_curso" class="w-full px-4 py-2 border rounded shadow-sm">
            <option disabled value="">Seleccione un curso</option>
            <option v-for="c in cursos" :key="c.id_curso" :value="c.id_curso">
              {{ c.grado_curso }} {{ c.paralelo_curso }} - {{ c.nivel_educativo?.codigo || '—' }}
            </option>
          </select>
        </div>

        <!-- Profesor -->
        <div class="bg-white p-6 rounded shadow border border-green-200">
          <h3 class="text-lg font-semibold text-green-700 mb-4">👨‍🏫 Profesor</h3>
          <label class="block mb-1 font-medium">Seleccionar profesor</label>
          <select
            v-model="form.profesores_id_profesor"
            class="w-full px-4 py-2 border rounded shadow-sm"
          >
            <option disabled value="">Seleccione un profesor</option>
            <option v-for="p in profesores" :key="p.id_profesor" :value="p.id_profesor">
              {{ p.persona_rol?.persona?.apellidos_pat }}
              {{ p.persona_rol?.persona?.apellidos_mat }}
              {{ p.persona_rol?.persona?.nombres_persona }}
            </option>
          </select>
        </div>

        <!-- Materia -->
        <div class="bg-white p-6 rounded shadow border border-purple-200">
          <h3 class="text-lg font-semibold text-purple-700 mb-4">📚 Materia</h3>
          <label class="block mb-1 font-medium">Seleccionar materia</label>
          <select
            v-model="form.materias_id_materia"
            class="w-full px-4 py-2 border rounded shadow-sm"
          >
            <option disabled value="">Seleccione una materia</option>
            <option v-for="m in materias" :key="m.id_materia" :value="m.id_materia">
              {{ m.sigla_materia }} - {{ m.area_materia }}
            </option>
          </select>
        </div>
      </div>

      <!-- VISTA PREVIA + TABLA -->
      <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Vista previa -->
        <div class="bg-white p-6 rounded shadow border border-blue-200">
          <h3 class="text-lg font-semibold text-blue-700 mb-4">Vista previa de la asignación</h3>

          <div class="mb-3 flex items-start gap-2">
            <span class="font-semibold">📚 Materia:</span>
            <span v-if="materiaSeleccionada">
              {{ materiaSeleccionada.sigla_materia }} - {{ materiaSeleccionada.area_materia }}
            </span>
            <span v-else class="text-gray-400">No seleccionada</span>
          </div>

          <div class="mb-3 flex items-start gap-2">
            <span class="font-semibold">👨‍🏫 Profesor:</span>
            <span v-if="profesorSeleccionado">
              {{ profesorSeleccionado.persona_rol?.persona?.apellidos_pat }}
              {{ profesorSeleccionado.persona_rol?.persona?.apellidos_mat }}
              {{ profesorSeleccionado.persona_rol?.persona?.nombres_persona }}
            </span>
            <span v-else class="text-gray-400">No seleccionado</span>
          </div>

          <div class="mb-3 flex items-start gap-2">
            <span class="font-semibold">🏫 Curso:</span>
            <span v-if="cursoSeleccionado">
              {{ cursoSeleccionado.grado_curso }} {{ cursoSeleccionado.paralelo_curso }} -
              {{ cursoSeleccionado.nivel_educativo?.codigo }}
            </span>
            <span v-else class="text-gray-400">No seleccionado</span>
          </div>

          <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded shadow-sm text-sm mb-4">
            Gestión activa: <strong>{{ gestionActiva?.nombre_gestion || '—' }}</strong>
          </div>

          <div class="flex gap-4">
            <BaseButton
              :disabled="loading"
              label="Asignar Materia"
              color="primary"
              @click="asignarMateria"
            />
            <BaseButton
              label="Cancelar"
              color="danger"
              @click="
                form = {
                  cursos_id_curso: '',
                  profesores_id_profesor: '',
                  materias_id_materia: '',
                  gestiones_id_gestion: gestionActiva?.id_gestion || '',
                }
              "
            />
          </div>
        </div>

        <!-- Similares -->
        <div class="bg-white p-6 rounded shadow border border-yellow-300">
          <h3 class="text-lg font-semibold text-yellow-700 mb-4">Cursos similares ya asignados</h3>
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-2">Materia</th>
                <th class="p-2">Profesor</th>
                <th class="p-2">Curso</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="asignacionesSimilares.length === 0">
                <td colspan="3" class="p-2 text-center text-gray-400">Sin coincidencias aún</td>
              </tr>
              <tr v-for="a in asignacionesSimilares" :key="a.id">
                <td class="p-2">{{ a.materia?.sigla_materia }} - {{ a.materia?.area_materia }}</td>
                <td class="p-2">
                  {{ a.profesor?.persona_rol?.persona?.apellidos_pat }}
                  {{ a.profesor?.persona_rol?.persona?.apellidos_mat }}
                  {{ a.profesor?.persona_rol?.persona?.nombres_persona }}
                </td>
                <td class="p-2">
                  {{ a.curso?.grado_curso }} {{ a.curso?.paralelo_curso }} -
                  {{ a.curso?.nivel_educativo?.codigo }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
