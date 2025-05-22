<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const materias = ref([])
const loading = ref(true)
const error = ref(null)
const search = ref('')
const materiaSeleccionada = ref(null)
const mostrarDetalle = ref(false)
const detalleRef = ref(null)

const currentPage = ref(1)
const pageSize = 10

onMounted(async () => {
  try {
    const res = await api.get('/materias')
    materias.value = (res.data.data || []).sort((a, b) =>
      a.sigla_materia.localeCompare(b.sigla_materia),
    )
  } catch (err) {
    error.value = 'No se pudo cargar la lista de materias'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const materiasFiltradas = computed(() => {
  const filtradas = search.value
    ? materias.value.filter(
        (mat) =>
          mat.sigla_materia.toLowerCase().includes(search.value.toLowerCase()) ||
          mat.area_materia.toLowerCase().includes(search.value.toLowerCase()) ||
          mat.campo_materia.toLowerCase().includes(search.value.toLowerCase()) ||
          (mat.nivel_educativo?.nombre || '').toLowerCase().includes(search.value.toLowerCase()),
      )
    : materias.value

  const start = (currentPage.value - 1) * pageSize
  return filtradas.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  const total = search.value
    ? materias.value.filter(
        (mat) =>
          mat.sigla_materia.toLowerCase().includes(search.value.toLowerCase()) ||
          mat.area_materia.toLowerCase().includes(search.value.toLowerCase()) ||
          mat.campo_materia.toLowerCase().includes(search.value.toLowerCase()) ||
          (mat.nivel_educativo?.nombre || '').toLowerCase().includes(search.value.toLowerCase()),
      ).length
    : materias.value.length
  return Math.ceil(total / pageSize)
})

function verMateria(materia) {
  materiaSeleccionada.value = { ...materia }
  mostrarDetalle.value = true
  nextTick(() => {
    detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-xl font-bold mb-4">Listado de Materias</h2>

      <div class="mb-4 flex flex-col md:flex-row md:items-center md:gap-4">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por sigla, área o campo..."
          class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
        />
      </div>

      <div v-if="loading" class="text-gray-500">Cargando materias...</div>
      <div v-else-if="error" class="text-red-600">{{ error }}</div>

      <table v-else class="w-full text-left bg-white rounded shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">Sigla</th>
            <th class="p-2">Área</th>
            <th class="p-2">Campo</th>
            <th class="p-2">Nivel Educativo</th>
            <th class="p-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="mat in materiasFiltradas" :key="mat.id_materia">
            <td class="p-2">{{ mat.sigla_materia }}</td>
            <td class="p-2">{{ mat.area_materia }}</td>
            <td class="p-2">{{ mat.campo_materia }}</td>
            <td class="p-2">{{ mat.nivel_educativo?.nombre || '—' }}</td>
            <td class="p-2">
              <BaseButton color="info" label="Ver" small @click="verMateria(mat)" />
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 flex justify-center items-center gap-2" v-if="totalPages > 1">
        <BaseButton label="« Anterior" :disabled="currentPage === 1" @click="currentPage--" small />
        <span>Página {{ currentPage }} de {{ totalPages }}</span>
        <BaseButton
          label="Siguiente »"
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          small
        />
      </div>

      <div
        v-if="mostrarDetalle && materiaSeleccionada"
        ref="detalleRef"
        class="mt-6 border border-blue-300 p-6 rounded shadow-xl bg-white"
      >
        <h3 class="text-xl font-semibold mb-4 text-blue-800">Detalle de la Materia</h3>
        <p><strong>Sigla:</strong> {{ materiaSeleccionada.sigla_materia }}</p>
        <p><strong>Área:</strong> {{ materiaSeleccionada.area_materia }}</p>
        <p><strong>Campo:</strong> {{ materiaSeleccionada.campo_materia }}</p>
        <p>
          <strong>Nivel Educativo:</strong>
          {{ materiaSeleccionada.nivel_educativo?.nombre || '—' }}
        </p>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
