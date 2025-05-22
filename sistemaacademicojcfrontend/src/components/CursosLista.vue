<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const cursos = ref([])
const busqueda = ref('')
const currentPage = ref(1)
const pageSize = 7
const error = ref(null)
const cursoSeleccionado = ref(null)

const cargarCursos = async () => {
  try {
    const res = await api.get('/cursos')
    cursos.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo cargar la lista de cursos.'
  }
}

onMounted(() => {
  cargarCursos()
})

// Limpiar selección cuando el usuario busca
watch(busqueda, () => {
  cursoSeleccionado.value = null
})

const cursosFiltrados = computed(() => {
  if (!busqueda.value) return cursos.value
  const query = busqueda.value.toLowerCase()
  return cursos.value.filter(
    (curso) =>
      curso.grado_curso.toLowerCase().includes(query) ||
      curso.nivel_educativo?.codigo.toLowerCase().includes(query) ||
      curso.aula_curso.toLowerCase().includes(query) ||
      curso.turno_curso.toLowerCase().includes(query),
  )
})

const paginatedCursos = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return cursosFiltrados.value.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  return Math.ceil(cursosFiltrados.value.length / pageSize)
})

const verCurso = (curso) => {
  cursoSeleccionado.value = curso
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Listado de Cursos</h1>

    <div class="mb-4 flex justify-start">
      <input
        v-model="busqueda"
        type="text"
        placeholder="Buscar curso..."
        class="px-4 py-2 border rounded shadow-sm w-full md:w-1/3"
      />
    </div>

    <div v-if="error" class="text-red-600 mb-4">{{ error }}</div>

    <div class="overflow-auto rounded border">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 text-left">Grado</th>
            <th class="px-4 py-2 text-left">Paralelo</th>
            <th class="px-4 py-2 text-left">Nivel</th>
            <th class="px-4 py-2 text-left">Aula</th>
            <th class="px-4 py-2 text-left">Turno</th>
            <th class="px-4 py-2 text-left">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="curso in paginatedCursos" :key="curso.id_curso" class="hover:bg-gray-50">
            <td class="px-4 py-2">{{ curso.grado_curso }}</td>
            <td class="px-4 py-2">{{ curso.paralelo_curso }}</td>
            <td class="px-4 py-2">{{ curso.nivel_educativo?.codigo || '—' }}</td>
            <td class="px-4 py-2">{{ curso.aula_curso }}</td>
            <td class="px-4 py-2">{{ curso.turno_curso }}</td>
            <td class="px-4 py-2">
              <BaseButton label="Ver" color="info" size="sm" @click="verCurso(curso)" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-center items-center gap-2" v-if="totalPages > 1">
      <BaseButton
        label="« Anterior"
        :disabled="currentPage === 1"
        @click="currentPage--"
        size="sm"
      />
      <span class="text-sm">Página {{ currentPage }} de {{ totalPages }}</span>
      <BaseButton
        label="Siguiente »"
        :disabled="currentPage === totalPages"
        @click="currentPage++"
        size="sm"
      />
    </div>

    <div v-if="cursoSeleccionado" class="mt-6 p-4 border rounded shadow bg-white w-full">
      <h2 class="text-lg font-semibold mb-2">Información del Curso</h2>
      <p><strong>Grado:</strong> {{ cursoSeleccionado.grado_curso }}</p>
      <p><strong>Paralelo:</strong> {{ cursoSeleccionado.paralelo_curso }}</p>
      <p><strong>Nivel:</strong> {{ cursoSeleccionado.nivel_educativo?.codigo || '—' }}</p>
      <p><strong>Aula:</strong> {{ cursoSeleccionado.aula_curso }}</p>
      <p><strong>Turno:</strong> {{ cursoSeleccionado.turno_curso }}</p>
      <p><strong>Descripción:</strong> {{ cursoSeleccionado.descripcion }}</p>
    </div>
  </div>
</template>
