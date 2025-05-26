<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'

const materias = ref([])
const materiaSeleccionada = ref(null)
const niveles = ref([])
const mensaje = ref('')
const error = ref('')
const busqueda = ref('')

const form = reactive({
  sigla_materia: '',
  campo_materia: '',
  area_materia: '',
  nivel_educativo_id: '',
})

const modalVisibleEdit = ref(false)
const modalConfirmarAccion = ref(false)
const tipoAccion = ref('')
const modalConfirmarEdicion = ref(false)
const mensajeExito = ref('')

// Nuevas referencias para advertencia legal
const mostrarAdvertencia = ref(false)
const advertenciaPara = ref('')
const contador = ref(10)
const temporizador = ref(null)

const materiasFiltradas = computed(() => {
  if (!busqueda.value) return materias.value
  const q = busqueda.value.toLowerCase()
  return materias.value.filter(
    (m) =>
      m.sigla_materia.toLowerCase().includes(q) ||
      m.area_materia.toLowerCase().includes(q) ||
      m.campo_materia.toLowerCase().includes(q) ||
      m.nivel_educativo?.nombre?.toLowerCase().includes(q),
  )
})

const seleccionarMateria = (materia) => {
  materiaSeleccionada.value = materia
  Object.assign(form, {
    sigla_materia: materia.sigla_materia,
    campo_materia: materia.campo_materia,
    area_materia: materia.area_materia,
    nivel_educativo_id: materia.nivel_educativo_id,
  })
  mensaje.value = ''
  error.value = ''
}

const abrirAdvertencia = (accion) => {
  advertenciaPara.value = accion
  contador.value = 10
  mostrarAdvertencia.value = true
  if (temporizador.value) clearInterval(temporizador.value)
  temporizador.value = setInterval(() => {
    if (contador.value > 0) {
      contador.value--
    } else {
      clearInterval(temporizador.value)
    }
  }, 1000)
}

const confirmarAdvertencia = () => {
  mostrarAdvertencia.value = false
  if (advertenciaPara.value === 'guardar') actualizarMateria()
  else if (advertenciaPara.value === 'reactivar') ejecutarAccion(true)
}

const actualizarMateria = async () => {
  if (!materiaSeleccionada.value) return
  try {
    const res = await api.put(`/materias/${materiaSeleccionada.value.id_materia}`, form)
    mensajeExito.value = res.data.message || 'Materia actualizada correctamente.'
    modalVisibleEdit.value = true
    await cargarMaterias()
  } catch {
    error.value = 'Error al actualizar la materia.'
    mensajeExito.value = ''
  } finally {
    modalConfirmarEdicion.value = false
  }
}

const cancelar = () => {
  materiaSeleccionada.value = null
  mensaje.value = ''
  error.value = ''
}

const ejecutarAccion = async (desdeAdvertencia = false) => {
  if (!materiaSeleccionada.value) return
  try {
    const id = materiaSeleccionada.value.id_materia
    if (tipoAccion.value === 'inhabilitar') {
      await api.put(`/materias/${id}/inhabilitar`)
      materiaSeleccionada.value.estado = 'inactivo'
      mensajeExito.value = `Materia "${materiaSeleccionada.value.sigla_materia}" inhabilitada correctamente.`
      modalVisibleEdit.value = true
      await cargarMaterias()
      modalConfirmarAccion.value = false
    } else if (tipoAccion.value === 'reactivar') {
      if (desdeAdvertencia) {
        await api.put(`/materias/${id}/reactivar`)
        materiaSeleccionada.value.estado = 'activo'
        mensajeExito.value = `Materia "${materiaSeleccionada.value.sigla_materia}" reactivada correctamente.`
        modalVisibleEdit.value = true
        await cargarMaterias()
        modalConfirmarAccion.value = false
      } else {
        abrirAdvertencia('reactivar')
      }
    }
  } catch {
    error.value = 'Error al realizar la acción.'
  }
}

const confirmarAccion = (accion) => {
  tipoAccion.value = accion
  if (accion === 'reactivar') {
    abrirAdvertencia('reactivar')
  } else {
    modalConfirmarAccion.value = true
  }
}

const guardarCambios = () => {
  abrirAdvertencia('guardar')
}

const cargarMaterias = async () => {
  try {
    const res = await api.get('/admin/materias')
    materias.value = res.data.data
  } catch {
    error.value = 'Error al cargar materias'
  }
}

const cargarNiveles = async () => {
  try {
    const res = await api.get('/nivel-educativo')
    niveles.value = res.data.data
  } catch {
    console.error('Error al cargar niveles educativos')
  }
}

onMounted(() => {
  cargarMaterias()
  cargarNiveles()
})
</script>
<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Administrar Materias</h1>

      <div v-if="!materiaSeleccionada">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-xl font-semibold mb-4">Listado de materias</h2>
            <input
              v-model="busqueda"
              type="text"
              placeholder="Buscar por sigla, campo, área o nivel"
              class="w-full mb-4 px-4 py-2 border rounded shadow-sm"
            />
            <div class="overflow-auto max-h-[500px] border rounded">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-100 sticky top-0">
                  <tr>
                    <th class="px-4 py-2 text-left">Sigla</th>
                    <th class="px-4 py-2 text-left">Área</th>
                    <th class="px-4 py-2 text-left">Campo</th>
                    <th class="px-4 py-2 text-left">Nivel</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="m in materiasFiltradas"
                    :key="m.id_materia"
                    @click="seleccionarMateria(m)"
                    :class="[
                      'cursor-pointer',
                      materiaSeleccionada?.id_materia === m.id_materia
                        ? 'bg-blue-100'
                        : 'hover:bg-gray-50',
                    ]"
                  >
                    <td class="px-4 py-2">{{ m.sigla_materia }}</td>
                    <td class="px-4 py-2">{{ m.area_materia }}</td>
                    <td class="px-4 py-2">{{ m.campo_materia }}</td>
                    <td class="px-4 py-2">{{ m.nivel_educativo?.nombre }}</td>
                    <td class="px-4 py-2">
                      <span
                        :class="m.estado === 'activo' ? 'text-green-600' : 'text-red-600'"
                        class="font-medium"
                      >
                        {{ m.estado }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white p-6 rounded shadow-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">
            Editar materia: {{ form.sigla_materia }} ({{
              materiaSeleccionada?.nivel_educativo?.nombre
            }})
          </h2>
          <BaseButton label="Cancelar" color="secondary" @click="cancelar" />
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium mb-1">Sigla</label>
            <input v-model="form.sigla_materia" class="w-full border px-4 py-2 rounded" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Área</label>
            <input v-model="form.area_materia" class="w-full border px-4 py-2 rounded" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Campo</label>
            <input v-model="form.campo_materia" class="w-full border px-4 py-2 rounded" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Nivel educativo</label>
            <select v-model="form.nivel_educativo_id" class="w-full border px-4 py-2 rounded">
              <option disabled value="">Seleccione nivel</option>
              <option v-for="n in niveles" :key="n.id" :value="n.id">{{ n.nombre }}</option>
            </select>
          </div>
        </div>

        <div class="flex gap-4">
          <BaseButton label="Guardar cambios" color="success" @click="guardarCambios" />
          <BaseButton
            v-if="materiaSeleccionada.estado === 'activo'"
            label="Inhabilitar materia"
            color="danger"
            @click="confirmarAccion('inhabilitar')"
          />
          <BaseButton
            v-else
            label="Reactivar materia"
            color="warning"
            @click="confirmarAccion('reactivar')"
          />
        </div>

        <div v-if="mensaje" class="text-green-600 mt-2">{{ mensaje }}</div>
        <div v-if="error" class="text-red-600 mt-2">{{ error }}</div>
      </div>

      <!-- Modal Confirmar Acción (solo para inhabilitar) -->
      <div
        v-if="modalConfirmarAccion"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
      >
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
          <h2 class="text-lg font-bold text-red-700">¿Desea inhabilitar esta materia?</h2>
          <div class="flex justify-end gap-2 mt-4">
            <button
              @click="modalConfirmarAccion = false"
              class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
            >
              Cancelar
            </button>
            <button
              @click="ejecutarAccion()"
              class="px-4 py-2 text-sm text-white bg-red-600 rounded"
            >
              Inhabilitar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Confirmar Edición (solo texto) -->
      <div
        v-if="modalConfirmarEdicion"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
      >
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
          <h2 class="text-lg font-bold text-green-700">
            ¿Desea guardar los cambios de la materia <strong>{{ form.sigla_materia }}</strong
            >?
          </h2>
          <div class="flex justify-end gap-2 mt-4">
            <button
              @click="modalConfirmarEdicion = false"
              class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
            >
              Cancelar
            </button>
            <button
              @click="guardarCambios"
              class="px-4 py-2 text-sm text-white bg-green-600 rounded"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de Éxito -->
      <div
        v-if="modalVisibleEdit"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
      >
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
          <h2 class="text-lg font-bold text-blue-700">Materia actualizada</h2>
          <p>{{ mensajeExito }}</p>
          <div class="flex justify-end mt-4">
            <button
              @click="modalVisibleEdit = false"
              class="px-4 py-2 text-sm text-white bg-blue-600 rounded"
            >
              Aceptar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de Advertencia Legal -->
      <div
        v-if="mostrarAdvertencia"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
      >
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
          <h2 class="text-lg font-bold text-red-700">Advertencia legal</h2>
          <p class="text-sm text-gray-700">
            La modificación o creación de materias fuera del marco de la Ley N.º 070 puede
            contravenir la normativa vigente del sistema educativo boliviano.
          </p>
          <p class="text-xs text-gray-500">
            Lea esta advertencia. El botón se habilitará en {{ contador }} segundos.
          </p>
          <div class="flex justify-end gap-2 mt-4">
            <button
              @click="mostrarAdvertencia = false"
              class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
            >
              Cancelar
            </button>
            <button
              :disabled="contador > 0"
              @click="confirmarAdvertencia"
              class="px-4 py-2 text-sm text-white bg-red-600 rounded disabled:opacity-50"
            >
              Proseguir
            </button>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
