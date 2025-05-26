<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const padres = ref([])
const busqueda = ref('')
const padreSeleccionado = ref(null)
const mensaje = ref('')
const error = ref('')
const previewUrl = ref(null)

const mostrarModalActualizar = ref(false)
const mensajeExito = ref('')

const form = reactive({
  persona: {},
  documento: {},
  padre: {},
})

const cargarPadres = async () => {
  try {
    const res = await api.get('/info/padres')
    padres.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = 'Error al cargar padres'
  }
}

onMounted(() => {
  cargarPadres()
})

const seleccionarPadre = (padre) => {
  padreSeleccionado.value = padre
  form.persona = { ...padre.persona_rol.persona }
  form.documento = { ...padre.persona_rol.persona.documento }
  form.padre = {
    estado_padre: padre.estado_padre,
    profesion_padre: padre.profesion_padre,
  }

  previewUrl.value = padre.persona_rol.persona.fotografia_persona
    ? `/storage/fotos/${padre.persona_rol.persona.fotografia_persona}`
    : null

  mensaje.value = ''
  error.value = ''
}

const padresFiltrados = computed(() => {
  if (!busqueda.value) return padres.value
  const q = busqueda.value.toLowerCase()
  return padres.value.filter((padre) => {
    const p = padre.persona_rol.persona
    return (
      p.nombres_persona.toLowerCase().includes(q) ||
      p.apellidos_pat.toLowerCase().includes(q) ||
      p.apellidos_mat.toLowerCase().includes(q)
    )
  })
})

const onFotoChange = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    form.persona.fotografia_persona = file
    previewUrl.value = URL.createObjectURL(file)
  } else {
    form.persona.fotografia_persona = null
    previewUrl.value = null
  }
}

const confirmarActualizarPadre = async () => {
  if (!padreSeleccionado.value) return
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

    for (const [key, value] of Object.entries(form.padre)) {
      formData.append(`padre[${key}]`, value)
    }

    await api.post(`/actualizar-padre/${padreSeleccionado.value.id_padre}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    mensaje.value = ''
    error.value = ''
    mensajeExito.value = 'Datos del padre modificados correctamente.'
    cargarPadres()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Error al actualizar padre'
    mensaje.value = ''
  } finally {
    mostrarModalActualizar.value = false
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Administrar Padres de Familia</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Lista -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Listado de Padres</h2>
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por nombre o apellido"
            class="w-full mb-4 px-4 py-2 border rounded shadow-sm"
          />
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2 text-left">Nombre completo</th>
                <th class="px-4 py-2 text-left">CI</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="p in padresFiltrados"
                :key="p.id_padre"
                @click="seleccionarPadre(p)"
                :class="[
                  'cursor-pointer hover:bg-gray-50',
                  padreSeleccionado?.id_padre === p.id_padre ? 'bg-blue-100' : '',
                ]"
              >
                <td class="px-4 py-2">
                  {{ p.persona_rol.persona.nombres_persona }}
                  {{ p.persona_rol.persona.apellidos_pat }}
                  {{ p.persona_rol.persona.apellidos_mat }}
                </td>
                <td class="px-4 py-2">
                  {{ p.persona_rol.persona.documento?.carnet_identidad || '---' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Formulario -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Editar Padre</h2>
          <div v-if="!padreSeleccionado" class="text-gray-500">
            Selecciona un padre para editar.
          </div>

          <div v-else class="space-y-4">
            <h3 class="text-lg font-semibold">Datos personales</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium">Nombres</label>
                <input
                  v-model="form.persona.nombres_persona"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Apellido Paterno</label>
                <input
                  v-model="form.persona.apellidos_pat"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Apellido Materno</label>
                <input
                  v-model="form.persona.apellidos_mat"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Sexo</label>
                <select v-model="form.persona.sexo_persona" class="w-full px-4 py-2 border rounded">
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium">Fecha de Nacimiento</label>
                <input
                  v-model="form.persona.fecha_nacimiento"
                  type="date"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Dirección</label>
                <input
                  v-model="form.persona.direccion_persona"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Nacionalidad</label>
                <input
                  v-model="form.persona.nacionalidad_persona"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Celular</label>
                <input
                  v-model="form.persona.celular_persona"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium">Fotografía</label>
                <input
                  type="file"
                  accept="image/*"
                  @change="onFotoChange"
                  class="w-full px-4 py-2 border rounded"
                />
                <div v-if="previewUrl" class="mt-2 flex justify-center">
                  <img
                    :src="previewUrl"
                    alt="Foto del padre"
                    class="w-32 h-32 rounded-full object-cover border shadow"
                  />
                </div>
              </div>
            </div>

            <h3 class="text-lg font-semibold mt-4">Documentos</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium">Carnet de Identidad</label>
                <input
                  v-model="form.documento.carnet_identidad"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Certificado de Nacimiento</label>
                <input
                  v-model="form.documento.certificado_nacimiento"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
            </div>

            <h3 class="text-lg font-semibold mt-4">Datos del padre</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium">Estado</label>
                <select v-model="form.padre.estado_padre" class="w-full px-4 py-2 border rounded">
                  <option value="activo">Activo</option>
                  <option value="inactivo">Inactivo</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium">Profesión</label>
                <input
                  v-model="form.padre.profesion_padre"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>
            </div>

            <div class="flex gap-4">
              <BaseButton
                label="Guardar cambios"
                color="success"
                @click="mostrarModalActualizar = true"
              />
              <BaseButton label="Cancelar" color="secondary" @click="padreSeleccionado = null" />
            </div>

            <div v-if="mensaje" class="text-green-600">{{ mensaje }}</div>
            <div v-if="error" class="text-red-600">{{ error }}</div>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal: Confirmar actualización -->
    <div
      v-if="mostrarModalActualizar"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4">
        <h2 class="text-lg font-bold text-blue-700">¿Desea modificar los datos del padre?</h2>
        <div class="flex justify-end gap-2 mt-4">
          <button
            @click="mostrarModalActualizar = false"
            class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
          >
            Cancelar
          </button>
          <button
            @click="confirmarActualizarPadre"
            class="px-4 py-2 text-sm text-white bg-blue-600 rounded"
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
  </LayoutAuthenticated>
</template>
