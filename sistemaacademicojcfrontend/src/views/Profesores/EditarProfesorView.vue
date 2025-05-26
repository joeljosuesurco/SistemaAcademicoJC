<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const profesores = ref([])
const profesorSeleccionado = ref(null)
const mensaje = ref('')
const error = ref('')
const cargando = ref(true)
const busqueda = ref('')
const previewEditUrl = ref(null)

const mostrarModalEstado = ref(false)
const accionEstado = ref('') // 'inhabilitar' o 'reactivar'

const mensajeExito = ref('')

const mostrarModalActualizar = ref(false)
const mensajeExitoActualizar = ref('')

const form = reactive({
  persona: {},
  documento: {},
  profesor: {},
})

const cargarProfesores = async () => {
  try {
    const res = await api.get('/admin/profesores-completo')
    profesores.value = res.data.data.map((prof) => {
      const persona = prof.persona_rol?.persona || {}
      const documento = persona.documento || {}

      return {
        ...prof,
        nombres: persona.nombres_persona || '',
        apellidos_pat: persona.apellidos_pat || '',
        apellidos_mat: persona.apellidos_mat || '',
        persona,
        documento,
      }
    })
  } catch (err) {
    console.error(err)
    error.value = 'Error al cargar la lista de profesores'
  } finally {
    cargando.value = false
  }
}

const profesoresFiltrados = computed(() => {
  if (!busqueda.value) return profesores.value
  const query = busqueda.value.toLowerCase()
  return profesores.value.filter((prof) => {
    return (
      `${prof.nombres} ${prof.apellidos_pat} ${prof.apellidos_mat}`.toLowerCase().includes(query) ||
      prof.especialidad_profesor?.toLowerCase().includes(query)
    )
  })
})

const seleccionarProfesor = (prof) => {
  profesorSeleccionado.value = prof
  form.persona = { ...prof.persona }
  form.documento = { ...prof.documento }
  form.profesor = {
    especialidad_profesor: prof.especialidad_profesor,
    estado_profesor: prof.estado_profesor,
    titulo_provision_nacional: prof.titulo_provision_nacional,
    rda: prof.rda,
    cas: prof.cas,
  }

  // Imagen previa
  previewEditUrl.value = prof.persona?.fotografia_persona
    ? `/storage/fotos/${prof.persona.fotografia_persona}`
    : null

  mensaje.value = ''
  error.value = ''
}

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

const confirmarActualizarProfesor = async () => {
  if (!profesorSeleccionado.value) return

  try {
    const formData = new FormData()

    for (const [key, value] of Object.entries(form.persona)) {
      if (key === 'fotografia_persona') {
        if (value instanceof File) {
          formData.append(`persona[${key}]`, value)
        }
      } else if (value !== null && value !== undefined) {
        formData.append(`persona[${key}]`, value)
      }
    }

    for (const [key, value] of Object.entries(form.documento)) {
      formData.append(`documento[${key}]`, value)
    }

    for (const [key, value] of Object.entries(form.profesor)) {
      formData.append(`profesor[${key}]`, value)
    }

    await api.post(`/actualizar-profesor/${profesorSeleccionado.value.id_profesor}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    error.value = ''
    mensajeExitoActualizar.value = 'Datos del profesor modificados correctamente.'
    cargarProfesores()
  } catch (err) {
    console.error('🔴 Error al actualizar:', err.response?.data)
    error.value = err.response?.data?.message || 'Error al actualizar profesor'
  } finally {
    mostrarModalActualizar.value = false
  }
}

const confirmarCambioEstado = async () => {
  if (!profesorSeleccionado.value) return

  const id = profesorSeleccionado.value.id_profesor
  const endpoint =
    accionEstado.value === 'inhabilitar'
      ? `/profesores/${id}/inhabilitar`
      : `/profesores/${id}/reactivar`

  try {
    await api.put(endpoint)
    mensaje.value = ''
    error.value = ''
    mensajeExito.value =
      accionEstado.value === 'inhabilitar'
        ? 'Profesor inhabilitado correctamente.'
        : 'Profesor reactivado correctamente.'

    // Actualizar visualmente
    const nuevoEstado = accionEstado.value === 'inhabilitar' ? 'inactivo' : 'activo'
    profesorSeleccionado.value.estado_profesor = nuevoEstado

    const index = profesores.value.findIndex((p) => p.id_profesor === id)
    if (index !== -1) {
      profesores.value[index].estado_profesor = nuevoEstado
    }
  } catch (err) {
    console.error('❌ Error al cambiar estado del profesor:', err)
    error.value = err.response?.data?.message || 'Error al cambiar estado del profesor'
    mensaje.value = ''
  } finally {
    mostrarModalEstado.value = false
  }
}

const abrirModalEstado = () => {
  if (!profesorSeleccionado.value) return
  accionEstado.value =
    profesorSeleccionado.value.estado_profesor === 'activo' ? 'inhabilitar' : 'reactivar'
  mostrarModalEstado.value = true
}

onMounted(() => {
  cargarProfesores()
})
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6 max-w-7xl mx-auto">
      <h1 class="text-2xl font-bold mb-6">Administrar Profesor</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Lista de profesores -->
        <div class="bg-white p-6 rounded shadow">
          <h2 class="text-lg font-semibold mb-4">Selecciona un profesor</h2>

          <input
            type="text"
            v-model="busqueda"
            placeholder="Buscar por nombre o especialidad..."
            class="w-full mb-4 px-4 py-2 border rounded shadow-sm"
          />

          <div v-if="cargando" class="text-gray-500">Cargando profesores...</div>

          <div v-else class="overflow-auto max-h-[500px] border rounded">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100 sticky top-0">
                <tr>
                  <th class="px-4 py-2 text-left">Nombre completo</th>
                  <th class="px-4 py-2 text-left">Especialidad</th>
                  <th class="px-4 py-2 text-left">Estado</th>
                  <!-- 👈 nuevo -->
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="prof in profesoresFiltrados"
                  :key="prof.id_profesor"
                  @click="seleccionarProfesor(prof)"
                  :class="[
                    'cursor-pointer',
                    profesorSeleccionado?.id_profesor === prof.id_profesor
                      ? 'bg-blue-100'
                      : 'hover:bg-gray-50',
                  ]"
                >
                  <td class="px-4 py-2">
                    {{ prof.nombres }} {{ prof.apellidos_pat }} {{ prof.apellidos_mat }}
                  </td>
                  <td class="px-4 py-2">
                    {{ prof.profesor?.especialidad_profesor || prof.especialidad_profesor }}
                  </td>
                  <td class="px-4 py-2">
                    <span
                      :class="prof.estado_profesor === 'activo' ? 'text-green-600' : 'text-red-600'"
                    >
                      {{ prof.estado_profesor }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Formulario -->
        <div class="bg-white p-6 rounded shadow">
          <h2 class="text-xl font-semibold mb-6">Editar Profesor</h2>

          <div v-if="!profesorSeleccionado" class="text-gray-500">
            Selecciona un profesor para editar.
          </div>

          <div v-else class="space-y-6">
            <h3 class="text-lg font-semibold">Datos personales</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Nombres</label>
                <input
                  v-model="form.persona.nombres_persona"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Apellido paterno</label>
                <input
                  v-model="form.persona.apellidos_pat"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Apellido materno</label>
                <input
                  v-model="form.persona.apellidos_mat"
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
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Nacionalidad</label>
                <input
                  v-model="form.persona.nacionalidad_persona"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Celular</label>
                <input
                  v-model="form.persona.celular_persona"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Fotografía</label>
                <input
                  type="file"
                  accept="image/*"
                  @change="onFotoEditChange"
                  class="w-full px-4 py-2 border rounded"
                />

                <div v-if="previewEditUrl" class="mt-2 flex justify-center">
                  <img
                    :src="previewEditUrl"
                    alt="Previsualización"
                    class="w-32 h-32 object-cover rounded-full border shadow"
                  />
                </div>
              </div>
            </div>

            <h3 class="text-lg font-semibold">Documentos</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Carnet de Identidad</label>
                <input
                  v-model="form.documento.carnet_identidad"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Certificado de Nacimiento</label>
                <input
                  v-model="form.documento.certificado_nacimiento"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
            </div>

            <h3 class="text-lg font-semibold">Datos institucionales</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Especialidad</label>
                <input
                  v-model="form.profesor.especialidad_profesor"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Estado</label>
                <input
                  v-model="form.profesor.estado_profesor"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Título de Provisión Nacional</label>
                <input
                  v-model="form.profesor.titulo_provision_nacional"
                  class="px-4 py-2 border rounded w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">RDA</label>
                <input v-model="form.profesor.rda" class="px-4 py-2 border rounded w-full" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">CAS</label>
                <input v-model="form.profesor.cas" class="px-4 py-2 border rounded w-full" />
              </div>
            </div>

            <div class="flex gap-4">
              <BaseButton
                label="Guardar Cambios"
                color="success"
                @click="mostrarModalActualizar = true"
              />
              <BaseButton label="Cancelar" color="secondary" @click="profesorSeleccionado = null" />
              <BaseButton
                :label="
                  profesorSeleccionado.estado_profesor === 'activo' ? 'Inhabilitar' : 'Reactivar'
                "
                :color="profesorSeleccionado.estado_profesor === 'activo' ? 'danger' : 'warning'"
                @click="abrirModalEstado"
              />
            </div>

            <div v-if="mensaje" class="text-green-600">{{ mensaje }}</div>
            <div v-if="error" class="text-red-600">{{ error }}</div>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal: Confirmar inhabilitar/reactivar -->
    <div
      v-if="mostrarModalEstado"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4">
        <h2 class="text-lg font-bold text-red-700">
          ¿Desea {{ accionEstado === 'inhabilitar' ? 'inhabilitar' : 'reactivar' }} al profesor?
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
    <!-- Modal: Mensaje de éxito -->
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
    <!-- Modal: Confirmar modificación -->
    <div
      v-if="mostrarModalActualizar"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4">
        <h2 class="text-lg font-bold text-blue-700">¿Desea modificar los datos del profesor?</h2>
        <div class="flex justify-end gap-2 mt-4">
          <button
            @click="mostrarModalActualizar = false"
            class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
          >
            Cancelar
          </button>
          <button
            @click="confirmarActualizarProfesor"
            class="px-4 py-2 text-sm text-white bg-blue-600 rounded"
          >
            Confirmar
          </button>
        </div>
      </div>
    </div>
    <!-- Modal: Éxito al modificar -->
    <div
      v-if="mensajeExitoActualizar"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4 text-center">
        <h2 class="text-lg font-bold text-green-700">Éxito</h2>
        <p class="text-gray-700">{{ mensajeExitoActualizar }}</p>
        <div class="flex justify-center mt-4">
          <button
            @click="mensajeExitoActualizar = ''"
            class="px-4 py-2 text-sm text-white bg-green-600 rounded"
          >
            Aceptar
          </button>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
