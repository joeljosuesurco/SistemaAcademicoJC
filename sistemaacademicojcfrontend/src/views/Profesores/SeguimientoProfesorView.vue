<template>
  <LayoutAuthenticated>
    <div class="p-6 space-y-6">
      <!-- Paso 1: Seleccionar Curso -->
      <div v-if="!cursoSeleccionado">
        <h2 class="text-xl font-bold">Seleccionar Curso</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="curso in cursos"
            :key="curso.id_curso + '-' + curso.id_gestion"
            class="p-4 border rounded shadow hover:bg-slate-100 cursor-pointer"
            @click="seleccionarCurso(curso)"
          >
            <p class="font-semibold text-lg">{{ curso.nombre }}</p>
            <p class="text-sm text-gray-600">Nivel: {{ curso.nivel }}</p>
            <p class="text-sm text-gray-600">Gestión: {{ curso.gestion }}</p>
          </div>
        </div>
      </div>

      <!-- Paso 2: Seleccionar Estudiante -->
      <div v-else-if="!estudianteSeleccionado">
        <h2 class="text-xl font-bold">Seleccionar Estudiante</h2>
        <div class="flex flex-col md:flex-row md:items-center md:gap-4 my-4">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por apellido..."
            class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
          />
          <BaseButton
            label="Volver a cursos"
            color="gray"
            small
            @click="cursoSeleccionado = null"
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
              <td class="p-2">
                <BaseButton
                  color="info"
                  label="Ver Seguimiento"
                  small
                  @click="seleccionarEstudiante(est)"
                />
              </td>
            </tr>
            <tr v-if="!estudiantesPaginados.length && !loadingEstudiantes">
              <td colspan="4" class="p-4 text-center text-gray-500">
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

      <!-- Paso 3: Ver Seguimiento -->
      <div v-else>
        <h2 class="text-xl font-bold">
          Seguimiento de {{ estudianteSeleccionado.nombre_completo }}
        </h2>
        <BaseButton
          label="Volver a estudiantes"
          color="gray"
          small
          class="mb-4"
          @click="volverSeleccion"
        />

        <!-- Selección de materia -->
        <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl shadow-md mb-4">
          <h3 class="text-lg font-semibold text-orange-800 mb-2">Materia</h3>
          <select v-model="selectedMateriaId" class="w-full px-4 py-2 border rounded">
            <option disabled value="">Selecciona una materia</option>
            <option
              v-for="m in cursoSeleccionado.materias"
              :key="m.id_materia"
              :value="m.id_materia"
            >
              {{ m.area_materia }}
            </option>
          </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

          <!-- Radar solo visual -->
          <div
            class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-md flex flex-col h-[360px] overflow-hidden"
          >
            <h3 class="text-lg font-semibold text-blue-700 mb-4 text-center">
              Rendimiento en {{ selectedDate || '—' }}
            </h3>

            <div
              v-if="selectedDate && selectedMateriaId"
              class="flex-1 flex justify-center items-center"
            >
              <Radar
                :data="chartData"
                :options="chartOptions"
                :key="selectedDate + '-' + selectedMateriaId"
                width="300"
                height="300"
              />
            </div>

            <div v-else class="text-center text-gray-500 mt-auto mb-auto">
              Selecciona materia y fecha para ver el gráfico
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
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

// Estado general
const cursos = ref([])
const cursoSeleccionado = ref(null)
const estudiantes = ref([])
const estudianteSeleccionado = ref(null)
const search = ref('')
const currentPage = ref(1)
const pageSize = 10
const loadingEstudiantes = ref(false)
const errorEstudiantes = ref(null)

const selectedMateriaId = ref('')
const selectedDate = ref(null)
const seguimientosList = ref([])

// Radar y calendario
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

// Cargar cursos asignados al profesor
onMounted(async () => {
  const res = await api.get('/profesor-auth/cursos')
  const cursosMap = new Map()
  res.data.data.forEach((item) => {
    const curso = item.curso
    const gestion = item.gestion
    const key = `${curso.id_curso}-${gestion.id_gestion}`
    if (!cursosMap.has(key)) {
      cursosMap.set(key, {
        id_curso: curso.id_curso,
        id_gestion: gestion.id_gestion,
        nombre: `${curso.grado_curso} ${curso.paralelo_curso}`,
        nivel: curso.nivel_educativo?.nombre || 'Desconocido',
        gestion: gestion.gestion,
        materias: [],
      })
    }
    cursosMap.get(key).materias.push(item.materia)
  })
  cursos.value = Array.from(cursosMap.values())
})

function seleccionarCurso(curso) {
  cursoSeleccionado.value = curso
  estudianteSeleccionado.value = null
  cargarEstudiantesDelCurso()
}

async function cargarEstudiantesDelCurso() {
  loadingEstudiantes.value = true
  try {
    const res = await api.get('/profesor-auth/estudiantes-seguimiento', {
      params: {
        curso_id: cursoSeleccionado.value.id_curso,
        gestion_id: cursoSeleccionado.value.id_gestion,
      },
    })
    estudiantes.value = res.data.data.estudiantes.map((e) => {
      const p = e.estudiante.persona_rol.persona
      return {
        id_estudiante: e.estudiante.id_estudiante,
        apellidos_pat: p.apellidos_pat,
        apellidos_mat: p.apellidos_mat,
        nombres: p.nombres_persona,
        nombre_completo: `${p.apellidos_pat} ${p.apellidos_mat} ${p.nombres_persona}`,
      }
    })
  } catch {
    errorEstudiantes.value = 'Error al cargar estudiantes'
  } finally {
    loadingEstudiantes.value = false
  }
}

function seleccionarEstudiante(est) {
  estudianteSeleccionado.value = est
  selectedDate.value = null
  selectedMateriaId.value = ''
  cargarSeguimientos(est.id_estudiante)
}

async function cargarSeguimientos(idEstudiante) {
  const res = await api.get(`/estudiantes/${idEstudiante}/seguimientos`)
  seguimientosList.value = res.data.data || []
}

function volverSeleccion() {
  estudianteSeleccionado.value = null
  selectedMateriaId.value = ''
}

// Filtros
const estudiantesFiltrados = computed(() => {
  if (!search.value) return estudiantes.value
  return estudiantes.value.filter((e) =>
    [e.apellidos_pat, e.apellidos_mat, e.nombres].some((f) =>
      f.toLowerCase().includes(search.value.toLowerCase()),
    ),
  )
})
const totalPages = computed(() => Math.ceil(estudiantesFiltrados.value.length / pageSize))
const estudiantesPaginados = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return estudiantesFiltrados.value.slice(start, start + pageSize)
})

// Calendario
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

// Cuando cambia materia
watch(selectedMateriaId, (id) => {
  if (!id) return
  const registros = seguimientosList.value.filter((s) => s.materias_id_materia === id)
  if (registros.length) {
    selectDate(registros[0].fecha_reg_seg)
    const d = new Date(registros[0].fecha_reg_seg)
    calendarMonth.value = d.getMonth()
    calendarYear.value = d.getFullYear()
  } else {
    selectedDate.value = null
    campos.forEach((c) => (form.value[c.name] = 0))
  }
})

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

// Radar Chart
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
