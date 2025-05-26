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
            placeholder="Buscar por nombre o curso..."
            class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
          />
        </div>
        <div v-if="loadingEstudiantes" class="text-gray-500">Cargando estudiantes...</div>
        <div v-else-if="errorEstudiantes" class="text-red-600">{{ errorEstudiantes }}</div>
        <table v-else class="w-full text-left bg-white rounded shadow">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2">Apellido Paterno</th>
              <th class="p-2">Apellido Materno</th>
              <th class="p-2">Nombres</th>
              <th class="p-2">Curso</th>
              <th class="p-2">Nivel Educativo</th>
              <th class="p-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="est in estudiantesPaginados" :key="est.id_estudiante">
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
          </tbody>
        </table>
        <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-4">
          <BaseButton
            label="« Anterior"
            :disabled="currentPage === 1"
            @click="currentPage--"
            small
          />
          <span>Página {{ currentPage }} de {{ totalPages }}</span>
          <BaseButton
            label="Siguiente »"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
            small
          />
        </div>
      </div>

      <!-- Formulario de seguimiento -->
      <div v-else>
        <h2 class="text-xl font-bold">
          Registrar Seguimiento de {{ estudianteSeleccionado.nombre_completo }}
        </h2>

        <!-- Selección de Materia arriba -->
        <div class="mt-4">
          <label class="block mb-1 font-medium">Materia</label>
          <select v-model="form.materias_id_materia" class="w-full px-4 py-2 border rounded">
            <option disabled value="">Selecciona una materia</option>
            <option v-for="m in materias" :key="m.id_materia" :value="m.id_materia">
              {{ m.sigla_materia }} – {{ m.area_materia }}
            </option>
          </select>
          <span v-if="errors.materias_id_materia" class="text-red-600 text-sm">
            {{ errors.materias_id_materia[0] }}
          </span>
        </div>

        <!-- Grid: Sliders y gráfico -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <!-- Cardboard: Valoraciones -->
          <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl shadow-md">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Valoraciones (0-5)</h3>
            <div v-for="campo in campos" :key="campo.name" class="flex items-center gap-4 mb-4">
              <label class="w-36 text-right font-medium text-gray-600">
                {{ campo.label }}
              </label>
              <div v-if="campo.name === 'asistencia'">
                <input
                  type="range"
                  min="0"
                  max="5"
                  step="5"
                  v-model.number="form[campo.name]"
                  class="w-full h-2"
                />
              </div>
              <div v-else>
                <input
                  type="range"
                  min="0"
                  max="5"
                  step="1"
                  v-model.number="form[campo.name]"
                  class="w-full h-2"
                />
              </div>
              <div class="w-8 text-center font-semibold text-gray-800">
                {{ form[campo.name] }}
              </div>
              <div class="w-24 text-left text-gray-700">
                {{ labelFor(campo.name) }}
              </div>
              <span v-if="errors[campo.name]" class="text-red-600 text-sm ml-44">
                {{ errors[campo.name][0] }}
              </span>
            </div>
          </div>

          <!-- Cardboard: Gráfico Radar -->
          <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-md flex flex-col">
            <h3 class="text-lg font-semibold text-blue-700 mb-2 text-center">
              Vista Previa de Métricas
            </h3>
            <div class="flex-1">
              <Radar :data="chartData" :options="chartOptions" />
            </div>
          </div>
        </div>

        <!-- Observaciones -->
        <form @submit.prevent="submitSeguimiento" class="space-y-6 mt-4">
          <div>
            <label class="block mb-1">Observaciones</label>
            <textarea
              v-model="form.observaciones_seguimiento"
              rows="4"
              class="w-full px-4 py-2 border rounded"
              placeholder="Escribe tus observaciones aquí"
            ></textarea>
            <span v-if="errors.observaciones_seguimiento" class="text-red-600 text-sm">
              {{ errors.observaciones_seguimiento[0] }}
            </span>
          </div>
          <div class="flex space-x-4">
            <BaseButton color="primary" label="Registrar" @click="modalConfirmar = true" />
            <BaseButton color="secondary" label="Cancelar" @click="cancelar" />
          </div>
        </form>
      </div>
    </div>
    <!-- Modal: Confirmar registro -->
    <div
      v-if="modalConfirmar"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
        <h2 class="text-lg font-bold text-blue-700">¿Desea registrar el seguimiento diario?</h2>
        <div class="flex justify-end gap-2 mt-4">
          <button
            @click="modalConfirmar = false"
            class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
          >
            Cancelar
          </button>
          <button
            @click="confirmarRegistroSeguimiento"
            class="px-4 py-2 text-sm text-white bg-blue-600 rounded"
          >
            Registrar
          </button>
        </div>
      </div>
    </div>
    <!-- Modal: Éxito -->
    <div
      v-if="modalExito"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4 text-center">
        <h2 class="text-lg font-bold text-green-700">Registro exitoso</h2>
        <p class="text-gray-700">Seguimiento diario registrado correctamente.</p>
        <div class="flex justify-center mt-4">
          <button
            @click="modalExito = false"
            class="px-4 py-2 text-sm text-white bg-green-600 rounded"
          >
            Aceptar
          </button>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
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

const modalConfirmar = ref(false)
const modalExito = ref(false)

Chart.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

const estudiantes = ref([])
const loadingEstudiantes = ref(true)
const errorEstudiantes = ref(null)
const search = ref('')
const estudianteSeleccionado = ref(null)
const materias = ref([])
const currentPage = ref(1)
const pageSize = 10

const form = ref({
  fecha_reg_seg: new Date().toISOString().substr(0, 10),
  estudiantes_id_estudiante: null,
  cursos_id_curso: null,
  materias_id_materia: '',
  gestiones_id_gestion: null,
  asistencia: 0,
  participacion: 0,
  disciplina: 0,
  puntualidad: 0,
  respeto: 0,
  tolerancia: 0,
  estado_animo: 0,
  observaciones_seguimiento: '',
})

const errors = ref({})

const campos = [
  { name: 'asistencia', label: 'Asistencia' },
  { name: 'participacion', label: 'Participación' },
  { name: 'disciplina', label: 'Disciplina' },
  { name: 'puntualidad', label: 'Puntualidad' },
  { name: 'respeto', label: 'Respeto' },
  { name: 'tolerancia', label: 'Tolerancia' },
  { name: 'estado_animo', label: 'Estado Ánimo' },
]

function labelFor(field) {
  const v = form.value[field]
  switch (field) {
    case 'asistencia':
      return v >= 3 ? 'Asistió' : 'No asistió'
    case 'participacion':
      return v >= 4 ? 'Alta' : v >= 2 ? 'Media' : 'Baja'
    case 'disciplina':
      return v === 5 ? 'Excelente' : v === 4 ? 'Buena' : v === 3 ? 'Regular' : 'Mala'
    case 'puntualidad':
      return v === 5 ? 'Excelente' : v === 4 ? 'Buena' : v === 3 ? 'Regular' : 'Mala'
    case 'respeto':
      return v >= 3 ? 'Sí' : 'No'
    case 'tolerancia':
      return v >= 3 ? 'Sí' : 'No'
    case 'estado_animo':
      return v === 5
        ? 'Muy bien'
        : v === 4
          ? 'Bien'
          : v === 3
            ? 'Neutro'
            : v === 2
              ? 'Estresado'
              : 'Malo'
    default:
      return ''
  }
}

async function loadEstudiantes() {
  try {
    const res = await api.get('/info/estudiantes')
    estudiantes.value = (res.data.estudiantes || [])
      .filter((est) =>
        est.cursos.some((c) => c.estado === 'inscrito' && c.gestion.estado_gestion === 'activa'),
      )
      .map((est) => {
        const p = est.persona_rol.persona
        const cr = est.cursos.find(
          (c) => c.estado === 'inscrito' && c.gestion.estado_gestion === 'activa',
        )
        const cu = cr.curso
        return {
          ...est,
          nombre_completo: `${p.apellidos_pat} ${p.apellidos_mat} ${p.nombres_persona}`.trim(),
          id_estudiante: est.id_estudiante,
          apellidos_pat: p.apellidos_pat,
          apellidos_mat: p.apellidos_mat,
          nombres: p.nombres_persona,
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

const estudiantesFiltrados = computed(() =>
  !search.value
    ? estudiantes.value
    : estudiantes.value.filter(
        (est) =>
          est.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
          est.curso.toLowerCase().includes(search.value.toLowerCase()),
      ),
)
const totalPages = computed(() => Math.ceil(estudiantesFiltrados.value.length / pageSize))
const estudiantesPaginados = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return estudiantesFiltrados.value.slice(start, start + pageSize)
})

async function seleccionarEstudiante(est) {
  estudianteSeleccionado.value = est
  errors.value = {}
  form.value.estudiantes_id_estudiante = est.id_estudiante

  // traer curso/gestión
  const cfg = await api.get(`/seguimiento/create-form/${est.id_estudiante}`)
  form.value.cursos_id_curso = cfg.data.data.curso_id
  form.value.gestiones_id_gestion = cfg.data.data.gestion_id

  // traer todas las materias
  const all = await api.get('/materias')
  materias.value = all.data.data || []

  // filtrar por nivel educativo
  const nivelId = est.nivel_educativo.id
  materias.value = materias.value.filter((m) => m.nivel_educativo_id === nivelId)

  // preseleccionar primera
  form.value.materias_id_materia = materias.value[0]?.id_materia || ''
  //por defecto
  form.value.observaciones_seguimiento = 'ninguno'
}

async function submitSeguimiento() {
  errors.value = {}
  const payload = {
    ...form.value,
    asistencia: form.value.asistencia >= 3 ? 'Sí' : 'No',
    participacion:
      form.value.participacion >= 4 ? 'Alta' : form.value.participacion >= 2 ? 'Media' : 'Baja',
    disciplina:
      form.value.disciplina === 5
        ? 'Excelente'
        : form.value.disciplina === 4
          ? 'Buena'
          : form.value.disciplina === 3
            ? 'Regular'
            : 'Mala',
    puntualidad:
      form.value.puntualidad === 5
        ? 'Excelente'
        : form.value.puntualidad === 4
          ? 'Buena'
          : form.value.puntualidad === 3
            ? 'Regular'
            : 'Mala',
    respeto: form.value.respeto >= 3 ? 'Sí' : 'No',
    tolerancia: form.value.tolerancia >= 3 ? 'Sí' : 'No',
    estado_animo:
      form.value.estado_animo === 5
        ? 'Muy bien'
        : form.value.estado_animo === 4
          ? 'Bien'
          : form.value.estado_animo === 3
            ? 'Neutro'
            : form.value.estado_animo === 2
              ? 'Estresado'
              : 'Malo',
  }
  try {
    await api.post('/seguimiento', payload)
    estudianteSeleccionado.value = null
    materias.value = []
    form.value.materias_id_materia = ''
    currentPage.value = 1
    loadEstudiantes()
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    }
  }
}

function cancelar() {
  estudianteSeleccionado.value = null
  materias.value = []
  form.value.materias_id_materia = ''
}

const radarLabels = [
  'Asistencia',
  'Participación',
  'Disciplina',
  'Puntualidad',
  'Respeto',
  'Tolerancia',
  'Estado Ánimo',
]
const chartData = computed(() => ({
  labels: radarLabels,
  datasets: [
    {
      label: estudianteSeleccionado.value?.nombre_completo || '',
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

async function confirmarRegistroSeguimiento() {
  await submitSeguimiento()
  modalConfirmar.value = false
  modalExito.value = true
}
</script>
