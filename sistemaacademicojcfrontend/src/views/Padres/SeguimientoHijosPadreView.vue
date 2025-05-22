<template>
  <LayoutAuthenticated>
    <div class="p-6 space-y-6">
      <h2 class="text-xl font-bold">Seguimiento de mis hijos</h2>

      <!-- Listado de hijos -->
      <div v-if="!estudianteSeleccionado">
        <div class="mb-4">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre..."
            class="w-full md:w-1/2 px-4 py-2 border rounded shadow-sm"
          />
        </div>

        <div v-if="loading" class="text-gray-500">Cargando hijos...</div>
        <div v-else-if="error" class="text-red-600">{{ error }}</div>

        <table v-else class="w-full text-left bg-white rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2">Estudiante</th>
              <th class="p-2">Curso</th>
              <th class="p-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="hijo in hijosFiltrados" :key="hijo.id_estudiante">
              <td class="p-2">{{ hijo.nombre_completo }}</td>
              <td class="p-2">{{ hijo.curso.grado_curso }} {{ hijo.curso.paralelo_curso }}</td>
              <td class="p-2">
                <BaseButton
                  label="Ver Seguimiento"
                  color="info"
                  small
                  @click="verSeguimiento(hijo)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Seguimiento Detalle -->
      <div v-else>
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">
            Seguimiento de {{ estudianteSeleccionado.nombre_completo }}
          </h2>
          <BaseButton label="Volver" color="gray" small @click="estudianteSeleccionado = null" />
        </div>

        <div class="mb-4">
          <label class="block font-semibold mb-1">Materia:</label>
          <select v-model="materiaSeleccionada" class="px-4 py-2 border rounded w-full">
            <option disabled value="">Seleccione una materia</option>
            <option v-for="m in materiasUnicas" :key="m.id_materia" :value="m.id_materia">
              {{ m.area_materia }}
            </option>
          </select>
        </div>

        <div v-if="seguimientosFiltrados.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-md flex flex-col h-[360px] overflow-hidden"
          >
            <h3 class="text-lg font-semibold text-blue-700 mb-4 text-center">
              Gráfico de Seguimiento
            </h3>

            <Radar :data="chartData" :options="chartOptions" width="300" height="300" />
          </div>

          <div class="bg-white p-4 border rounded shadow">
            <h3 class="text-lg font-semibold mb-2">Detalle de Seguimientos</h3>
            <ul class="text-sm space-y-2 max-h-80 overflow-y-auto">
              <li v-for="s in seguimientosFiltrados" :key="s.id_seguimiento" class="border-b pb-2">
                <p><strong>Fecha:</strong> {{ s.fecha_reg_seg }}</p>
                <p><strong>Observación:</strong> {{ s.observaciones_seguimiento }}</p>
                <ul class="ml-4 list-disc mt-2">
                  <li><strong>Asistencia:</strong> {{ s.asistencia }}</li>
                  <li><strong>Participación:</strong> {{ s.participacion }}</li>
                  <li><strong>Disciplina:</strong> {{ s.disciplina }}</li>
                  <li><strong>Puntualidad:</strong> {{ s.puntualidad }}</li>
                  <li><strong>Respeto:</strong> {{ s.respeto }}</li>
                  <li><strong>Tolerancia:</strong> {{ s.tolerancia }}</li>
                  <li><strong>Estado Ánimo:</strong> {{ s.estado_animo }}</li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
        <div v-else class="text-gray-600 text-center">
          No hay registros de seguimiento disponibles.
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import { Radar } from 'vue-chartjs'
import {
  Chart,
  RadialLinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js'
Chart.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

const hijos = ref([])
const estudianteSeleccionado = ref(null)
const seguimientoData = ref([])
const materiaSeleccionada = ref('')
const loading = ref(true)
const error = ref(null)
const search = ref('')

onMounted(async () => {
  try {
    const res = await api.get('/padre-auth/hijos')
    hijos.value = res.data.data || []
  } catch {
    error.value = 'No se pudo cargar la lista de hijos'
  } finally {
    loading.value = false
  }
})

function verSeguimiento(hijo) {
  estudianteSeleccionado.value = hijo
  materiaSeleccionada.value = ''
  cargarSeguimiento(hijo.id_estudiante)
}

async function cargarSeguimiento(idEstudiante) {
  try {
    const res = await api.get(`/padre-auth/seguimientos/${idEstudiante}`)
    seguimientoData.value = res.data.data || []
  } catch (err) {
    console.error('Error cargando seguimientos:', err)
  }
}

const materiasUnicas = computed(() => {
  const set = new Map()
  seguimientoData.value.forEach((s) => {
    if (s.materia) set.set(s.materias_id_materia, s.materia)
  })
  return Array.from(set.values())
})

const seguimientosFiltrados = computed(() => {
  return seguimientoData.value.filter((s) => s.materias_id_materia === materiaSeleccionada.value)
})

const radarLabels = [
  'Asistencia',
  'Participación',
  'Disciplina',
  'Puntualidad',
  'Respeto',
  'Tolerancia',
  'Ánimo',
]

const chartData = computed(() => {
  const ultimo = seguimientosFiltrados.value[0] || {}
  return {
    labels: radarLabels,
    datasets: [
      {
        label: 'Desempeño',
        data: [
          ultimo.asistencia === 'Sí' ? 5 : 0,
          ultimo.participacion === 'Alta' ? 5 : ultimo.participacion === 'Media' ? 3 : 1,
          ultimo.disciplina === 'Excelente'
            ? 5
            : ultimo.disciplina === 'Buena'
              ? 4
              : ultimo.disciplina === 'Regular'
                ? 3
                : 1,
          ultimo.puntualidad === 'Excelente'
            ? 5
            : ultimo.puntualidad === 'Buena'
              ? 4
              : ultimo.puntualidad === 'Regular'
                ? 3
                : 1,
          ultimo.respeto === 'Sí' ? 5 : 0,
          ultimo.tolerancia === 'Sí' ? 5 : 0,
          ultimo.estado_animo === 'Muy bien'
            ? 5
            : ultimo.estado_animo === 'Bien'
              ? 4
              : ultimo.estado_animo === 'Neutro'
                ? 3
                : ultimo.estado_animo === 'Estresado'
                  ? 2
                  : 1,
        ],
        backgroundColor: 'rgba(59,130,246,0.3)',
        borderColor: 'rgba(59,130,246,0.8)',
        pointBackgroundColor: 'rgba(37,99,235,0.8)',
        pointBorderColor: '#fff',
      },
    ],
  }
})

const chartOptions = {
  responsive: false,
  maintainAspectRatio: false,
  devicePixelRatio: 1,
  plugins: {
    legend: { position: 'top' },
  },
  scales: {
    r: {
      beginAtZero: true,
      min: 0,
      max: 5,
      ticks: { stepSize: 1 },
    },
  },
}

const hijosFiltrados = computed(() => {
  return search.value
    ? hijos.value.filter((h) =>
        h.nombre_completo.toLowerCase().includes(search.value.toLowerCase()),
      )
    : hijos.value
})
</script>

<style scoped></style>
