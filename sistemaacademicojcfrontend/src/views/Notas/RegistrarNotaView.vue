<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'

const estudiantes = ref([])
const estudianteSeleccionado = ref(null)
const search = ref('')
const currentPage = ref(1)
const pageSize = 10
const mensaje = ref('')
const error = ref('')
const showModal = ref(false)
const confirmMessage = ref('')
const actionToConfirm = ref(null)

const materias = ref([])
const gestiones = ref([])
const periodoSeleccionado = ref('1')
const notasExistentes = ref([])
const materiasFiltradas = ref([])

const form = ref({
  estudiante_id: null,
  gestion_id: null,
  curso_id: null,
  periodo: 'trimestre',
  numero_periodo: '1',
  notas: [],
})

// Recalcula nota_final automáticamente aplicando pesos, redondeando a entero y capando en 100
watch(
  () => form.value.notas,
  (notas) => {
    const pesos = {
      ser_docente: 5,
      saber_docente: 45,
      hacer_docente: 40,
      decidir_docente: 5,
      ser_autoeval: 5,
      decidir_autoeval: 5,
    }
    notas.forEach((n) => {
      let total = 0
      for (const [dim, raw] of Object.entries(n.dimensiones)) {
        const valor = parseFloat(raw) || 0
        const pct = pesos[dim] || 0
        total += valor * (pct / 100)
      }
      // Redondeamos al entero más cercano y capamos en 100
      n.nota_final = Math.min(Math.round(total), 100)
    })
  },
  { deep: true },
)

const filteredEstudiantes = computed(() =>
  estudiantes.value.filter((est) => {
    const p = est.persona_rol.persona
    const fullName = `${p.nombres_persona} ${p.apellidos_pat} ${p.apellidos_mat}`.toLowerCase()
    return fullName.includes(search.value.toLowerCase())
  }),
)

const totalPages = computed(() => Math.ceil(filteredEstudiantes.value.length / pageSize))

const paginatedEstudiantes = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredEstudiantes.value.slice(start, start + pageSize)
})

onMounted(async () => {
  try {
    const [resEst, resMat, resGes] = await Promise.all([
      api.get('/estudiantes'),
      api.get('/materias'),
      api.get('/gestiones'),
    ])
    estudiantes.value = resEst.data.data || []
    materias.value = resMat.data.data || []
    gestiones.value = resGes.data.data || []
  } catch (e) {
    console.error('Error inicial:', e)
    error.value = 'No se pudieron cargar datos iniciales.'
  }
})

function seleccionarEstudiante(est) {
  api
    .get(`/estudiantes/${est.id_estudiante}`)
    .then((res) => {
      const data = res.data.data
      const insc = data.cursos.find((c) => c.estado === 'inscrito')
      if (!insc) throw new Error('Estudiante no inscrito activo.')

      estudianteSeleccionado.value = est
      form.value.estudiante_id = est.id_estudiante
      form.value.curso_id = insc.curso.id_curso
      form.value.gestion_id = insc.gestion.id_gestion

      materiasFiltradas.value = materias.value.filter(
        (m) => m.nivel_educativo_id === insc.curso.nivel_educativo_id,
      )
      cargarNotasExistentes()
    })
    .catch((e) => {
      console.error(e)
      error.value = 'No se pudo cargar estudiante.'
    })
}

watch(periodoSeleccionado, cargarNotasExistentes)

async function cargarNotasExistentes() {
  if (!estudianteSeleccionado.value || !form.value.gestion_id) return
  try {
    const res = await api.get(
      `/notas?estudiante_id=${form.value.estudiante_id}&gestion_id=${form.value.gestion_id}&numero_periodo=${periodoSeleccionado.value}`,
    )
    const data = Array.isArray(res.data.data) ? res.data.data : []
    notasExistentes.value = data

    form.value.notas = materiasFiltradas.value.map((m) => {
      const nota = notasExistentes.value.find((n) => n.materia.id === m.id_materia)
      const dims = {
        ser_docente: nota?.valores_raw.ser_docente ?? '',
        saber_docente: nota?.valores_raw.saber_docente ?? '',
        hacer_docente: nota?.valores_raw.hacer_docente ?? '',
        decidir_docente: nota?.valores_raw.decidir_docente ?? '',
        ser_autoeval: nota?.valores_raw.ser_autoeval ?? '',
        decidir_autoeval: nota?.valores_raw.decidir_autoeval ?? '',
      }
      return {
        materia_id: m.id_materia,
        nombre: `${m.area_materia} (${m.sigla_materia})`,
        nota_final: nota?.nota_final ?? '',
        dimensiones: dims,
      }
    })
  } catch (e) {
    console.error('Error cargando notas:', e)
    error.value = 'No se pudieron cargar notas existentes.'
  }
}

function confirmarRegistro() {
  confirmMessage.value = '¿Registrar/modificar notas?'
  actionToConfirm.value = registrarNota
  showModal.value = true
}

async function registrarNota() {
  showModal.value = false
  mensaje.value = ''
  error.value = ''
  try {
    for (const n of form.value.notas) {
      // Clamp nota_final a 100 max
      const rawNota = n.nota_final === '' ? null : Number(n.nota_final)
      const notaFinalClamped = rawNota !== null ? Math.min(rawNota, 100) : null

      const payload = {
        estudiante_id: form.value.estudiante_id,
        materia_id: n.materia_id,
        curso_id: form.value.curso_id,
        gestion_id: form.value.gestion_id,
        periodo: form.value.periodo,
        numero_periodo: Number(periodoSeleccionado.value),
        nota_final: notaFinalClamped,
        dimensiones: Object.entries(n.dimensiones).map(([k, v]) => ({
          nombre_dimension: k,
          valor_obtenido: v === '' ? null : Number(v),
        })),
      }
      await api.post('/notas', payload)
    }
    mensaje.value = 'Notas registradas correctamente.'
  } catch (e) {
    console.error('Error registrando:', e)
    if (e.response?.status === 422) {
      const errs = e.response.data.errors
      error.value = Object.values(errs).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Error al registrar notas.'
    }
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6 max-w-6xl mx-auto">
      <h2 class="text-2xl font-bold mb-4">Registrar Nota por Estudiante</h2>

      <template v-if="!estudianteSeleccionado">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar estudiante..."
          class="w-full md:w-1/2 px-4 py-2 border rounded mb-4"
        />
        <table class="w-full bg-white rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 text-left">Estudiante</th>
              <th class="p-2">RUDE</th>
              <th class="p-2">Curso</th>
              <th class="p-2">Nivel</th>
              <th class="p-2">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="est in paginatedEstudiantes" :key="est.id_estudiante">
              <td class="p-2">
                {{ est.persona_rol.persona.nombres_persona }}
                {{ est.persona_rol.persona.apellidos_pat }}
              </td>
              <td class="p-2">{{ est.rude }}</td>
              <td class="p-2">{{ est.curso_nombre }}</td>
              <td class="p-2">{{ est.nivel_nombre }}</td>
              <td class="p-2">
                <BaseButton
                  label="Editar Nota"
                  small
                  color="info"
                  @click="seleccionarEstudiante(est)"
                />
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-between items-center">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
          >
            Anterior
          </button>
          <span>Página {{ currentPage }} de {{ totalPages }}</span>
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
          >
            Siguiente
          </button>
        </div>
      </template>

      <template v-else>
        <div class="bg-white p-6 rounded-xl shadow">
          <h3 class="text-lg font-semibold mb-4">
            Notas de {{ estudianteSeleccionado.persona_rol.persona.nombres_persona }}
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <label class="block text-sm font-medium mb-1">Gestión</label>
              <select v-model="form.gestion_id" disabled class="w-full border px-3 py-2 rounded">
                <option v-for="g in gestiones" :key="g.id_gestion" :value="g.id_gestion">
                  {{ g.gestion }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Periodo</label>
              <select v-model="periodoSeleccionado" class="w-full border px-3 py-2 rounded">
                <option value="1">1er Trimestre</option>
                <option value="2">2do Trimestre</option>
                <option value="3">3er Trimestre</option>
              </select>
            </div>
          </div>
          <div class="overflow-x-auto mb-6">
            <table class="min-w-full border">
              <thead class="bg-blue-50 text-blue-800">
                <tr>
                  <th rowspan="3" class="border px-4 py-2 font-semibold text-blue-800 bg-blue-50">
                    Materia
                  </th>
                  <th colspan="4" class="border px-4 py-2 font-semibold bg-blue-100 text-blue-700">
                    Evaluación Maestro(a)
                  </th>
                  <th
                    colspan="2"
                    class="border px-4 py-2 font-semibold bg-purple-100 text-purple-700"
                  >
                    Autoevaluación
                  </th>
                  <th rowspan="3" class="border px-4 py-2 font-semibold text-blue-800 bg-blue-50">
                    Nota Final
                  </th>
                </tr>
                <tr>
                  <th colspan="4" class="border px-4 py-2 font-medium bg-blue-200 text-blue-600">
                    Dimensiones
                  </th>
                  <th
                    colspan="2"
                    class="border px-4 py-2 font-medium bg-purple-200 text-purple-600"
                  >
                    Dimensiones
                  </th>
                </tr>
                <tr>
                  <th class="border px-4 py-2 font-medium text-blue-700">
                    Ser <span class="text-xs text-blue-500 font-bold">(5 Pts.)</span>
                  </th>
                  <th class="border px-4 py-2 font-medium text-blue-700">
                    Saber <span class="text-xs text-blue-500 font-bold">(45 Pts.)</span>
                  </th>
                  <th class="border px-4 py-2 font-medium text-blue-700">
                    Hacer <span class="text-xs text-blue-500 font-bold">(40 Pts.)</span>
                  </th>
                  <th class="border px-4 py-2 font-medium text-blue-700">
                    Decidir <span class="text-xs text-blue-500 font-bold">(5 Pts.)</span>
                  </th>
                  <th class="border px-4 py-2 font-medium text-purple-700">
                    Ser <span class="text-xs text-purple-500 font-bold">(5 Pts.)</span>
                  </th>
                  <th class="border px-4 py-2 font-medium text-purple-700">
                    Decidir <span class="text-xs text-purple-500 font-bold">(5 Pts.)</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(nota, index) in form.notas"
                  :key="nota.materia_id"
                  :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                  class="hover:bg-gray-100 transition"
                >
                  <td class="border px-2 py-1">{{ nota.nombre }}</td>
                  <td class="border px-2 py-1">
                    <input
                      type="text"
                      v-model="nota.dimensiones.ser_docente"
                      class="w-16 border rounded px-1 py-0.5 text-center"
                    />
                  </td>
                  <td class="border px-2 py-1">
                    <input
                      type="text"
                      v-model="nota.dimensiones.saber_docente"
                      class="w-16 border rounded px-1 py-0.5 text-center"
                    />
                  </td>
                  <td class="border px-2 py-1">
                    <input
                      type="text"
                      v-model="nota.dimensiones.hacer_docente"
                      class="w-16 border rounded px-1 py-0.5 text-center"
                    />
                  </td>
                  <td class="border px-2 py-1">
                    <input
                      type="text"
                      v-model="nota.dimensiones.decidir_docente"
                      class="w-16 border rounded px-1 py-0.5 text-center"
                    />
                  </td>
                  <td class="border px-2 py-1">
                    <input
                      type="text"
                      v-model="nota.dimensiones.ser_autoeval"
                      class="w-16 border rounded px-1 py-0.5 text-center"
                    />
                  </td>
                  <td class="border px-2 py-1">
                    <input
                      type="text"
                      v-model="nota.dimensiones.decidir_autoeval"
                      class="w-16 border rounded px-1 py-0.5 text-center"
                    />
                  </td>
                  <td class="border px-2 py-1 font-semibold text-center">{{ nota.nota_final }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex justify-end gap-2">
            <BaseButton label="Registrar Notas" color="success" @click="confirmarRegistro" />
            <BaseButton label="Cancelar" color="warning" @click="estudianteSeleccionado = null" />
          </div>
          <p v-if="mensaje" class="mt-4 text-green-700 font-medium">{{ mensaje }}</p>
          <p v-if="error" class="mt-4 text-red-600 font-medium">{{ error }}</p>
        </div>
      </template>

      <CardBoxModal
        v-model="showModal"
        :title="confirmMessage"
        button="success"
        button-label="Confirmar"
        :has-cancel="true"
        @confirm="actionToConfirm"
      >
        <p>Esta acción modificará las notas del estudiante seleccionado. ¿Está seguro?</p>
      </CardBoxModal>
    </div>
  </LayoutAuthenticated>
</template>
