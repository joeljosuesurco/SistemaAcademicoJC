<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const estudiantes = ref([])
const materiasTodas = ref([])
const materiasBoleta = ref([])
const estudianteSeleccionado = ref(null)
const mostrarBoleta = ref(false)
const periodoSeleccionado = ref('trimestre 1')
const search = ref('')
const currentPage = ref(1)
const pageSize = 10
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const response = await api.get('/notas')
    const notasRaw = response.data.data
    const map = new Map()
    notasRaw.forEach((n) => {
      const id = n.estudiante.id
      if (!map.has(id)) {
        map.set(id, {
          ...n.estudiante,
          curso: `${n.curso.grado} ${n.curso.paralelo}`,
          nivel: n.curso.nivel,
          nivelId: n.curso.nivelId,
          notas: [],
        })
      }
      map.get(id).notas.push(n)
    })
    estudiantes.value = Array.from(map.values())
  } catch {
    error.value = 'Error cargando notas'
  } finally {
    loading.value = false
  }
})

const estudiantesFiltrados = computed(() => {
  const filtered = search.value
    ? estudiantes.value.filter(
        (e) =>
          e.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
          e.curso.toLowerCase().includes(search.value.toLowerCase()) ||
          e.nivel.toLowerCase().includes(search.value.toLowerCase()),
      )
    : estudiantes.value
  const start = (currentPage.value - 1) * pageSize
  return filtered.slice(start, start + pageSize)
})

const totalPages = computed(() =>
  Math.ceil(
    (search.value
      ? estudiantes.value.filter(
          (e) =>
            e.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
            e.curso.toLowerCase().includes(search.value.toLowerCase()) ||
            e.nivel.toLowerCase().includes(search.value.toLowerCase()),
        ).length
      : estudiantes.value.length) / pageSize,
  ),
)

function verNota(est) {
  estudianteSeleccionado.value = est
  mostrarBoleta.value = false
  actualizarMaterias()
}
function volverAListado() {
  estudianteSeleccionado.value = null
  mostrarBoleta.value = false
}

async function actualizarMaterias() {
  if (!estudianteSeleccionado.value) return
  const [periodo, numero] = periodoSeleccionado.value.split(' ')
  const notas = estudianteSeleccionado.value.notas

  materiasTodas.value = notas
    .filter((n) => n.periodo === periodo && String(n.numero_periodo) === numero)
    .map((n) => ({
      nombre: `${n.materia.area} (${n.materia.sigla})`,
      ser_raw: parseInt(n.valores_raw.ser_docente),
      ser_pct: n.pesos_pct.ser_docente,
      saber_raw: parseInt(n.valores_raw.saber_docente),
      saber_pct: n.pesos_pct.saber_docente,
      hacer_raw: parseInt(n.valores_raw.hacer_docente),
      hacer_pct: n.pesos_pct.hacer_docente,
      decidir_raw: parseInt(n.valores_raw.decidir_docente),
      decidir_pct: n.pesos_pct.decidir_docente,
      ser_auto_raw: parseInt(n.valores_raw.ser_autoeval),
      ser_auto_pct: n.pesos_pct.ser_autoeval,
      decidir_auto_raw: parseInt(n.valores_raw.decidir_autoeval),
      decidir_auto_pct: n.pesos_pct.decidir_autoeval,
      // Aquí ya no hay cálculo: simplemente toma n.nota_final del backend
      promedio: n.nota_final,
    }))

  const nivelId = estudianteSeleccionado.value.nivelId
  if (!nivelId) return
  try {
    const res = await api.get(`/materias/nivel/${nivelId}`)
    const boleta = new Map()
    res.data.data.forEach((m) =>
      boleta.set(m.id_materia, {
        nombre: `${m.area_materia} (${m.sigla_materia})`,
        t1: 0,
        t2: 0,
        t3: 0,
      }),
    )
    notas.forEach((n) => {
      const e = boleta.get(n.materia.id)
      if (e) e[`t${n.numero_periodo}`] = n.nota_final
    })
    materiasBoleta.value = Array.from(boleta.values()).map((m) => {
      const arr = [m.t1, m.t2, m.t3]
      const suma = arr.reduce((a, b) => a + b, 0)
      return {
        ...m,
        promedio: Math.min(Math.round(suma / 3), 100),
      }
    })
  } catch (er) {
    console.error(er)
  }
}

const descargarBoletaPDF = async () => {
  if (!estudianteSeleccionado.value) return

  const [, numero] = periodoSeleccionado.value.split(' ')
  const url = `${import.meta.env.VITE_API_BASE_URL}/reportes/boleta`
  const token = localStorage.getItem('token')
  console.log('📦 estudianteSeleccionado:', estudianteSeleccionado.value)
  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/pdf',
      },
      body: JSON.stringify({
        estudiante: estudianteSeleccionado.value,
        periodo: numero,
        materias: materiasBoleta.value,
      }),
    })

    const contentType = res.headers.get('content-type')
    if (!res.ok || !contentType.includes('application/pdf')) {
      const text = await res.text()
      console.error('Respuesta inesperada:', text)
      alert('No se pudo generar el PDF. Revisa consola.')
      return
    }

    const blob = await res.blob()
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Boleta_${estudianteSeleccionado.value.nombre_completo}.pdf`
    link.click()
  } catch (error) {
    console.error('Error al descargar boleta:', error)
    alert('Ocurrió un error al generar la boleta PDF.')
  }
}

function verBoleta() {
  mostrarBoleta.value = true
}
watch(periodoSeleccionado, actualizarMaterias)
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-xl font-bold mb-4">Listado de Notas</h2>

      <div v-if="estudianteSeleccionado" class="bg-white border rounded shadow p-6">
        <p><strong>Estudiante:</strong> {{ estudianteSeleccionado.nombre_completo }}</p>
        <p><strong>Curso:</strong> {{ estudianteSeleccionado.curso }}</p>
        <p><strong>Nivel:</strong> {{ estudianteSeleccionado.nivel }}</p>

        <div v-if="!mostrarBoleta" class="mt-4 mb-4">
          <label class="block text-sm font-medium mb-1">Periodo</label>
          <select v-model="periodoSeleccionado" class="px-3 py-2 border rounded shadow-sm w-52">
            <option value="trimestre 1">Trimestre 1</option>
            <option value="trimestre 2">Trimestre 2</option>
            <option value="trimestre 3">Trimestre 3</option>
          </select>
        </div>

        <!-- Tabla de dimensiones -->
        <div v-if="!mostrarBoleta" class="overflow-x-auto">
          <table
            class="min-w-full text-center border border-gray-200 bg-white rounded-xl shadow-sm"
          >
            <thead>
              <tr>
                <th rowspan="3" class="border px-4 py-2 font-semibold text-blue-800 bg-blue-50">
                  MATERIAS
                </th>
                <th colspan="4" class="border px-4 py-2 font-semibold bg-blue-100 text-blue-700">
                  Evaluación Maestro(a)
                </th>
                <th colspan="2" class="border px-4 py-2 font-semibold bg-purple-50 text-purple-700">
                  Autoevaluación
                </th>
                <th rowspan="3" class="border px-4 py-2 font-semibold text-blue-800 bg-blue-50">
                  Promedio
                </th>
              </tr>
              <tr>
                <th colspan="4" class="border px-4 py-2 font-medium bg-blue-200 text-blue-600">
                  Dimensiones
                </th>
                <th colspan="2" class="border px-4 py-2 font-medium bg-purple-100 text-purple-700">
                  Dimensiones
                </th>
              </tr>
              <tr>
                <th class="border px-4 py-2 font-medium text-blue-700">
                  Ser <span class="text-xs text-blue-600 font-bold">(5 Pts.)</span>
                </th>
                <th class="border px-4 py-2 font-medium text-blue-700">
                  Saber <span class="text-xs text-blue-600 font-bold">(45 Pts.)</span>
                </th>
                <th class="border px-4 py-2 font-medium text-blue-700">
                  Hacer <span class="text-xs text-blue-600 font-bold">(40 Pts.)</span>
                </th>
                <th class="border px-4 py-2 font-medium text-blue-700">
                  Decidir <span class="text-xs text-blue-600 font-bold">(5 Pts.)</span>
                </th>
                <th class="border px-4 py-2 font-medium text-purple-700">
                  Ser <span class="text-xs text-purple-600 font-bold">(5 Pts.)</span>
                </th>
                <th class="border px-4 py-2 font-medium text-purple-700">
                  Decidir <span class="text-xs text-purple-600 font-bold">(5 Pts.)</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(m, i) in materiasTodas"
                :key="i"
                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                class="hover:bg-gray-100 transition"
              >
                <td class="border px-6 py-3 text-left font-medium">{{ m.nombre }}</td>
                <td class="border px-6 py-3">
                  {{ m.ser_raw }} <span class="text-xs text-gray-500">({{ m.ser_pct }})</span>
                </td>
                <td class="border px-6 py-3">
                  {{ m.saber_raw }} <span class="text-xs text-gray-500">({{ m.saber_pct }})</span>
                </td>
                <td class="border px-6 py-3">
                  {{ m.hacer_raw }} <span class="text-xs text-gray-500">({{ m.hacer_pct }})</span>
                </td>
                <td class="border px-6 py-3">
                  {{ m.decidir_raw }}
                  <span class="text-xs text-gray-500">({{ m.decidir_pct }})</span>
                </td>
                <td class="border px-6 py-3">
                  {{ m.ser_auto_raw }}
                  <span class="text-xs text-gray-500">({{ m.ser_auto_pct }})</span>
                </td>
                <td class="border px-6 py-3">
                  {{ m.decidir_auto_raw }}
                  <span class="text-xs text-gray-500">({{ m.decidir_auto_pct }})</span>
                </td>
                <td class="border px-6 py-3 font-semibold">{{ m.promedio }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Tabla de Boleta completa -->
        <div v-if="mostrarBoleta" class="overflow-x-auto mt-6">
          <h3 class="text-xl font-semibold mb-4 text-blue-800">Boleta de Calificaciones</h3>
          <table
            class="min-w-full text-center border border-gray-200 bg-white rounded-xl shadow-sm"
          >
            <thead>
              <tr>
                <th class="border px-4 py-2 font-semibold text-blue-800 bg-blue-50">MATERIAS</th>
                <th class="border px-4 py-2 font-semibold bg-blue-100 text-blue-700">1er Trim</th>
                <th class="border px-4 py-2 font-semibold bg-blue-100 text-blue-700">2do Trim</th>
                <th class="border px-4 py-2 font-semibold bg-blue-100 text-blue-700">3er Trim</th>
                <th class="border px-4 py-2 font-semibold text-blue-800 bg-blue-50">
                  Promedio Final
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(m, i) in materiasBoleta"
                :key="i"
                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                class="hover:bg-gray-100 transition"
              >
                <td class="border px-6 py-3 text-left font-medium">{{ m.nombre }}</td>
                <td class="border px-6 py-3">{{ m.t1 }}</td>
                <td class="border px-6 py-3">{{ m.t2 }}</td>
                <td class="border px-6 py-3">{{ m.t3 }}</td>
                <td class="border px-6 py-3 font-bold text-blue-800">{{ m.promedio }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-6 flex gap-2">
          <BaseButton label="Volver al listado" small color="green" @click="volverAListado" />
          <BaseButton
            v-if="!mostrarBoleta"
            label="Ver boleta"
            small
            color="blue"
            @click="verBoleta"
          />
          <BaseButton
            v-if="mostrarBoleta"
            label="Descargar Boleta PDF"
            small
            color="success"
            @click="descargarBoletaPDF"
          />
        </div>
      </div>

      <div v-else>
        <!-- Listado de estudiantes con paginación -->
        <div class="mb-4">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por estudiante, curso o nivel..."
            class="w-full md:w-1/2 px-4 py-2 border rounded shadow-sm"
          />
        </div>
        <div v-if="loading" class="text-gray-500">Cargando estudiantes...</div>
        <div v-else-if="error" class="text-red-600">{{ error }}</div>
        <table v-else class="w-full text-left bg-white rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2">Estudiante</th>
              <th class="p-2">Curso</th>
              <th class="p-2">Nivel</th>
              <th class="p-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="est in estudiantesFiltrados" :key="est.id_estudiante">
              <td class="p-2">{{ est.nombre_completo }}</td>
              <td class="p-2">{{ est.curso }}</td>
              <td class="p-2">{{ est.nivel }}</td>
              <td class="p-2">
                <BaseButton label="Ver" small color="info" @click="verNota(est)" />
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-center items-center gap-2" v-if="totalPages > 1">
          <BaseButton
            label="« Anterior"
            small
            :disabled="currentPage === 1"
            @click="currentPage--"
          />
          <span>Página {{ currentPage }} de {{ totalPages }}</span>
          <BaseButton
            label="Siguiente »"
            small
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          />
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
