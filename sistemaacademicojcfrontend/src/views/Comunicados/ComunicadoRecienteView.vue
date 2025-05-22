<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useMainStore } from '@/stores/main.js'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'

const mainStore = useMainStore()
const comunicados = ref([])
const error = ref('')

onMounted(async () => {
  try {
    if (!mainStore.userId) {
      await mainStore.fetchPerfil()
    }

    const res = await api.get(`/notificacion-usuario/${mainStore.userId}/ultimos`)
    const asignados = res.data.data || []

    comunicados.value = asignados
      .map((n) => n.notificacion)
      .filter(Boolean)
      .sort((a, b) => b.fecha_notificacion.localeCompare(a.fecha_notificacion))

    mainStore.marcarComunicadosComoVistos()
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo cargar los comunicados recientes.'
  }
})
</script>

<template>
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton title="Últimos Comunicados" />

      <div v-if="comunicados.length > 0" class="grid gap-4">
        <CardBox
          v-for="(comunicado, index) in comunicados"
          :key="comunicado.id_notificacion"
          class="transition-shadow border border-indigo-300 p-4 rounded-lg shadow-md"
          :class="{ 'animate-pulse border-2 border-indigo-500': index === 0 }"
        >
          <h2 class="text-lg font-bold text-indigo-800 mb-1">
            {{ comunicado.titulo_notificacion }}
          </h2>
          <p class="text-gray-700 whitespace-pre-line">{{ comunicado.mensaje_notificacion }}</p>
          <p class="text-sm text-gray-500 mt-2">
            Fecha: {{ comunicado.fecha_notificacion }} | Tipo: {{ comunicado.tipo_notificacion }} |
            Estado:
            {{ comunicado.estado_notificacion }}
          </p>
        </CardBox>
      </div>

      <div v-else class="text-gray-500">
        No hay comunicados recientes disponibles para tu usuario.
      </div>
      <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
@keyframes pulse {
  0%,
  100% {
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.5);
  }
  50% {
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.8);
  }
}

.animate-pulse {
  animation: pulse 1.5s infinite;
}
</style>
