<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'

const form = ref({
  sigla_materia: '',
  area_materia: '',
  campo_materia: '',
  nivel_educativo_id: '',
})

const errores = ref({})
const materias = ref([])
const materiaCreada = ref(null)
const niveles = ref([])
const modalExito = ref(false)

const mostrarAdvertencia = ref(false)
const contador = ref(10)
const temporizador = ref(null)

const vistaPrevia = computed(() => {
  const nivel = niveles.value.find((n) => n.id === form.value.nivel_educativo_id)
  return form.value.sigla_materia && nivel ? `${form.value.sigla_materia} – ${nivel.nombre}` : null
})

const materiasSimilares = computed(() => {
  if (!form.value.area_materia) return []
  return materias.value.filter(
    (m) =>
      m.area_materia === form.value.area_materia && m.sigla_materia !== form.value.sigla_materia,
  )
})

const cargarMaterias = async () => {
  try {
    const res = await api.get('/materias')
    materias.value = res.data.data
  } catch {
    console.error('Error al cargar materias')
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

const enviarFormulario = async () => {
  errores.value = {}

  try {
    const res = await api.post('/materias', form.value)
    materiaCreada.value = res.data.data
    materias.value.unshift(res.data.data)
    modalExito.value = true

    form.value = {
      sigla_materia: '',
      area_materia: '',
      campo_materia: '',
      nivel_educativo_id: '',
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errores.value = error.response.data.errors
    } else {
      console.error('Error al crear materia', error)
    }
  }
}

const abrirAdvertencia = () => {
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
  enviarFormulario()
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Formulario -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Registrar nueva materia</h2>
        <form @submit.prevent class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Sigla *</label>
            <input v-model="form.sigla_materia" class="w-full border px-4 py-2 rounded" />
            <span v-if="errores.sigla_materia" class="text-red-600 text-sm">
              {{ errores.sigla_materia[0] }}
            </span>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Campo *</label>
            <input v-model="form.campo_materia" class="w-full border px-4 py-2 rounded" />
            <span v-if="errores.campo_materia" class="text-red-600 text-sm">
              {{ errores.campo_materia[0] }}
            </span>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Área *</label>
            <input v-model="form.area_materia" class="w-full border px-4 py-2 rounded" />
            <span v-if="errores.area_materia" class="text-red-600 text-sm">
              {{ errores.area_materia[0] }}
            </span>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Nivel educativo *</label>
            <select v-model="form.nivel_educativo_id" class="w-full border px-4 py-2 rounded">
              <option disabled value="">Seleccione nivel</option>
              <option v-for="n in niveles" :key="n.id" :value="n.id">{{ n.nombre }}</option>
            </select>
            <span v-if="errores.nivel_educativo_id" class="text-red-600 text-sm">
              {{ errores.nivel_educativo_id[0] }}
            </span>
          </div>

          <BaseButton
            type="button"
            label="Registrar"
            color="success"
            class="mt-4"
            @click="abrirAdvertencia"
          />
        </form>

        <div v-if="vistaPrevia" class="mt-4 bg-blue-50 border p-3 rounded text-blue-700">
          Vista previa: <strong>{{ vistaPrevia }}</strong>
        </div>

        <div
          v-if="materiasSimilares.length"
          class="mt-4 bg-yellow-50 border p-3 rounded text-yellow-800"
        >
          <p class="font-medium mb-1">Materias similares:</p>
          <ul class="list-disc ml-5">
            <li v-for="m in materiasSimilares" :key="m.id_materia">
              {{ m.sigla_materia }} – {{ m.area_materia }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Listado de materias existentes -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Materias registradas</h2>
        <ul class="space-y-2 max-h-[400px] overflow-y-auto pr-2">
          <li v-for="m in materias" :key="m.id_materia" class="border px-4 py-2 rounded bg-gray-50">
            <p class="font-semibold">{{ m.sigla_materia }}</p>
            <p class="text-sm text-gray-600">{{ m.area_materia }} – {{ m.campo_materia }}</p>
            <p class="text-sm text-gray-500">{{ m.nivel_educativo?.nombre }}</p>
          </li>
        </ul>
      </div>
    </div>

    <!-- Modal de éxito -->
    <CardBoxModal v-model="modalExito" title="Materia registrada" button="Aceptar" color="success">
      <p>
        La materia <strong>{{ materiaCreada?.sigla_materia }}</strong> fue registrada correctamente.
      </p>
    </CardBoxModal>

    <!-- Modal de advertencia personalizado -->
    <div
      v-if="mostrarAdvertencia"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
        <h2 class="text-lg font-bold text-red-700">Advertencia legal</h2>
        <p class="text-sm text-gray-700">
          La modificación o creación de materias fuera del marco de la Ley N.º 070 puede contravenir
          la normativa vigente del sistema educativo boliviano.
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
  </LayoutAuthenticated>
</template>
