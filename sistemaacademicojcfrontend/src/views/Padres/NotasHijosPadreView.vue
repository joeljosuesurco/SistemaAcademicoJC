<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-xl font-bold mb-4">Notas de mis hijos</h2>

      <div v-if="estudianteSeleccionado" class="bg-white border rounded shadow p-6">
        <p><strong>Estudiante:</strong> {{ estudianteSeleccionado.nombre_completo }}</p>
        <p>
          <strong>Curso:</strong>
          {{ estudianteSeleccionado.curso?.grado_curso }}
          {{ estudianteSeleccionado.curso?.paralelo_curso }} -
          {{ estudianteSeleccionado.curso?.nivel }}
        </p>

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
                <th
                  colspan="2"
                  class="border px-4 py-2 font-semibold bg-purple-100 text-purple-700"
                >
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
                <th colspan="2" class="border px-4 py-2 font-medium bg-purple-200 text-purple-700">
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
                v-for="(m, i) in materiasDimensiones"
                :key="i"
                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
              >
                <td class="border px-4 py-2 text-left font-medium">{{ m.nombre }}</td>
                <td class="border px-4 py-2">{{ m.ser_raw }}</td>
                <td class="border px-4 py-2">{{ m.saber_raw }}</td>
                <td class="border px-4 py-2">{{ m.hacer_raw }}</td>
                <td class="border px-4 py-2">{{ m.decidir_raw }}</td>
                <td class="border px-4 py-2">{{ m.ser_auto_raw }}</td>
                <td class="border px-4 py-2">{{ m.decidir_auto_raw }}</td>
                <td class="border px-4 py-2 font-bold text-blue-800">{{ m.promedio }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Boleta por trimestres -->
        <div v-if="mostrarBoleta" class="overflow-x-auto mt-6">
          <h3 class="text-xl font-semibold mb-4 text-blue-800">Boleta de Calificaciones</h3>
          <table
            class="min-w-full text-center border border-gray-200 bg-white rounded-xl shadow-sm"
          >
            <thead>
              <tr>
                <th class="border px-4 py-2 font-semibold text-blue-800 bg-blue-50">MATERIAS</th>
                <th class="border px-4 py-2">1er Trim</th>
                <th class="border px-4 py-2">2do Trim</th>
                <th class="border px-4 py-2">3er Trim</th>
                <th class="border px-4 py-2">Promedio Final</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(m, i) in materiasBoleta"
                :key="i"
                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
              >
                <td class="border px-4 py-2 text-left font-medium">{{ m.nombre }}</td>
                <td class="border px-4 py-2">{{ m.t1 }}</td>
                <td class="border px-4 py-2">{{ m.t2 }}</td>
                <td class="border px-4 py-2">{{ m.t3 }}</td>
                <td class="border px-4 py-2 font-bold text-blue-800">{{ m.promedio }}</td>
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
        </div>
      </div>

      <!-- Listado de hijos -->
      <div v-else>
        <div class="mb-4">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre o curso..."
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
            <tr v-for="h in hijosFiltrados" :key="h.id_estudiante">
              <td class="p-2">{{ h.nombre_completo }}</td>
              <td class="p-2">{{ h.curso?.grado_curso }} {{ h.curso?.paralelo_curso }}</td>
              <td class="p-2">{{ h.curso?.nivel }}</td>
              <td class="p-2">
                <BaseButton label="Ver Notas" small color="info" @click="verNota(h)" />
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
<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const hijos = ref([])
const estudianteSeleccionado = ref(null)
const materiasDimensiones = ref([])
const materiasBoleta = ref([])
const mostrarBoleta = ref(false)
const periodoSeleccionado = ref('trimestre 1')
const search = ref('')
const currentPage = ref(1)
const pageSize = 10
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const res = await api.get('/padre-auth/hijos')
    hijos.value = res.data.data || []
  } catch (err) {
    error.value = 'No se pudo cargar la lista de hijos'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const hijosFiltrados = computed(() => {
  const filtrados = search.value
    ? hijos.value.filter(
        (h) =>
          h.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
          `${h.curso?.grado_curso} ${h.curso?.paralelo_curso}`
            .toLowerCase()
            .includes(search.value.toLowerCase()) ||
          h.curso?.nivel.toLowerCase().includes(search.value.toLowerCase()),
      )
    : hijos.value
  const start = (currentPage.value - 1) * pageSize
  return filtrados.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  const total = search.value
    ? hijos.value.filter(
        (h) =>
          h.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
          `${h.curso?.grado_curso} ${h.curso?.paralelo_curso}`
            .toLowerCase()
            .includes(search.value.toLowerCase()) ||
          h.curso?.nivel.toLowerCase().includes(search.value.toLowerCase()),
      ).length
    : hijos.value.length
  return Math.ceil(total / pageSize)
})

function verNota(hijo) {
  estudianteSeleccionado.value = hijo
  mostrarBoleta.value = false
  cargarNotas()
}

function volverAListado() {
  estudianteSeleccionado.value = null
  mostrarBoleta.value = false
}

function verBoleta() {
  mostrarBoleta.value = true
}

function calcularPromedio(e) {
  const ser = Math.round((e.ser_docente || 0) * 0.05)
  const saber = Math.round((e.saber_docente || 0) * 0.45)
  const hacer = Math.round((e.hacer_docente || 0) * 0.4)
  const decidir = Math.round((e.decidir_docente || 0) * 0.05)
  const ser_auto = Math.round((e.ser_autoeval || 0) * 0.05)
  const decidir_auto = Math.round((e.decidir_autoeval || 0) * 0.05)
  return Math.min(ser + saber + hacer + decidir + ser_auto + decidir_auto, 100)
}

async function cargarNotas() {
  if (!estudianteSeleccionado.value) return
  const [periodo, numero] = periodoSeleccionado.value.split(' ')
  try {
    const res = await api.get(`/padre-auth/notas/${estudianteSeleccionado.value.id_estudiante}`, {
      params: {
        periodo,
        numero_periodo: numero,
      },
    })

    const notas = res.data.data.notas

    materiasDimensiones.value = notas.map((n) => {
      const dims = Object.fromEntries(
        n.dimensiones.map((d) => [d.nombre_dimension, d.valor_obtenido]),
      )
      const promedio = calcularPromedio(dims)

      return {
        nombre: `${n.materia.area_materia} (${n.materia.sigla_materia})`,
        ser_raw: dims.ser_docente || 0,
        saber_raw: dims.saber_docente || 0,
        hacer_raw: dims.hacer_docente || 0,
        decidir_raw: dims.decidir_docente || 0,
        ser_auto_raw: dims.ser_autoeval || 0,
        decidir_auto_raw: dims.decidir_autoeval || 0,
        promedio,
      }
    })

    // Boleta (trimestres)
    const boletaMap = new Map()
    notas.forEach((n) => {
      const dims = Object.fromEntries(
        n.dimensiones.map((d) => [d.nombre_dimension, d.valor_obtenido]),
      )
      const final = calcularPromedio(dims)

      const key = n.materia.id_materia
      if (!boletaMap.has(key)) {
        boletaMap.set(key, {
          nombre: `${n.materia.area_materia} (${n.materia.sigla_materia})`,
          t1: 0,
          t2: 0,
          t3: 0,
        })
      }
      const item = boletaMap.get(key)
      item[`t${n.numero_periodo}`] = final
    })
    materiasBoleta.value = Array.from(boletaMap.values()).map((m) => {
      const arr = [m.t1, m.t2, m.t3]
      const suma = arr.reduce((a, b) => a + b, 0)
      return {
        ...m,
        promedio: Math.min(Math.round(suma / 3), 100),
      }
    })
  } catch (err) {
    console.error('Error al cargar notas:', err)
  }
}

watch(periodoSeleccionado, cargarNotas)
</script>
