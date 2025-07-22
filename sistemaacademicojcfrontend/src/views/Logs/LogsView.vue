<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const logs = ref([])
const paginaActual = ref(1)
const tieneMasPaginas = ref(false)
const filtros = ref({
  usuario_id: '',
  modulo: '',
  accion: '',
  desde: '',
  hasta: '',
})

const cargarLogs = async () => {
  try {
    const params = {
      page: paginaActual.value,
      ...filtros.value,
    }

    const res = await api.get('/logs', { params })
    logs.value = res.data.data.data
    tieneMasPaginas.value = !!res.data.data.next_page_url
  } catch (err) {
    console.error('Error al cargar logs:', err)
  }
}

const formatFecha = (fechaISO) => {
  return new Date(fechaISO).toLocaleString()
}

onMounted(() => {
  cargarLogs()
})
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Logs del Sistema</h1>

      <div class="flex gap-4 flex-wrap mb-4">
        <input
          v-model="filtros.usuario_id"
          type="text"
          placeholder="ID Usuario"
          class="border p-2 rounded"
        />
        <input
          v-model="filtros.modulo"
          type="text"
          placeholder="Módulo"
          class="border p-2 rounded"
        />
        <input
          v-model="filtros.accion"
          type="text"
          placeholder="Acción"
          class="border p-2 rounded"
        />
        <input v-model="filtros.desde" type="date" class="border p-2 rounded" />
        <input v-model="filtros.hasta" type="date" class="border p-2 rounded" />
        <BaseButton label="Buscar" @click="cargarLogs" />
      </div>

      <div v-if="logs.length === 0" class="text-gray-500">
        No se encontraron logs con los filtros seleccionados.
      </div>

      <div v-else>
        <table class="min-w-full border border-gray-300 mb-4 text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2">ID</th>
              <th class="p-2">Usuario</th>
              <th class="p-2">Módulo</th>
              <th class="p-2">Acción</th>
              <th class="p-2">Descripción</th>
              <th class="p-2">IP</th>
              <th class="p-2">Navegador</th>
              <th class="p-2">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id">
              <td class="p-2">{{ log.id }}</td>
              <td class="p-2">{{ log.usuario?.name_user || 'Sin nombre' }}</td>
              <td class="p-2">{{ log.modulo }}</td>
              <td class="p-2">{{ log.accion }}</td>
              <td class="p-2">{{ log.descripcion }}</td>
              <td class="p-2">{{ log.ip }}</td>
              <td class="p-2">{{ log.navegador }}</td>
              <td class="p-2">{{ formatFecha(log.created_at) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="flex items-center gap-2">
          <BaseButton
            label="Anterior"
            :disabled="paginaActual <= 1"
            @click="(paginaActual--, cargarLogs())"
          />
          <span>Página {{ paginaActual }}</span>
          <BaseButton
            label="Siguiente"
            :disabled="!tieneMasPaginas"
            @click="(paginaActual++, cargarLogs())"
          />
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
