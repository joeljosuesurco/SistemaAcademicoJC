<script setup>
import { ref, onMounted } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'
import api from '@/services/api'
import { Bar } from 'vue-chartjs'
import { Chart, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const seccion = ref('materias')
const cursos = ref([])
const materias = ref([])
const cursoSeleccionado = ref('')
const materiaSeleccionada = ref('')
const datos = ref([])
const cargando = ref(false)
const error = ref('')
const chartData = ref(null)

const chartOptions = {
  responsive: true,
  indexAxis: 'y',
  plugins: {
    legend: { position: 'top' },
  },
  scales: {
    x: { beginAtZero: true, max: 5 },
  },
}

onMounted(async () => {
  try {
    const resCursos = await api.get('/cursos')
    cursos.value = resCursos.data.data

    const resMaterias = await api.get('/materias')
    materias.value = resMaterias.data.data
  } catch {
    error.value = 'Error al cargar cursos o materias'
  }
})

const generarReporte = async () => {
  cargando.value = true
  error.value = ''
  datos.value = []
  chartData.value = null

  try {
    let res

    if (seccion.value === 'materias') {
      res = await api.get('/reportes/rendimiento/materias')
      datos.value = res.data.data
      chartData.value = generarChartData(datos.value, 'materia')
    } else if (seccion.value === 'cursos') {
      res = await api.get('/reportes/rendimiento/cursos')
      datos.value = res.data.data
      chartData.value = generarChartData(datos.value, 'curso')
    } else if (seccion.value === 'estudiantes') {
      if (!cursoSeleccionado.value || !materiaSeleccionada.value) {
        error.value = 'Selecciona curso y materia'
        cargando.value = false
        return
      }
      res = await api.get(
        `/reportes/rendimiento/estudiantes/${cursoSeleccionado.value}/${materiaSeleccionada.value}`,
      )
      datos.value = res.data.data
      chartData.value = generarChartData(datos.value, 'estudiante')
    }
  } catch {
    error.value = 'Error al generar reporte'
  } finally {
    cargando.value = false
  }
}

function generarChartData(data, tipo) {
  let labels = []
  if (tipo === 'materia') {
    labels = data.map((d) => `${d.area_materia} (${d.nivel_educativo_nombre})`)
  } else if (tipo === 'curso') {
    labels = data.map((d) => `${d.grado_curso} ${d.paralelo_curso}`)
  } else {
    labels = data.map((d) => d.nombre_completo)
  }

  return {
    labels,
    datasets: [
      { label: 'Asistencia', data: data.map((d) => d.asistencia), backgroundColor: '#6366f1' },
      {
        label: 'Participación',
        data: data.map((d) => d.participacion),
        backgroundColor: '#3b82f6',
      },
      { label: 'Disciplina', data: data.map((d) => d.disciplina), backgroundColor: '#10b981' },
      { label: 'Puntualidad', data: data.map((d) => d.puntualidad), backgroundColor: '#f59e0b' },
      { label: 'Respeto', data: data.map((d) => d.respeto), backgroundColor: '#8b5cf6' },
      { label: 'Tolerancia', data: data.map((d) => d.tolerancia), backgroundColor: '#ec4899' },
      {
        label: 'Estado de ánimo',
        data: data.map((d) => d.estado_animo),
        backgroundColor: '#ef4444',
      },
    ],
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold">Reportes de Rendimiento</h1>

      <div class="bg-white border rounded shadow p-4 space-y-4">
        <div class="flex gap-4">
          <BaseButton
            label="Por Materia"
            @click="seccion = 'materias'"
            :color="seccion === 'materias' ? 'primary' : 'default'"
          />
          <BaseButton
            label="Por Curso"
            @click="seccion = 'cursos'"
            :color="seccion === 'cursos' ? 'primary' : 'default'"
          />
          <BaseButton
            label="Por Estudiante"
            @click="seccion = 'estudiantes'"
            :color="seccion === 'estudiantes' ? 'primary' : 'default'"
          />
        </div>

        <div v-if="seccion === 'estudiantes'" class="grid md:grid-cols-2 gap-4">
          <select v-model="cursoSeleccionado" class="w-full px-4 py-2 border rounded">
            <option value="">Seleccionar curso</option>
            <option v-for="c in cursos" :key="c.id_curso" :value="c.id_curso">
              {{ c.grado_curso }} {{ c.paralelo_curso }} - {{ c.nivel_educativo.codigo }}
            </option>
          </select>
          <select v-model="materiaSeleccionada" class="w-full px-4 py-2 border rounded">
            <option value="">Seleccionar materia</option>
            <option v-for="m in materias" :key="m.id_materia" :value="m.id_materia">
              {{ m.sigla_materia }} - {{ m.area_materia }}
            </option>
          </select>
        </div>

        <div>
          <BaseButton label="Generar" color="success" @click="generarReporte" />
        </div>
      </div>

      <div v-if="error" class="text-red-600">{{ error }}</div>
      <div v-if="cargando" class="text-gray-500">Generando...</div>

      <Bar v-if="chartData" :data="chartData" :options="chartOptions" style="max-height: 500px" />
    </div>
  </LayoutAuthenticated>
</template>
