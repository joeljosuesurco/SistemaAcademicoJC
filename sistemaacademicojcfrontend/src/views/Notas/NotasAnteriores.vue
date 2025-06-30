<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const gestiones = ref([])
const cursos = ref([])
const estudiantes = ref([])
const notas = ref([])

const gestionSeleccionada = ref('')
const cursoSeleccionado = ref('')
const estudianteSeleccionado = ref('')
const periodoSeleccionado = ref('Trimestre 1')

const cargarGestiones = async () => {
  try {
    const res = await api.get('/gestiones')
    gestiones.value = res.data.data
  } catch (err) {
    console.error('Error al cargar gestiones:', err)
  }
}

const cargarCursos = async () => {
  if (!gestionSeleccionada.value) return
  try {
    const res = await api.get(`/cursos?gestion=${gestionSeleccionada.value}`)
    cursos.value = res.data.data
  } catch (err) {
    console.error('Error al cargar cursos:', err)
  }
}

const cargarEstudiantes = async () => {
  if (!cursoSeleccionado.value) return
  try {
    const res = await api.get(`/cursos/${cursoSeleccionado.value}/estudiantes`)
    estudiantes.value = res.data.data
  } catch (err) {
    console.error('Error al cargar estudiantes:', err)
  }
}

watch(gestionSeleccionada, () => {
  cursoSeleccionado.value = ''
  estudianteSeleccionado.value = ''
  cursos.value = []
  estudiantes.value = []
  cargarCursos()
})

watch(cursoSeleccionado, () => {
  estudianteSeleccionado.value = ''
  estudiantes.value = []
  cargarEstudiantes()
})

const buscarNotas = async () => {
  if (!estudianteSeleccionado.value) return
  try {
    const res = await api.get('/historial/notas', {
      params: {
        gestion: gestionSeleccionada.value,
        periodo: periodoSeleccionado.value,
        estudiante_id: estudianteSeleccionado.value,
      },
    })
    notas.value = res.data.data
  } catch (err) {
    console.error('Error al buscar notas:', err)
  }
}

onMounted(() => {
  cargarGestiones()
})
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Historial de Notas</h1>

      <div class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-center">
        <select class="border rounded p-3 min-w-[12rem]" v-model="gestionSeleccionada">
          <option disabled value="">Seleccione gestión</option>
          <option v-for="g in gestiones" :key="g.id_gestion" :value="g.gestion">
            Gestión {{ g.gestion }}
          </option>
        </select>

        <select class="border rounded p-3 min-w-[12rem]" v-model="cursoSeleccionado">
          <option disabled value="">Seleccione curso</option>
          <option v-for="c in cursos" :key="c.id_curso" :value="c.id_curso">
            {{ c.grado_curso }} {{ c.paralelo_curso }}
          </option>
        </select>

        <select class="border rounded p-3 min-w-[14rem]" v-model="estudianteSeleccionado">
          <option disabled value="">Seleccione estudiante</option>
          <option v-for="e in estudiantes" :key="e.id_estudiante" :value="e.id_estudiante">
            {{ e.nombre_completo }}
          </option>
        </select>

        <select class="border rounded p-3 min-w-[10rem]" v-model="periodoSeleccionado">
          <option>Trimestre 1</option>
          <option>Trimestre 2</option>
          <option>Trimestre 3</option>
        </select>

        <BaseButton label="Buscar" @click="buscarNotas" />
      </div>

      <div v-if="notas.length === 0" class="text-gray-500">
        No se encontraron notas para los filtros seleccionados.
      </div>

      <div v-else>
        <table class="min-w-full border border-gray-200 rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2">Materia</th>
              <th class="p-2">Nota Final</th>
              <th class="p-2">Observación</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="n in notas" :key="n.id_nota">
              <td class="p-2">{{ n.materia?.campo_materia }} ({{ n.materia?.sigla_materia }})</td>
              <td class="p-2">{{ n.nota_final }}</td>
              <td class="p-2">{{ n.observacion }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
