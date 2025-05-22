<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-4">Mis Materias</h2>

      <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <input
          type="text"
          v-model="search"
          placeholder="Buscar por materia o curso..."
          class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
        />
      </div>

      <div v-if="paginatedMaterias.length" class="overflow-x-auto">
        <table class="min-w-full bg-white border">
          <thead class="bg-slate-100">
            <tr>
              <th class="px-4 py-2 border">Área</th>
              <th class="px-4 py-2 border">Campo</th>
              <th class="px-4 py-2 border">Sigla</th>
              <th class="px-4 py-2 border">Curso</th>
              <th class="px-4 py-2 border">Nivel</th>
              <th class="px-4 py-2 border">Gestión</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in paginatedMaterias" :key="item.id" class="hover:bg-slate-50">
              <td class="px-4 py-2 border">{{ item.materia.area_materia }}</td>
              <td class="px-4 py-2 border">{{ item.materia.campo_materia }}</td>
              <td class="px-4 py-2 border">{{ item.materia.sigla_materia }}</td>
              <td class="px-4 py-2 border">
                {{ item.curso.grado_curso }} {{ item.curso.paralelo_curso }}
              </td>
              <td class="px-4 py-2 border">{{ item.curso.nivel_educativo.nombre }}</td>
              <td class="px-4 py-2 border">{{ item.gestion.gestion }}</td>
            </tr>
          </tbody>
        </table>

        <div class="mt-4 flex justify-center">
          <button
            class="mx-2 px-3 py-1 border rounded"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            Anterior
          </button>
          <span>Página {{ currentPage }} de {{ totalPages }}</span>
          <button
            class="mx-2 px-3 py-1 border rounded"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            Siguiente
          </button>
        </div>
      </div>

      <div v-else class="text-gray-600">No se encontraron materias asignadas.</div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'

const materias = ref([])
const search = ref('')
const currentPage = ref(1)
const pageSize = 10

onMounted(async () => {
  try {
    const res = await api.get('/profesor-auth/cursos')
    materias.value = res.data.data || []
  } catch (err) {
    console.error('Error al cargar las materias del profesor:', err)
  }
})

const materiasFiltradas = computed(() => {
  if (!search.value) return materias.value
  return materias.value.filter((item) => {
    const texto =
      `${item.materia.area_materia} ${item.materia.campo_materia} ${item.curso.grado_curso} ${item.curso.paralelo_curso}`.toLowerCase()
    return texto.includes(search.value.toLowerCase())
  })
})

const totalPages = computed(() => Math.ceil(materiasFiltradas.value.length / pageSize))
const paginatedMaterias = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return materiasFiltradas.value.slice(start, start + pageSize)
})
</script>

<style scoped></style>
