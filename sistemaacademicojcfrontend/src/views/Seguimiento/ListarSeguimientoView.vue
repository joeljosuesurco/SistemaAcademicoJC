<template>
  <LayoutAuthenticated>
    <div class="p-6 space-y-6">
      <!-- Selección de estudiante -->
      <div v-if="!estudianteSeleccionado">
        <h2 class="text-xl font-bold">Seleccionar Estudiante</h2>
        <div class="flex flex-col md:flex-row md:items-center md:gap-4 my-4">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por apellido o curso..."
            class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
          />
        </div>
        <div v-if="loadingEstudiantes" class="text-gray-500">Cargando estudiantes…</div>
        <div v-else-if="errorEstudiantes" class="text-red-600">{{ errorEstudiantes }}</div>
        <table v-else class="w-full text-left bg-white rounded shadow">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2">Apellido Paterno</th>
              <th class="p-2">Apellido Materno</th>
              <th class="p-2">Nombres</th>
              <th class="p-2">Curso</th>
              <th class="p-2">Nivel</th>
              <th class="p-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="est in estudiantesPaginados"
              :key="est.id_estudiante"
              class="even:bg-gray-50"
            >
              <td class="p-2">{{ est.apellidos_pat }}</td>
              <td class="p-2">{{ est.apellidos_mat }}</td>
              <td class="p-2">{{ est.nombres }}</td>
              <td class="p-2">{{ est.curso }}</td>
              <td class="p-2">{{ est.nivel_educativo.nombre }}</td>
              <td class="p-2">
                <BaseButton
                  color="info"
                  label="Seleccionar"
                  small
                  @click="seleccionarEstudiante(est)"
                />
              </td>
            </tr>
            <tr v-if="!estudiantesPaginados.length && !loadingEstudiantes">
              <td colspan="6" class="p-4 text-center text-gray-500">
                No hay estudiantes para mostrar.
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-4">
          <BaseButton
            label="« Anterior"
            :disabled="currentPage === 1"
            small
            @click="currentPage--"
          />
          <span>Página {{ currentPage }} de {{ totalPages }}</span>
          <BaseButton
            label="Siguiente »"
            :disabled="currentPage === totalPages"
            small
            @click="currentPage++"
          />
        </div>
      </div>

      <!-- Vista de seguimiento -->
      <div v-else>
        <h2 class="text-xl font-bold">
          Seguimientos de {{ estudianteSeleccionado.nombre_completo }}
        </h2>

        <!-- Info y Materias -->
        <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl shadow-md">
          <h3 class="text-lg font-semibold mb-2 text-orange-800">Información del Estudiante</h3>
          <p><strong>Nombre:</strong> {{ estudianteSeleccionado.nombre_completo }}</p>
          <p><strong>Curso:</strong> {{ estudianteSeleccionado.curso }}</p>
          <p>
            <strong>Nivel Educativo:</strong> {{ estudianteSeleccionado.nivel_educativo.nombre }}
          </p>
          <div class="mt-4">
            <label class="block mb-1 font-medium text-orange-800">Materia</label>
            <select v-model="selectedMateriaId" class="w-full px-4 py-2 border rounded">
              <option disabled value="">Selecciona una materia</option>
              <option
                v-for="m in materiasFiltradasPorNivel"
                :key="m.id_materia"
                :value="m.id_materia"
              >
                {{ m.area_materia }}
              </option>
            </select>
          </div>
        </div>

        <!-- Calendario y Radar -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
          <!-- Calendario -->
          <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl shadow-md">
            <div class="flex justify-between items-center mb-4">
              <button @click="prevMonth" class="p-1 rounded hover:bg-yellow-200 text-yellow-800">
                ‹
              </button>
              <div class="font-semibold text-yellow-800">
                {{ monthNames[calendarMonth] }} {{ calendarYear }}
              </div>
              <button @click="nextMonth" class="p-1 rounded hover:bg-yellow-200 text-yellow-800">
                ›
              </button>
            </div>
            <div class="grid grid-cols-7 text-center text-xs text-gray-600 mb-2">
              <div v-for="d in weekDays" :key="d">{{ d }}</div>
            </div>
            <div class="grid grid-cols-7 text-center">
              <div v-for="blank in firstDayOffset" :key="'b' + blank" class="h-8"></div>
              <button
                v-for="day in daysInMonth"
                :key="day"
                @click="selectDate(makeDate(calendarYear, calendarMonth, day))"
                :class="[
                  'h-8 w-8 flex items-center justify-center rounded-full transition',
                  isSelected(day)
                    ? 'bg-amber-600 text-white'
                    : hasRecord(day)
                      ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 cursor-pointer'
                      : 'text-gray-300 cursor-default',
                ]"
                :disabled="!hasRecord(day)"
              >
                {{ day }}
              </button>
            </div>
          </div>

          <!-- Gráfico Radar -->
          <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-md flex flex-col">
            <h3 class="text-lg font-semibold text-blue-700 mb-4 text-center">
              Rendimiento en {{ selectedDate || '—' }}
            </h3>
            <div v-if="selectedDate && selectedMateriaId" class="flex-1">
              <Radar
                :data="chartData"
                :options="chartOptions"
                :key="selectedDate + '-' + selectedMateriaId"
              />
            </div>
            <div v-else class="text-center text-gray-500">
              Selecciona materia y fecha para ver el gráfico
            </div>
          </div>
        </div>

        <div class="mt-6 text-left">
          <BaseButton
            color="success"
            label="Volver"
            class="px-6 py-3 text-lg"
            @click="volverSeleccion"
          />
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import {
  Chart,
  RadialLinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js'
import { Radar } from 'vue-chartjs'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'

Chart.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

// Búsqueda y paginación
const search = ref('')
const estudiantes = ref([])
const loadingEstudiantes = ref(true)
const errorEstudiantes = ref(null)
const currentPage = ref(1)
const pageSize = 10

// Selección y datos
const estudianteSeleccionado = ref(null)
const seguimientosList = ref([])
const materiasList = ref([])
const selectedDate = ref(null)
const selectedMateriaId = ref('')

// Calendario
const calendarMonth = ref(new Date().getMonth())
const calendarYear = ref(new Date().getFullYear())
const weekDays = ['LU', 'MA', 'MI', 'JU', 'VI', 'SA', 'DO']
const monthNames = [
  'enero',
  'febrero',
  'marzo',
  'abril',
  'mayo',
  'junio',
  'julio',
  'agosto',
  'septiembre',
  'octubre',
  'noviembre',
  'diciembre',
]

// Radar métricas
const campos = [
  { name: 'asistencia', label: 'Asistencia' },
  { name: 'participacion', label: 'Participación' },
  { name: 'disciplina', label: 'Disciplina' },
  { name: 'puntualidad', label: 'Puntualidad' },
  { name: 'respeto', label: 'Respeto' },
  { name: 'tolerancia', label: 'Tolerancia' },
  { name: 'estado_animo', label: 'Estado Ánimo' },
]
const form = ref({
  asistencia: 0,
  participacion: 0,
  disciplina: 0,
  puntualidad: 0,
  respeto: 0,
  tolerancia: 0,
  estado_animo: 0,
})

// Carga inicial de estudiantes
async function loadEstudiantes() {
  try {
    const res = await api.get('/info/estudiantes')

    estudiantes.value = (res.data.estudiantes || [])
      .filter((est) =>
        est.cursos.some((c) => c.estado === 'inscrito' && c.gestion.estado_gestion === 'activa'),
      )
      .map((est) => {
        const p = est.persona_rol.persona
        const insc = est.cursos.find(
          (c) => c.estado === 'inscrito' && c.gestion.estado_gestion === 'activa',
        )
        const cu = insc?.curso
        return {
          id_estudiante: est.id_estudiante,
          apellidos_pat: p.apellidos_pat,
          apellidos_mat: p.apellidos_mat,
          nombres: p.nombres_persona,
          nombre_completo: `${p.apellidos_pat} ${p.apellidos_mat} ${p.nombres_persona}`,
          curso: cu ? `${cu.grado_curso} ${cu.paralelo_curso}` : '—',
          nivel_educativo: cu?.nivel_educativo,
        }
      })
  } catch {
    errorEstudiantes.value = 'Error al cargar estudiantes'
  } finally {
    loadingEstudiantes.value = false
  }
}
onMounted(loadEstudiantes)

const estudiantesFiltrados = computed(() => {
  if (!search.value) return estudiantes.value
  return estudiantes.value.filter((e) =>
    [e.apellidos_pat, e.apellidos_mat, e.nombres, e.curso].some((f) =>
      f.toLowerCase().includes(search.value.toLowerCase()),
    ),
  )
})
const totalPages = computed(() => Math.ceil(estudiantesFiltrados.value.length / pageSize))
const estudiantesPaginados = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return estudiantesFiltrados.value.slice(start, start + pageSize)
})

// Seleccionar estudiante
async function seleccionarEstudiante(est) {
  estudianteSeleccionado.value = est
  selectedDate.value = null
  selectedMateriaId.value = ''
  // cargar seguimientos
  const segRes = await api.get(`/estudiantes/${est.id_estudiante}/seguimientos`)
  seguimientosList.value = segRes.data.data || []
  // cargar materias por nivel
  const matRes = await api.get(`/materias?nivel_id=${est.nivel_educativo.id}`)
  materiasList.value = matRes.data.data || []
}

// Filtrar materias por nivel
const materiasFiltradasPorNivel = computed(() =>
  materiasList.value.filter(
    (m) => m.nivel_educativo_id === estudianteSeleccionado.value.nivel_educativo.id,
  ),
)

// Volver atrás
function volverSeleccion() {
  estudianteSeleccionado.value = null
  seguimientosList.value = []
  materiasList.value = []
  selectedMateriaId.value = ''
}

// Helpers calendario
const firstDayOffset = computed(
  () => (new Date(calendarYear.value, calendarMonth.value, 1).getDay() + 6) % 7,
)
const daysInMonth = computed(() =>
  new Date(calendarYear.value, calendarMonth.value + 1, 0).getDate(),
)
function makeDate(y, m, d) {
  return `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
}
function hasRecord(day) {
  return seguimientosList.value.some(
    (s) =>
      s.materias_id_materia === selectedMateriaId.value &&
      s.fecha_reg_seg === makeDate(calendarYear.value, calendarMonth.value, day),
  )
}
function isSelected(day) {
  return selectedDate.value === makeDate(calendarYear.value, calendarMonth.value, day)
}
function prevMonth() {
  if (calendarMonth.value === 0) {
    calendarMonth.value = 11
    calendarYear.value--
  } else calendarMonth.value--
}
function nextMonth() {
  if (calendarMonth.value === 11) {
    calendarMonth.value = 0
    calendarYear.value++
  } else calendarMonth.value++
}

// Al cambiar materia: si no hay seguimientos, limpiar
watch(selectedMateriaId, (id) => {
  if (!id) return
  const regs = seguimientosList.value.filter((s) => s.materias_id_materia === id)
  if (regs.length) {
    selectDate(regs[0].fecha_reg_seg)
    const d = new Date(regs[0].fecha_reg_seg)
    calendarMonth.value = d.getMonth()
    calendarYear.value = d.getFullYear()
  } else {
    selectedDate.value = null
    campos.forEach((c) => (form.value[c.name] = 0))
  }
})

// Seleccionar fecha y mapear métricas
function selectDate(fecha) {
  selectedDate.value = fecha
  const seg = seguimientosList.value.find(
    (s) => s.materias_id_materia === selectedMateriaId.value && s.fecha_reg_seg === fecha,
  )
  if (!seg) return
  form.value.asistencia = seg.asistencia === 'Sí' ? 5 : 0
  form.value.participacion =
    seg.participacion === 'Alta' ? 5 : seg.participacion === 'Media' ? 3 : 1
  form.value.disciplina =
    seg.disciplina === 'Excelente'
      ? 5
      : seg.disciplina === 'Buena'
        ? 4
        : seg.disciplina === 'Regular'
          ? 3
          : 1
  form.value.puntualidad =
    seg.puntualidad === 'Excelente'
      ? 5
      : seg.puntualidad === 'Buena'
        ? 4
        : seg.puntualidad === 'Regular'
          ? 3
          : 1
  form.value.respeto = seg.respeto === 'Sí' ? 5 : 0
  form.value.tolerancia = seg.tolerancia === 'Sí' ? 5 : 0
  form.value.estado_animo =
    seg.estado_animo === 'Muy bien'
      ? 5
      : seg.estado_animo === 'Bien'
        ? 4
        : seg.estado_animo === 'Neutro'
          ? 3
          : seg.estado_animo === 'Estresado'
            ? 2
            : 1
}

// Radar chart
const radarLabels = campos.map((c) => c.label)
const chartData = computed(() => ({
  labels: radarLabels,
  datasets: [
    {
      label: estudianteSeleccionado.value?.nombres,
      data: campos.map((c) => form.value[c.name]),
      backgroundColor: 'rgba(59,130,246,0.3)',
      borderColor: 'rgba(59,130,246,0.8)',
      pointBackgroundColor: 'rgba(37,99,235,0.8)',
      pointBorderColor: '#fff',
    },
  ],
}))
const chartOptions = {
  maintainAspectRatio: false,
  scales: { r: { beginAtZero: true, min: 0, max: 5, ticks: { stepSize: 1 } } },
  plugins: { legend: { position: 'top' } },
}
</script>
