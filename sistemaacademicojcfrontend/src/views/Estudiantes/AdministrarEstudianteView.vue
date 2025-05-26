<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const estudiantes = ref([])
const busqueda = ref('')
const estudianteSeleccionado = ref(null)
const mensaje = ref('')
const error = ref('')
const previewEditUrl = ref(null)

const mostrarModalEstado = ref(false)
const accionEstado = ref('') // 'inhabilitar' o 'rehabilitar'
const mensajeExito = ref('')

const mostrarModalActualizar = ref(false)

const onFotoEditChange = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    form.persona.fotografia_persona = file
    previewEditUrl.value = URL.createObjectURL(file)
  } else {
    form.persona.fotografia_persona = null
    previewEditUrl.value = null
  }
}

const form = reactive({
  persona: {},
  documento: {},
  estudiante: {},
  curso_id: null,
  gestion_id: null,
})

const cargarEstudiantes = async () => {
  try {
    const res = await api.get('/info/estudiantes')
    estudiantes.value = res.data.estudiantes // ✅ Corregido aquí
  } catch (err) {
    console.error(err)
    error.value = 'Error al cargar estudiantes'
  }
}

onMounted(() => {
  cargarEstudiantes()
})

const cursoVisibleDe = (est) => {
  if (!est?.cursos?.length) return null

  const cursoInscrito = est.cursos.find((c) => c.estado === 'inscrito')
  if (cursoInscrito) return cursoInscrito

  const cursosOrdenados = [...est.cursos]
    .filter((c) => c.estado === 'no_inscrito')
    .sort((a, b) => b.id - a.id)

  return cursosOrdenados[0] || null
}

const seleccionarEstudiante = (est) => {
  estudianteSeleccionado.value = est
  const curso = cursoVisibleDe(est)

  form.persona = { ...est.persona_rol.persona }
  form.documento = { ...est.persona_rol.persona.documento }
  form.estudiante = {
    rude: est.rude,
    libreta_anterior: est.libreta_anterior,
    obsev_estudiante: est.obsev_estudiante,
  }
  form.curso_id = curso?.curso.id_curso || null
  form.gestion_id = curso?.gestion.id_gestion || null

  mensaje.value = ''
  error.value = ''
}

const estadoCurso = computed(() => cursoVisibleDe(estudianteSeleccionado.value)?.estado || null)
const esInscrito = computed(() => estadoCurso.value === 'inscrito')

const confirmarActualizarEstudiante = async () => {
  if (!estudianteSeleccionado.value) return

  try {
    const formData = new FormData()

    for (const [key, value] of Object.entries(form.persona)) {
      if (key === 'fotografia_persona') {
        if (value instanceof File) {
          formData.append(`persona[${key}]`, value)
        }
      } else if (value !== undefined && value !== null) {
        formData.append(`persona[${key}]`, value)
      }
    }

    for (const [key, value] of Object.entries(form.documento)) {
      formData.append(`documento[${key}]`, value)
    }

    for (const [key, value] of Object.entries(form.estudiante)) {
      formData.append(`estudiante[${key}]`, value)
    }

    await api.post(
      `/actualizar-estudiante/${estudianteSeleccionado.value.id_estudiante}`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      },
    )

    error.value = ''
    mensajeExito.value = 'Datos del estudiante modificados correctamente.'
    cargarEstudiantes()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Error al actualizar estudiante'
    mensaje.value = ''
  } finally {
    mostrarModalActualizar.value = false
  }
}

const confirmarCambioEstado = async () => {
  if (!form.curso_id || !form.gestion_id) return

  const payload = {
    persona: form.persona,
    documento: form.documento,
    estudiante: form.estudiante,
    curso_id: form.curso_id,
    gestion_id: form.gestion_id,
    remover_inscripcion: esInscrito.value,
    rehabilitar: !esInscrito.value,
  }

  try {
    await api.put(`/actualizar-estudiante/${estudianteSeleccionado.value.id_estudiante}`, payload)
    mensaje.value = ''
    error.value = ''
    mensajeExito.value =
      accionEstado.value === 'inhabilitar'
        ? 'Estudiante inhabilitado correctamente.'
        : 'Estudiante rehabilitado correctamente.'

    estudianteSeleccionado.value = null
    cargarEstudiantes()
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo actualizar el estado de inscripción'
    mensaje.value = ''
  } finally {
    mostrarModalEstado.value = false
  }
}

const estudiantesFiltrados = computed(() => {
  if (!busqueda.value) return estudiantes.value
  const q = busqueda.value.toLowerCase()
  return estudiantes.value.filter((est) => {
    const p = est.persona_rol.persona
    return (
      p.nombres_persona.toLowerCase().includes(q) ||
      p.apellidos_pat.toLowerCase().includes(q) ||
      p.apellidos_mat.toLowerCase().includes(q)
    )
  })
})

const abrirModalEstado = () => {
  if (!form.curso_id || !form.gestion_id) return
  accionEstado.value = esInscrito.value ? 'inhabilitar' : 'rehabilitar'
  mostrarModalEstado.value = true
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Administrar Estudiantes</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Lista -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Listado de estudiantes</h2>
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por nombre o apellido"
            class="w-full mb-4 px-4 py-2 border rounded shadow-sm"
          />

          <p v-if="!estudiantes.length" class="text-gray-500 p-4">Cargando estudiantes...</p>

          <div class="overflow-auto max-h-[500px] border rounded" v-else>
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100 sticky top-0">
                <tr>
                  <th class="px-4 py-2 text-left">Nombre completo</th>
                  <th class="px-4 py-2 text-left">Curso</th>
                  <th class="px-4 py-2 text-left">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="est in estudiantesFiltrados"
                  :key="est.id_estudiante"
                  @click="seleccionarEstudiante(est)"
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
                    {{
                      cursoVisibleDe(est)
                        ? `${cursoVisibleDe(est).curso.grado_curso} ${cursoVisibleDe(est).curso.paralelo_curso} - ${cursoVisibleDe(est).curso.nivel_educativo?.nombre}`
                        : 'Sin curso'
                    }}
                  </td>
                  <td class="px-4 py-2 capitalize">
                    {{ cursoVisibleDe(est)?.estado || '---' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Card 2: Formulario -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Editar estudiante</h2>

          <div v-if="!estudianteSeleccionado" class="text-gray-500">
            Selecciona un estudiante para editar.
          </div>

          <div v-else class="space-y-4">
            <!-- Datos personales -->
            <h3 class="text-lg font-semibold mt-6 mb-2">Datos personales</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Nombres</label>
                <input
                  v-model="form.persona.nombres_persona"
                  placeholder="Nombres"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Apellido paterno</label>
                <input
                  v-model="form.persona.apellidos_pat"
                  placeholder="Apellido paterno"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Apellido materno</label>
                <input
                  v-model="form.persona.apellidos_mat"
                  placeholder="Apellido materno"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Sexo</label>
                <select v-model="form.persona.sexo_persona" class="px-4 py-2 border rounded w-full">
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Fecha de nacimiento</label>
                <input
                  v-model="form.persona.fecha_nacimiento"
                  type="date"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Dirección</label>
                <input
                  v-model="form.persona.direccion_persona"
                  placeholder="Dirección"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Nacionalidad</label>
                <input
                  v-model="form.persona.nacionalidad_persona"
                  placeholder="Nacionalidad"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Celular</label>
                <input
                  v-model="form.persona.celular_persona"
                  placeholder="Celular"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Fotografía</label>
                <input
                  type="file"
                  accept="image/*"
                  @change="onFotoEditChange"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div v-if="previewEditUrl" class="md:col-span-2 flex justify-center mt-2">
                <img
                  :src="previewEditUrl"
                  alt="Previsualización"
                  class="w-32 h-32 object-cover rounded-full border shadow"
                />
              </div>
            </div>

            <!-- Documentos -->
            <h3 class="text-lg font-semibold mt-6 mb-2">Documentos</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Carnet de Identidad</label>
                <input
                  v-model="form.documento.carnet_identidad"
                  placeholder="Carnet de Identidad"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Certificado de Nacimiento</label>
                <input
                  v-model="form.documento.certificado_nacimiento"
                  placeholder="Certificado de Nacimiento"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
            </div>

            <!-- Información académica -->
            <h3 class="text-lg font-semibold mt-6 mb-2">Información académica</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">RUDE</label>
                <input
                  v-model="form.estudiante.rude"
                  placeholder="RUDE"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Libreta anterior</label>
                <input
                  v-model="form.estudiante.libreta_anterior"
                  placeholder="Libreta anterior"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Observaciones</label>
                <input
                  v-model="form.estudiante.obsev_estudiante"
                  placeholder="Observaciones"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-4">
              <BaseButton
                label="Guardar cambios"
                color="success"
                @click="mostrarModalActualizar = true"
              />
              <BaseButton
                v-if="estadoCurso"
                :label="esInscrito ? 'Inhabilitar' : 'Rehabilitar'"
                :color="esInscrito ? 'danger' : 'info'"
                @click="abrirModalEstado"
              />
              <BaseButton
                label="Cancelar"
                color="secondary"
                @click="estudianteSeleccionado = null"
              />
            </div>

            <div v-if="mensaje" class="text-green-600">{{ mensaje }}</div>
            <div v-if="error" class="text-red-600">{{ error }}</div>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal: Confirmar acción -->
    <div
      v-if="mostrarModalEstado"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4">
        <h2 class="text-lg font-bold text-red-700">
          ¿Desea {{ accionEstado === 'inhabilitar' ? 'inhabilitar' : 'rehabilitar' }} al estudiante?
        </h2>
        <div class="flex justify-end gap-2 mt-4">
          <button
            @click="mostrarModalEstado = false"
            class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
          >
            Cancelar
          </button>
          <button
            @click="confirmarCambioEstado"
            class="px-4 py-2 text-sm text-white bg-red-600 rounded"
          >
            Confirmar
          </button>
        </div>
      </div>
    </div>
    <!-- Modal: Éxito -->
    <div
      v-if="mensajeExito"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4 text-center">
        <h2 class="text-lg font-bold text-green-700">Éxito</h2>
        <p class="text-gray-700">{{ mensajeExito }}</p>
        <div class="flex justify-center mt-4">
          <button
            @click="mensajeExito = ''"
            class="px-4 py-2 text-sm text-white bg-green-600 rounded"
          >
            Aceptar
          </button>
        </div>
      </div>
    </div>
    <!-- Modal: Confirmar actualización -->
    <div
      v-if="mostrarModalActualizar"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4">
        <h2 class="text-lg font-bold text-blue-700">¿Desea modificar los datos del estudiante?</h2>
        <div class="flex justify-end gap-2 mt-4">
          <button
            @click="mostrarModalActualizar = false"
            class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
          >
            Cancelar
          </button>
          <button
            @click="confirmarActualizarEstudiante"
            class="px-4 py-2 text-sm text-white bg-blue-600 rounded"
          >
            Confirmar
          </button>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
