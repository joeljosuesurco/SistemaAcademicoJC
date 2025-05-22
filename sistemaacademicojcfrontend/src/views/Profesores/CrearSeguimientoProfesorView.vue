<template>
  <LayoutAuthenticated>
    <div class="p-6 space-y-6">
      <!-- Paso 1: Seleccionar curso -->
      <div v-if="!cursoSeleccionado">
        <h2 class="text-xl font-bold mb-4">Seleccionar Curso</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="curso in cursos"
            :key="curso.id_curso + '-' + curso.id_gestion"
            class="p-4 border rounded shadow hover:bg-slate-100 cursor-pointer"
            @click="seleccionarCurso(curso)"
          >
            <p class="font-semibold">{{ curso.curso_nombre }}</p>
            <p class="text-sm text-gray-600">Nivel: {{ curso.nivel }}</p>
            <p class="text-sm text-gray-600">Gestión: {{ curso.gestion }}</p>
          </div>
        </div>
      </div>

      <!-- Paso 2: Seleccionar estudiante -->
      <div v-else-if="!estudianteSeleccionado">
        <h2 class="text-xl font-bold mb-4">Seleccionar Estudiante</h2>
        <BaseButton
          label="Volver a cursos"
          color="gray"
          small
          class="mb-4"
          @click="cursoSeleccionado = null"
        />
        <div class="flex flex-col md:flex-row md:items-center md:gap-4 mb-4">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre..."
            class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
          />
        </div>
        <table class="w-full text-left bg-white rounded shadow text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2">Nombre completo</th>
              <th class="p-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="est in estudiantesFiltrados"
              :key="est.id_estudiante"
              class="even:bg-gray-50"
            >
              <td class="p-2">{{ est.nombre_completo }}</td>
              <td class="p-2">
                <BaseButton
                  color="info"
                  label="Seleccionar"
                  small
                  @click="seleccionarEstudiante(est)"
                />
              </td>
            </tr>
            <tr v-if="!estudiantesFiltrados.length">
              <td colspan="2" class="p-4 text-center text-gray-500">
                No hay estudiantes para mostrar.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paso 3: Formulario de seguimiento -->
      <div v-else>
        <h2 class="text-xl font-bold mb-2">
          Registrar Seguimiento de {{ estudianteSeleccionado.nombre_completo }}
        </h2>
        <BaseButton
          label="Volver a estudiantes"
          color="gray"
          small
          class="mb-4"
          @click="volverAEstudiantes"
        />

        <!-- Selección de Materia -->
        <div class="mb-4">
          <label class="block mb-1 font-medium">Materia</label>
          <select v-model="form.materias_id_materia" class="w-full px-4 py-2 border rounded">
            <option disabled value="">Selecciona una materia</option>
            <option
              v-for="m in cursoSeleccionado.materias"
              :key="m.id_materia"
              :value="m.id_materia"
            >
              {{ m.area }} ({{ m.sigla }})
            </option>
          </select>
        </div>

        <!-- Indicadores y Radar -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Sliders -->
          <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl shadow-md">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Valoraciones (0-5)</h3>
            <div v-for="campo in campos" :key="campo.name" class="flex items-center gap-4 mb-4">
              <label class="w-36 text-right font-medium text-gray-600">
                {{ campo.label }}
              </label>
              <input
                type="range"
                min="0"
                max="5"
                v-model.number="form[campo.name]"
                class="flex-1 h-2"
              />
              <div class="w-8 text-center font-semibold text-gray-800">
                {{ form[campo.name] }}
              </div>
              <div class="w-28 text-left text-gray-700 text-sm">
                {{ labelFor(campo.name) }}
              </div>
            </div>
          </div>

          <!-- Radar -->
          <div
            class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-md flex flex-col h-[360px] overflow-hidden"
          >
            <h3 class="text-lg font-semibold text-blue-700 mb-4 text-center">Vista Previa</h3>
            <Radar :data="chartData" :options="chartOptions" width="300" height="300" />
          </div>
        </div>

        <!-- Observaciones -->
        <div class="mt-6">
          <label class="block mb-1 font-medium">Observaciones</label>
          <textarea
            v-model="form.observaciones"
            rows="4"
            class="w-full px-4 py-2 border rounded"
            placeholder="Ingrese observaciones obligatorias..."
          ></textarea>
        </div>

        <!-- Botones -->
        <div class="mt-6 flex gap-4">
          <BaseButton color="primary" label="Registrar Seguimiento" @click="confirmarEnvio" />
          <BaseButton color="secondary" label="Cancelar" @click="volverAEstudiantes" />
        </div>

        <!-- Modal de confirmación -->
        <CardBoxModal
          v-model="mostrarConfirmacion"
          title="Confirmar registro"
          button="success"
          button-label="Confirmar"
          :has-cancel="true"
          @confirm="registrarSeguimiento"
        >
          ¿Desea registrar este seguimiento?
        </CardBoxModal>
        <CardBoxModal
          v-model="mostrarErrorMateria"
          title="Validación Requerida"
          button="danger"
          button-label="Entendido"
        >
          <p>Debe escoger una materia antes de registrar el seguimiento.</p>
        </CardBoxModal>

        <CardBoxModal
          v-model="mostrarExito"
          title="Registro Exitoso"
          button="success"
          button-label="Aceptar"
        >
          <p>Registro de seguimiento diario exitoso.</p>
        </CardBoxModal>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
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
import CardBoxModal from '@/components/CardBoxModal.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'

Chart.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

const cursos = ref([])
const cursoSeleccionado = ref(null)
const estudianteSeleccionado = ref(null)
const estudiantesFiltrados = ref([])
const search = ref('')

const mostrarConfirmacion = ref(false)
const mostrarExito = ref(false)
const mostrarErrorMateria = ref(false)

const form = ref({
  materias_id_materia: '',
  cursos_id_curso: null,
  gestiones_id_gestion: null,
  asistencia: 0,
  participacion: 0,
  disciplina: 0,
  puntualidad: 0,
  respeto: 0,
  tolerancia: 0,
  estado_animo: 0,
  observaciones: '',
})

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

async function loadCursos() {
  const res = await api.get('/profesor-auth/cursos-seguimiento')
  cursos.value = res.data.data || []
}
onMounted(loadCursos)

function seleccionarCurso(curso) {
  cursoSeleccionado.value = curso
  estudiantesFiltrados.value = curso.estudiantes || []
  form.value.cursos_id_curso = curso.id_curso
  form.value.gestiones_id_gestion = curso.id_gestion
}

function seleccionarEstudiante(est) {
  estudianteSeleccionado.value = est
  form.value.materias_id_materia = ''
  campos.forEach((c) => (form.value[c.name] = 0))
  form.value.observaciones = ''
}

function volverAEstudiantes() {
  estudianteSeleccionado.value = null
}

function confirmarEnvio() {
  if (!form.value.materias_id_materia) {
    mostrarErrorMateria.value = true
    return
  }
  mostrarConfirmacion.value = true
}

async function registrarSeguimiento() {
  mostrarConfirmacion.value = false

  const payload = {
    estudiantes_id_estudiante: estudianteSeleccionado.value.id_estudiante,
    materias_id_materia: form.value.materias_id_materia,
    cursos_id_curso: form.value.cursos_id_curso,
    gestiones_id_gestion: form.value.gestiones_id_gestion,
    fecha_reg_seg: new Date().toISOString().slice(0, 10),
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
    observaciones: form.value.observaciones.trim() === '' ? 'NINGUNO' : form.value.observaciones,
  }

  try {
    await api.post('/profesor-auth/seguimientos', payload)
    mostrarExito.value = true
    volverAEstudiantes()
  } catch (e) {
    alert('Error al registrar seguimiento')
    console.error(e)
  }
}

const radarLabels = campos.map((c) => c.label)
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
