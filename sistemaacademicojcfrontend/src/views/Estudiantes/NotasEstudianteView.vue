<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-xl font-bold mb-6">Mis Notas</h2>

      <!-- Selector de trimestre -->
      <div class="mb-6" v-if="!mostrarBoleta">
        <label class="block text-sm font-medium mb-1">Periodo</label>
        <select v-model="periodoSeleccionado" class="px-3 py-2 border rounded shadow-sm w-52">
          <option value="trimestre 1">Trimestre 1</option>
          <option value="trimestre 2">Trimestre 2</option>
          <option value="trimestre 3">Trimestre 3</option>
        </select>
      </div>

      <!-- CARD: Dimensiones -->
      <div
        v-if="materiasDimensiones.length && !mostrarBoleta"
        class="bg-white border border-blue-200 rounded-xl shadow-md p-6 space-y-4"
      >
        <div class="space-y-1 text-sm">
          <p><strong>Estudiante:</strong> {{ infoEstudiante.nombre }}</p>
          <p><strong>Curso:</strong> {{ infoEstudiante.curso }}</p>
          <p><strong>Nivel:</strong> {{ infoEstudiante.nivel }}</p>
        </div>

        <div class="overflow-x-auto">
          <table
            class="min-w-full text-center border border-gray-200 bg-white rounded-xl shadow-sm text-sm"
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
                <th class="border px-4 py-2 font-medium text-blue-700">Ser</th>
                <th class="border px-4 py-2 font-medium text-blue-700">Saber</th>
                <th class="border px-4 py-2 font-medium text-blue-700">Hacer</th>
                <th class="border px-4 py-2 font-medium text-blue-700">Decidir</th>
                <th class="border px-4 py-2 font-medium text-purple-700">Ser</th>
                <th class="border px-4 py-2 font-medium text-purple-700">Decidir</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(m, i) in materiasDimensiones"
                :key="i"
                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
              >
                <td class="border px-4 py-2 text-left font-medium">{{ m.nombre }}</td>
                <td class="border px-4 py-2">{{ m.ser }}</td>
                <td class="border px-4 py-2">{{ m.saber }}</td>
                <td class="border px-4 py-2">{{ m.hacer }}</td>
                <td class="border px-4 py-2">{{ m.decidir }}</td>
                <td class="border px-4 py-2">{{ m.ser_auto }}</td>
                <td class="border px-4 py-2">{{ m.decidir_auto }}</td>
                <td class="border px-4 py-2 font-bold text-blue-800">{{ m.promedio }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- BOLETA -->
      <div v-if="materiasBoleta.length && mostrarBoleta" class="overflow-x-auto mt-6">
        <div class="space-y-1 text-sm mb-4">
          <p><strong>Estudiante:</strong> {{ infoEstudiante.nombre }}</p>
          <p><strong>Curso:</strong> {{ infoEstudiante.curso }}</p>
          <p><strong>Nivel:</strong> {{ infoEstudiante.nivel }}</p>
        </div>

        <h3 class="text-xl font-semibold mb-4 text-blue-700">Boleta de Calificaciones</h3>
        <table
          class="min-w-full text-center border border-gray-200 bg-white rounded-xl shadow-sm text-sm"
        >
          <thead class="bg-blue-50 text-blue-800">
            <tr>
              <th class="border px-4 py-2 font-semibold">MATERIAS</th>
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
              <td class="border px-4 py-2 font-bold text-blue-700">{{ m.promedio }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botones -->
      <div class="mt-6 flex gap-2">
        <BaseButton
          v-if="mostrarBoleta"
          label="Volver al listado"
          small
          color="green"
          @click="mostrarBoleta = false"
        />
        <BaseButton v-else label="Ver boleta" small color="blue" @click="mostrarBoleta = true" />
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const materiasDimensiones = ref([])
const materiasBoleta = ref([])
const periodoSeleccionado = ref('trimestre 1')
const mostrarBoleta = ref(false)
const infoEstudiante = ref({ nombre: '', curso: '', nivel: '' })

function calcularPromedioSimple(dims) {
  const ser = Math.round((dims.ser_docente || 0) * 0.05)
  const saber = Math.round((dims.saber_docente || 0) * 0.45)
  const hacer = Math.round((dims.hacer_docente || 0) * 0.4)
  const decidir = Math.round((dims.decidir_docente || 0) * 0.05)
  const ser_auto = Math.round((dims.ser_autoeval || 0) * 0.05)
  const decidir_auto = Math.round((dims.decidir_autoeval || 0) * 0.05)
  return Math.min(ser + saber + hacer + decidir + ser_auto + decidir_auto, 100)
}

async function cargarNotas() {
  const [periodo, numero] = periodoSeleccionado.value.split(' ')
  try {
    const res = await api.get('/estudiante-auth/notas', {
      params: { periodo, numero_periodo: numero },
    })

    const notas = res.data.data.notas
    const curso = res.data.data.curso
    const nivel = res.data.data.nivel

    if (!infoEstudiante.value.nombre) {
      const perfil = await api.get('/perfil')
      const persona = perfil.data.data.persona_rol?.persona
      const nombre =
        `${persona?.apellidos_pat || ''} ${persona?.apellidos_mat || ''} ${persona?.nombres_persona || ''}`.trim()
      infoEstudiante.value = {
        nombre,
        curso,
        nivel,
      }
    }

    materiasDimensiones.value = notas.map((n) => {
      const dims = Object.fromEntries(
        n.dimensiones.map((d) => [d.nombre_dimension, d.valor_obtenido]),
      )
      return {
        nombre: `${n.materia.area_materia} (${n.materia.sigla_materia})`,
        ser: dims.ser_docente || 0,
        saber: dims.saber_docente || 0,
        hacer: dims.hacer_docente || 0,
        decidir: dims.decidir_docente || 0,
        ser_auto: dims.ser_autoeval || 0,
        decidir_auto: dims.decidir_autoeval || 0,
        promedio: calcularPromedioSimple(dims),
      }
    })

    const boletaMap = new Map()
    notas.forEach((n) => {
      const dims = Object.fromEntries(
        n.dimensiones.map((d) => [d.nombre_dimension, d.valor_obtenido]),
      )
      const final = calcularPromedioSimple(dims)
      const key = n.materia.id_materia
      if (!boletaMap.has(key)) {
        boletaMap.set(key, {
          nombre: `${n.materia.area_materia} (${n.materia.sigla_materia})`,
          t1: 0,
          t2: 0,
          t3: 0,
        })
      }
      boletaMap.get(key)[`t${n.numero_periodo}`] = final
    })

    materiasBoleta.value = Array.from(boletaMap.values()).map((m) => {
      const suma = m.t1 + m.t2 + m.t3
      const count = [m.t1, m.t2, m.t3].filter((v) => v > 0).length || 1
      return {
        ...m,
        promedio: Math.min(Math.round(suma / count), 100),
      }
    })
  } catch (err) {
    console.error('Error al cargar notas:', err)
  }
}

onMounted(() => {
  cargarNotas()
})
watch(periodoSeleccionado, cargarNotas)
</script>
