<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const padres = ref([])
const search = ref('')
const loading = ref(true)
const error = ref(null)
const padreSeleccionado = ref(null)
const mostrarDetalle = ref(false)
const detalleRef = ref(null)

const currentPage = ref(1)
const pageSize = 10

watch(search, () => {
  mostrarDetalle.value = false
  padreSeleccionado.value = null
})

onMounted(async () => {
  try {
    const res = await api.get('/info/padres')
    padres.value = res.data.data.map((padre) => {
      const persona = padre.persona_rol.persona || {}
      const doc = persona.documento || {}
      return {
        ...padre,
        nombres: persona.nombres_persona || '',
        apellidos_pat: persona.apellidos_pat || '',
        apellidos_mat: persona.apellidos_mat || '',
        fecha_nacimiento: persona.fecha_nacimiento || '',
        direccion: persona.direccion_persona || '',
        nacionalidad: persona.nacionalidad_persona || '',
        sexo: persona.sexo_persona || '',
        celular: persona.celular_persona || '',
        fotografia: persona.fotografia_persona || '',
        ci: doc.carnet_identidad || '',
        certificado_nacimiento: doc.certificado_nacimiento || '',
        hijos: padre.estudiantes || [],
      }
    })
  } catch {
    error.value = 'No se pudo cargar la lista de padres'
  } finally {
    loading.value = false
  }
})

const padresFiltrados = computed(() => {
  const filtro = search.value.toLowerCase()
  const lista = padres.value.filter((p) => {
    const nombre = `${p.apellidos_pat} ${p.apellidos_mat} ${p.nombres}`.toLowerCase()
    return nombre.includes(filtro) || p.ci.toLowerCase().includes(filtro)
  })
  const start = (currentPage.value - 1) * pageSize
  return lista.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  const total = padresFiltrados.value.length
  return Math.ceil(total / pageSize)
})

function verPadre(p) {
  padreSeleccionado.value = { ...p }
  mostrarDetalle.value = true
  nextTick(() => detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }))
}

function calcularEdad(fecha) {
  if (!fecha) return '—'
  const hoy = new Date(),
    n = new Date(fecha)
  let edad = hoy.getFullYear() - n.getFullYear()
  const m = hoy.getMonth() - n.getMonth()
  if (m < 0 || (m === 0 && hoy.getDate() < n.getDate())) edad--
  return `${edad} años`
}
</script>

<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Listado de Padres de Familia</h2>

    <div class="mb-4 flex flex-col md:flex-row md:items-center md:gap-4">
      <input
        v-model="search"
        type="text"
        placeholder="Buscar por nombre o CI..."
        class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
      />
    </div>

    <div v-if="loading" class="text-gray-500">Cargando padres...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>

    <table v-else class="w-full text-left bg-white rounded shadow">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2">Apellido Paterno</th>
          <th class="p-2">Apellido Materno</th>
          <th class="p-2">Nombres</th>
          <th class="p-2">CI</th>
          <th class="p-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="padre in padresFiltrados" :key="padre.id_padre">
          <td class="p-2">{{ padre.apellidos_pat }}</td>
          <td class="p-2">{{ padre.apellidos_mat }}</td>
          <td class="p-2">{{ padre.nombres }}</td>
          <td class="p-2">{{ padre.ci }}</td>
          <td class="p-2">
            <BaseButton color="info" label="Ver" small @click="verPadre(padre)" />
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4 flex justify-center items-center gap-2" v-if="totalPages > 1">
      <BaseButton label="« Anterior" :disabled="currentPage === 1" small @click="currentPage--" />
      <span>Página {{ currentPage }} de {{ totalPages }}</span>
      <BaseButton
        label="Siguiente »"
        :disabled="currentPage === totalPages"
        small
        @click="currentPage++"
      />
    </div>

    <div
      v-if="mostrarDetalle && padreSeleccionado"
      ref="detalleRef"
      class="mt-6 grid md:grid-cols-2 gap-6 text-base"
    >
      <!-- Detalles del Padre -->
      <div class="border border-blue-300 p-6 rounded shadow-xl bg-white">
        <h3 class="text-xl font-semibold mb-4 text-blue-800">Detalles del Padre</h3>
        <div class="flex gap-6 items-start">
          <img
            v-if="padreSeleccionado.fotografia"
            :src="`/storage/fotos/${padreSeleccionado.fotografia}`"
            alt="Foto del padre"
            class="w-32 h-32 rounded object-cover border"
          />
          <div class="space-y-1">
            <p><strong>Apellido Paterno:</strong> {{ padreSeleccionado.apellidos_pat }}</p>
            <p><strong>Apellido Materno:</strong> {{ padreSeleccionado.apellidos_mat }}</p>
            <p><strong>Nombres:</strong> {{ padreSeleccionado.nombres }}</p>
            <p><strong>Fecha de Nacimiento:</strong> {{ padreSeleccionado.fecha_nacimiento }}</p>
            <p><strong>Edad:</strong> {{ calcularEdad(padreSeleccionado.fecha_nacimiento) }}</p>
            <p><strong>Sexo:</strong> {{ padreSeleccionado.sexo }}</p>
            <p><strong>Nacionalidad:</strong> {{ padreSeleccionado.nacionalidad }}</p>
            <p><strong>Celular:</strong> {{ padreSeleccionado.celular }}</p>
            <p><strong>Dirección:</strong> {{ padreSeleccionado.direccion }}</p>
          </div>
        </div>
      </div>

      <!-- Documentos e Hijos -->
      <div class="border border-green-300 p-6 rounded shadow-xl bg-white">
        <h3 class="text-xl font-semibold mb-4 text-green-800">Documentos del Padre</h3>
        <ul class="list-disc list-inside text-sm space-y-2">
          <li>
            Carnet de Identidad:
            <span class="text-gray-800">{{ padreSeleccionado.ci || '---' }}</span>
            <span class="ml-2" :class="padreSeleccionado.ci ? 'text-green-600' : 'text-red-600'">
              {{ padreSeleccionado.ci ? '✓ Presentado' : '✗ No presentado' }}
            </span>
          </li>
          <li>
            Certificado de Nacimiento:
            <span class="text-gray-800">{{
              padreSeleccionado.certificado_nacimiento || '---'
            }}</span>
            <span
              class="ml-2"
              :class="padreSeleccionado.certificado_nacimiento ? 'text-green-600' : 'text-red-600'"
            >
              {{ padreSeleccionado.certificado_nacimiento ? '✓ Presentado' : '✗ No presentado' }}
            </span>
          </li>
        </ul>

        <div class="mt-6">
          <h4 class="font-semibold text-gray-700 mb-1">Hijos Asociados:</h4>
          <ul class="list-disc list-inside text-sm space-y-1">
            <li v-for="hijo in padreSeleccionado.hijos" :key="hijo.estudiantes_id_estudiante">
              {{ hijo.estudiante.persona_rol.persona.apellidos_pat }}
              {{ hijo.estudiante.persona_rol.persona.apellidos_mat }}
              {{ hijo.estudiante.persona_rol.persona.nombres_persona }} —
              {{ hijo.estudiante.cursos[0]?.curso?.grado_curso || '—' }}
              {{ hijo.estudiante.cursos[0]?.curso?.paralelo_curso || '—' }} -
              {{ hijo.estudiante.cursos[0]?.curso?.nivel_educativo?.codigo || '—' }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
