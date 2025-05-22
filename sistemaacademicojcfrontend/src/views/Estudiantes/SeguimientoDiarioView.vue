<template>
  <LayoutAuthenticated>
    <div class="p-6 space-y-6">
      <h2 class="text-2xl font-bold">Seguimiento Diario</h2>

      <div class="space-y-1 text-sm">
        <p><strong>Estudiante:</strong> {{ estudiante.nombre }}</p>
        <p><strong>Curso:</strong> {{ estudiante.curso }}</p>
        <p><strong>Nivel:</strong> {{ estudiante.nivel }}</p>
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
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
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

const estudiante = ref({ nombre: '', curso: '', nivel: '' })
const seguimientoData = ref([])
const materiaSeleccionada = ref('')
const radarLabels = [
  'Asistencia',
  'Participación',
  'Disciplina',
  'Puntualidad',
  'Respeto',
  'Tolerancia',
  'Ánimo',
]

onMounted(async () => {
  try {
    const perfil = await api.get('/perfil')
    const p = perfil.data.data.persona_rol.persona
    estudiante.value.nombre = `${p.apellidos_pat} ${p.apellidos_mat} ${p.nombres_persona}`

    const segRes = await api.get('/estudiante-auth/seguimientos')
    seguimientoData.value = segRes.data.data || []

    const inscripcion = seguimientoData.value[0]?.curso
    estudiante.value.curso = `${inscripcion?.grado_curso || ''} ${inscripcion?.paralelo_curso || ''}`
    estudiante.value.nivel = inscripcion?.nivel_educativo?.nombre || ''
  } catch (err) {
    console.error('Error cargando perfil o seguimiento:', err)
  }
})

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
</script>
