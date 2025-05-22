<script setup>
import { ref, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const estudiantes = ref([])
const cursos = ref([])
const gestionActiva = ref(null)
const busqueda = ref('')
const estudianteSeleccionado = ref(null)
const nuevoCursoId = ref('')
const mensaje = ref('')
const error = ref('')

const cargarEstudiantes = async () => {
  try {
    const res = await api.get('/info/estudiantes')
    estudiantes.value = res.data.estudiantes || []
  } catch (err) {
    console.error(err)
    error.value = 'Error al cargar estudiantes'
  }
}

onMounted(async () => {
  try {
    await cargarEstudiantes()

    const resCursos = await api.get('/cursos')
    cursos.value = resCursos.data.data || []

    const resGestiones = await api.get('/gestiones')
    gestionActiva.value = resGestiones.data.data.find((g) => g.estado_gestion === 'activa')
  } catch (err) {
    console.error(err)
    error.value = 'Error al cargar datos'
  }
})

const estudiantesFiltrados = computed(() => {
  const q = busqueda.value.toLowerCase()
  return estudiantes.value.filter((est) => {
    const p = est.persona_rol?.persona
    return (
      p?.nombres_persona?.toLowerCase().includes(q) ||
      p?.apellidos_pat?.toLowerCase().includes(q) ||
      p?.apellidos_mat?.toLowerCase().includes(q)
    )
  })
})

const cursoActivo = (est) => {
  return est.cursos?.find((c) => c.gestion?.estado_gestion === 'activa' && c.estado === 'inscrito')
}

const cursoActual = computed(() => {
  return cursoActivo(estudianteSeleccionado.value)
})

const cursoAnterior = computed(() => {
  if (!estudianteSeleccionado.value || !gestionActiva.value) return null
  return (
    estudianteSeleccionado.value.cursos
      .filter(
        (c) =>
          c.gestion.id_gestion === gestionActiva.value.id_gestion && c.estado === 'no_inscrito',
      )
      .sort((a, b) => b.id - a.id)[0] || null
  )
})

const nombreCurso = (cursoWrapper) => {
  const curso = cursoWrapper?.curso
  if (!curso) return '—'
  return `${curso.grado_curso} ${curso.paralelo_curso} - ${curso.nivel_educativo?.nombre}`
}

const nombreCursoPlano = (curso) => {
  if (!curso) return '—'
  return `${curso.grado_curso} ${curso.paralelo_curso} - ${curso.nivel_educativo?.nombre}`
}

const cambiarCurso = async () => {
  if (!estudianteSeleccionado.value || !cursoActual.value || !nuevoCursoId.value) {
    error.value = 'Completa todos los campos.'
    return
  }

  try {
    const res = await api.post('/cambio-curso', {
      estudiantes_id_estudiante: estudianteSeleccionado.value.id_estudiante,
      gestiones_id_gestion: gestionActiva.value.id_gestion,
      nuevo_curso_id: nuevoCursoId.value,
    })

    mensaje.value = res.data.message
    error.value = ''
    estudianteSeleccionado.value = null
    nuevoCursoId.value = ''
    await cargarEstudiantes()
  } catch (err) {
    console.error(err)
    error.value = 'Error al cambiar de curso'
    mensaje.value = ''
  }
}

const revertirCambio = async () => {
  if (!estudianteSeleccionado.value || !gestionActiva.value) {
    error.value = 'Selecciona un estudiante primero.'
    return
  }

  try {
    const res = await api.post('/revertir-cambio-curso', {
      estudiantes_id_estudiante: estudianteSeleccionado.value.id_estudiante,
      gestiones_id_gestion: gestionActiva.value.id_gestion,
    })

    mensaje.value = res.data.message
    error.value = ''
    estudianteSeleccionado.value = null
    nuevoCursoId.value = ''
    await cargarEstudiantes()
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo revertir el cambio'
    mensaje.value = ''
  }
}

const puedeRevertir = computed(() => {
  return !!cursoAnterior.value
})
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Cambio de Curso</h1>
      <div class="grid md:grid-cols-2 gap-6">
        <!-- Lista de estudiantes -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Estudiantes</h2>
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por nombre o apellido"
            class="w-full mb-4 px-4 py-2 border rounded shadow-sm"
          />
          <div class="max-h-[500px] overflow-auto border rounded">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100 sticky top-0">
                <tr>
                  <th class="px-4 py-2 text-left">Nombre</th>
                  <th class="px-4 py-2 text-left">Curso</th>
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
                      ? 'bg-blue-100'
                      : 'hover:bg-gray-50',
                  ]"
                >
                  <td class="px-4 py-2">
                    {{ est.persona_rol.persona.nombres_persona }}
                    {{ est.persona_rol.persona.apellidos_pat }}
                    {{ est.persona_rol.persona.apellidos_mat }}
                  </td>
                  <td class="px-4 py-2">
                    {{ nombreCurso(cursoActivo(est)) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Cambio de curso -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Asignar nuevo curso</h2>

          <div v-if="!estudianteSeleccionado" class="text-gray-500">Selecciona un estudiante.</div>

          <div v-else class="space-y-4">
            <p>
              <strong>Estudiante:</strong>
              {{ estudianteSeleccionado.persona_rol.persona.nombres_persona }}
              {{ estudianteSeleccionado.persona_rol.persona.apellidos_pat }}
              {{ estudianteSeleccionado.persona_rol.persona.apellidos_mat }}
            </p>
            <p><strong>Curso actual:</strong> {{ nombreCurso(cursoActual) }}</p>
            <p><strong>Curso anterior:</strong> {{ nombreCurso(cursoAnterior) }}</p>

            <div v-if="cursoActual">
              <label class="block text-sm font-medium mb-1">Nuevo curso</label>
              <select v-model="nuevoCursoId" class="w-full border px-4 py-2 rounded shadow-sm">
                <option value="">Seleccionar</option>
                <option
                  v-for="curso in cursos"
                  :key="curso.id_curso"
                  :value="curso.id_curso"
                  :disabled="cursoActual && curso.id_curso === cursoActual.curso.id_curso"
                >
                  {{ nombreCursoPlano(curso) }}
                </option>
              </select>

              <div class="flex gap-4 mt-4">
                <BaseButton color="success" label="Cambiar de curso" @click="cambiarCurso" />
                <BaseButton
                  color="warning"
                  label="Revertir cambio"
                  @click="revertirCambio"
                  :disabled="!puedeRevertir"
                />
              </div>
            </div>

            <div v-else class="bg-yellow-100 text-yellow-800 border border-yellow-300 p-4 rounded">
              <template v-if="!cursoAnterior">
                Este estudiante es nuevo. Primero debe ser <strong>inscrito a un curso</strong>.
              </template>
              <template v-else>
                Este estudiante no tiene una inscripción activa. Para cambiarlo de curso primero
                debes <strong>habilitarlo</strong>.
              </template>
            </div>

            <div class="mt-4 flex gap-4">
              <BaseButton
                color="secondary"
                label="Cancelar"
                @click="estudianteSeleccionado = null"
              />
            </div>

            <div v-if="mensaje" class="text-green-600">{{ mensaje }}</div>
            <div v-if="error" class="text-red-600">{{ error }}</div>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
