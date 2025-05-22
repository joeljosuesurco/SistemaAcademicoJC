<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const comunicados = ref([])
const loading = ref(true)
const error = ref(null)
const search = ref('')
const comunicadoSeleccionado = ref(null)
const mostrarDetalle = ref(false)
const detalleRef = ref(null)

const currentPage = ref(1)
const pageSize = 10

watch(search, () => {
  mostrarDetalle.value = false
  comunicadoSeleccionado.value = null
})

onMounted(async () => {
  try {
    const res = await api.get('/notificaciones')
    comunicados.value = res.data.data.sort((a, b) =>
      b.fecha_notificacion.localeCompare(a.fecha_notificacion),
    )
  } catch (err) {
    error.value = 'No se pudo cargar la lista de comunicados'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const comunicadosFiltrados = computed(() => {
  const lista = search.value
    ? comunicados.value.filter((c) =>
        c.titulo_notificacion.toLowerCase().includes(search.value.toLowerCase()),
      )
    : comunicados.value
  const start = (currentPage.value - 1) * pageSize
  return lista.slice(start, start + pageSize)
})

const totalPages = computed(() =>
  Math.ceil(
    (search.value
      ? comunicados.value.filter((c) =>
          c.titulo_notificacion.toLowerCase().includes(search.value.toLowerCase()),
        ).length
      : comunicados.value.length) / pageSize,
  ),
)

function verComunicado(c) {
  comunicadoSeleccionado.value = { ...c }
  mostrarDetalle.value = true
  nextTick(() => {
    detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}
</script>

<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Listado de Comunicados</h2>

    <div class="mb-4 flex flex-col md:flex-row md:items-center md:gap-4">
      <input
        v-model="search"
        type="text"
        placeholder="Buscar por título..."
        class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
      />
    </div>

    <div v-if="loading" class="text-gray-500">Cargando comunicados...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>

    <table v-else class="w-full text-left bg-white rounded shadow">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2">Título</th>
          <th class="p-2">Fecha</th>
          <th class="p-2">Tipo</th>
          <th class="p-2">Estado</th>
          <th class="p-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="com in comunicadosFiltrados" :key="com.id_notificacion">
          <td class="p-2">{{ com.titulo_notificacion }}</td>
          <td class="p-2">{{ com.fecha_notificacion }}</td>
          <td class="p-2 capitalize">{{ com.tipo_notificacion }}</td>
          <td class="p-2 capitalize">{{ com.estado_notificacion }}</td>
          <td class="p-2 space-x-2">
            <BaseButton color="info" label="Ver" small @click="verComunicado(com)" />
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
      v-if="mostrarDetalle"
      ref="detalleRef"
      class="mt-6 border border-indigo-300 p-6 rounded shadow-xl bg-white"
    >
      <h3 class="text-xl font-semibold mb-4 text-indigo-800">Detalle del Comunicado</h3>
      <p><strong>Título:</strong> {{ comunicadoSeleccionado.titulo_notificacion }}</p>
      <p><strong>Mensaje:</strong> {{ comunicadoSeleccionado.mensaje_notificacion }}</p>
      <p><strong>Fecha:</strong> {{ comunicadoSeleccionado.fecha_notificacion }}</p>
      <p><strong>Tipo:</strong> {{ comunicadoSeleccionado.tipo_notificacion }}</p>
      <p><strong>Estado:</strong> {{ comunicadoSeleccionado.estado_notificacion }}</p>
    </div>
  </div>
</template>
