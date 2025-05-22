<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-6">Registrar Notas por Curso</h2>

      <!-- Modal de confirmación -->
      <CardBoxModal
        v-model="showModal"
        :title="confirmMessage"
        button="success"
        button-label="Confirmar"
        :has-cancel="true"
        @confirm="actionToConfirm"
      >
        <p>Esta acción modificará las notas de todos los estudiantes. ¿Está seguro?</p>
      </CardBoxModal>

      <!-- Modal de éxito -->
      <CardBoxModal
        v-model="showSuccessModal"
        title="Éxito"
        button="success"
        button-label="Aceptar"
        :has-cancel="false"
        @confirm="showSuccessModal = false"
      >
        <p class="text-green-700">{{ mensaje }}</p>
      </CardBoxModal>

      <div v-if="!cursoSeleccionado">
        <h3 class="text-lg font-semibold mb-2">Seleccione un curso</h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="curso in cursos"
            :key="curso.id_curso"
            class="p-4 border rounded shadow bg-indigo-50 hover:bg-indigo-100 cursor-pointer"
            @click="seleccionarCurso(curso)"
          >
            <p class="font-semibold">{{ curso.nombre }}</p>
            <p class="text-sm text-gray-600">Nivel: {{ curso.nivel }}</p>
            <p class="text-sm text-gray-600">Gestión: {{ curso.gestion }}</p>
          </div>
        </div>
      </div>

      <div v-else-if="!materiaSeleccionada">
        <div class="mb-4">
          <BaseButton
            label="Volver a cursos"
            color="gray"
            small
            @click="cursoSeleccionado = null"
          />
        </div>
        <h3 class="text-xl font-semibold mb-4">Seleccione una materia</h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="materia in cursoSeleccionado.materias"
            :key="materia.id_materia"
            class="p-4 border rounded shadow bg-blue-50 hover:bg-blue-100 cursor-pointer"
            @click="seleccionarMateria(materia)"
          >
            <p class="font-semibold">{{ materia.area_materia }} ({{ materia.sigla_materia }})</p>
            <p class="text-sm text-gray-600">Campo: {{ materia.campo_materia }}</p>
          </div>
        </div>
      </div>

      <div v-else>
        <CardBox class="mt-4">
          <div class="mb-4 flex justify-between items-center">
            <div class="flex gap-2 items-center">
              <BaseButton label="Volver" color="gray" small @click="reset" />
              <select v-model="numeroPeriodo" class="pl-3 pr-8 py-2 rounded border text-sm">
                <option value="1">Trimestre 1</option>
                <option value="2">Trimestre 2</option>
                <option value="3">Trimestre 3</option>
              </select>
            </div>
            <BaseButton label="Registrar Notas" color="primary" @click="confirmarRegistro" />
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-center bg-white rounded shadow border">
              <thead>
                <tr>
                  <th rowspan="2" class="bg-blue-50 text-blue-800 font-bold px-4 py-2">
                    Estudiante
                  </th>
                  <th colspan="4" class="bg-blue-100 text-blue-800 font-semibold">
                    Evaluación Docente
                  </th>
                  <th colspan="2" class="bg-purple-100 text-purple-800 font-semibold">
                    Autoevaluación
                  </th>
                  <th rowspan="2" class="bg-blue-50 text-blue-800 font-bold px-2">Promedio</th>
                </tr>
                <tr>
                  <th class="bg-blue-50 text-blue-700">
                    Ser<br /><span class="text-xs">(5 Pts.)</span>
                  </th>
                  <th class="bg-blue-50 text-blue-700">
                    Saber<br /><span class="text-xs">(45 Pts.)</span>
                  </th>
                  <th class="bg-blue-50 text-blue-700">
                    Hacer<br /><span class="text-xs">(40 Pts.)</span>
                  </th>
                  <th class="bg-blue-50 text-blue-700">
                    Decidir<br /><span class="text-xs">(5 Pts.)</span>
                  </th>
                  <th class="bg-purple-50 text-purple-700">
                    Ser<br /><span class="text-xs">(5 Pts.)</span>
                  </th>
                  <th class="bg-purple-50 text-purple-700">
                    Decidir<br /><span class="text-xs">(5 Pts.)</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in estudiantes" :key="e.id" class="border-t">
                  <td class="px-2 py-1 font-medium">{{ e.nombre }}</td>
                  <td>
                    <input
                      type="text"
                      class="w-16 text-center border rounded"
                      :class="{ 'border-red-500': erroresCampos[e.id]?.ser }"
                      v-model="e.ser"
                      @blur="validarCampo(e.id, 'ser', e.ser)"
                    />
                  </td>
                  <td>
                    <input
                      type="text"
                      class="w-16 text-center border rounded"
                      :class="{ 'border-red-500': erroresCampos[e.id]?.saber }"
                      v-model="e.saber"
                      @blur="validarCampo(e.id, 'saber', e.saber)"
                    />
                  </td>
                  <td>
                    <input
                      type="text"
                      class="w-16 text-center border rounded"
                      :class="{ 'border-red-500': erroresCampos[e.id]?.hacer }"
                      v-model="e.hacer"
                      @blur="validarCampo(e.id, 'hacer', e.hacer)"
                    />
                  </td>
                  <td>
                    <input
                      type="text"
                      class="w-16 text-center border rounded"
                      :class="{ 'border-red-500': erroresCampos[e.id]?.decidir }"
                      v-model="e.decidir"
                      @blur="validarCampo(e.id, 'decidir', e.decidir)"
                    />
                  </td>
                  <td>
                    <input
                      type="text"
                      class="w-16 text-center border rounded"
                      :class="{ 'border-red-500': erroresCampos[e.id]?.ser_auto }"
                      v-model="e.ser_auto"
                      @blur="validarCampo(e.id, 'ser_auto', e.ser_auto)"
                    />
                  </td>
                  <td>
                    <input
                      type="text"
                      class="w-16 text-center border rounded"
                      :class="{ 'border-red-500': erroresCampos[e.id]?.decidir_auto }"
                      v-model="e.decidir_auto"
                      @blur="validarCampo(e.id, 'decidir_auto', e.decidir_auto)"
                    />
                  </td>
                  <td class="font-bold text-blue-800">{{ calcularPromedio(e) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardBox>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'
import CardBox from '@/components/CardBox.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'

const cursos = ref([])
const estudiantes = ref([])
const cursoSeleccionado = ref(null)
const materiaSeleccionada = ref(null)
const numeroPeriodo = ref(1)
const periodo = 'trimestre'

const mensaje = ref('')
const error = ref('')
const showModal = ref(false)
const showSuccessModal = ref(false)
const confirmMessage = ref('¿Registrar o modificar notas de todos los estudiantes?')
const actionToConfirm = ref(null)
const erroresCampos = ref({})

const seleccionarCurso = (curso) => {
  cursoSeleccionado.value = curso
  materiaSeleccionada.value = null
  estudiantes.value = []
  erroresCampos.value = {}
}

const seleccionarMateria = async (materia) => {
  materiaSeleccionada.value = materia
  const res = await api.get('/adsolo/notas/estudiantes', {
    params: {
      curso_id: cursoSeleccionado.value.id_curso,
      materia_id: materia.id_materia,
      periodo,
      numero_periodo: numeroPeriodo.value,
    },
  })
  estudiantes.value = res.data.data.map((e) => ({
    id: e.id_estudiante,
    nombre: e.nombre_completo,
    ser: e.ser || '',
    saber: e.saber || '',
    hacer: e.hacer || '',
    decidir: e.decidir || '',
    ser_auto: e.ser_auto || '',
    decidir_auto: e.decidir_auto || '',
  }))
  erroresCampos.value = {}
}

const validarCampo = (id, campo, valor) => {
  const limpio = valor.replace(/[^0-9]/g, '')
  const numero = parseInt(limpio)
  const esValido = !isNaN(numero) && numero >= 0 && numero <= 100
  if (!erroresCampos.value[id]) erroresCampos.value[id] = {}
  erroresCampos.value[id][campo] = !esValido
  return esValido ? numero : ''
}

const calcularPromedio = (e) => {
  const ser = Math.round((e.ser || 0) * 0.05)
  const saber = Math.round((e.saber || 0) * 0.45)
  const hacer = Math.round((e.hacer || 0) * 0.4)
  const decidir = Math.round((e.decidir || 0) * 0.05)
  const ser_auto = Math.round((e.ser_auto || 0) * 0.05)
  const decidir_auto = Math.round((e.decidir_auto || 0) * 0.05)
  return Math.min(ser + saber + hacer + decidir + ser_auto + decidir_auto, 100)
}

const confirmarRegistro = () => {
  actionToConfirm.value = registrarNotas
  showModal.value = true
}

const registrarNotas = async () => {
  showModal.value = false
  mensaje.value = ''
  error.value = ''
  try {
    for (const e of estudiantes.value) {
      const payload = {
        estudiante_id: e.id,
        materia_id: materiaSeleccionada.value.id_materia,
        curso_id: cursoSeleccionado.value.id_curso,
        gestion_id: cursoSeleccionado.value.id_gestion,
        periodo,
        numero_periodo: numeroPeriodo.value,
        dimensiones: [
          { nombre_dimension: 'ser_docente', valor_obtenido: e.ser, porcentaje: 5 },
          { nombre_dimension: 'saber_docente', valor_obtenido: e.saber, porcentaje: 45 },
          { nombre_dimension: 'hacer_docente', valor_obtenido: e.hacer, porcentaje: 40 },
          { nombre_dimension: 'decidir_docente', valor_obtenido: e.decidir, porcentaje: 5 },
          { nombre_dimension: 'ser_autoeval', valor_obtenido: e.ser_auto, porcentaje: 5 },
          { nombre_dimension: 'decidir_autoeval', valor_obtenido: e.decidir_auto, porcentaje: 5 },
        ],
      }
      console.log(payload)
      await api.post('/adsolo/notas', payload)
    }
    mensaje.value = 'Notas registradas exitosamente'
    showSuccessModal.value = true
  } catch (e) {
    console.error(e)
    error.value = 'Error al registrar notas. Intente nuevamente.'
  }
}

const reset = () => {
  materiaSeleccionada.value = null
  estudiantes.value = []
  erroresCampos.value = {}
}

onMounted(async () => {
  const res = await api.get('/adsolo/cursos-con-materias')
  cursos.value = res.data.data.map((c) => ({
    ...c,
    nombre: `${c.grado_curso} ${c.paralelo_curso}`,
    nivel: c.nivel_educativo?.nombre || '',
  }))
})

watch(numeroPeriodo, () => {
  if (cursoSeleccionado.value && materiaSeleccionada.value) {
    seleccionarMateria(materiaSeleccionada.value)
  }
})
</script>
