<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const form = ref({
  grado_curso: '',
  paralelo_curso: '',
  nivel_educativo_id: '',
  aula_curso: '',
  turno_curso: '',
  descripcion: '',
})

const errores = ref({})
const mensaje = ref(null)
const cursosRecientes = ref([])
const cursoCreado = ref(null)

const grados = ['PRIMERO', 'SEGUNDO', 'TERCERO', 'CUARTO', 'QUINTO', 'SEXTO']
const paralelos = ['A', 'B', 'C', 'D']
const turnos = ['MAÑANA', 'TARDE', 'NOCHE']

const vistaPreviaCurso = ref(null)

watch(
  () => [form.value.grado_curso, form.value.paralelo_curso, form.value.nivel_educativo_id],
  ([grado, paralelo, nivelId]) => {
    const nivel = niveles.value.find((n) => n.id === nivelId)
    if (grado && paralelo && nivel) {
      vistaPreviaCurso.value = `${grado} ${paralelo} - ${nivel.nombre}`
    } else {
      vistaPreviaCurso.value = null
    }
  },
)

const niveles = computed(() => {
  const mapa = new Map()
  cursosRecientes.value.forEach((curso) => {
    const nivel = curso.nivel_educativo
    if (nivel && !mapa.has(nivel.id)) {
      mapa.set(nivel.id, nivel)
    }
  })
  return Array.from(mapa.values())
})

const cursosSimilares = computed(() => {
  if (!form.value.grado_curso || !form.value.nivel_educativo_id) return []
  return cursosRecientes.value.filter(
    (curso) =>
      curso.grado_curso === form.value.grado_curso &&
      curso.nivel_educativo_id === form.value.nivel_educativo_id &&
      curso.paralelo_curso !== form.value.paralelo_curso,
  )
})

const cursoDuplicado = computed(() => {
  return cursosRecientes.value.find(
    (curso) =>
      curso.grado_curso === form.value.grado_curso &&
      curso.paralelo_curso === form.value.paralelo_curso &&
      curso.nivel_educativo_id === form.value.nivel_educativo_id,
  )
})

const cargarCursos = async () => {
  try {
    const res = await api.get('/cursos')
    cursosRecientes.value = res.data.data.reverse()
  } catch {
    console.error('Error cargando cursos')
  }
}

onMounted(() => {
  cargarCursos()
})

const enviarFormulario = async () => {
  errores.value = {}
  mensaje.value = null

  try {
    const res = await api.post('/cursos', form.value)
    mensaje.value = 'Curso creado correctamente.'

    cursosRecientes.value.unshift(res.data.data)
    cursoCreado.value = res.data.data

    form.value = {
      grado_curso: '',
      paralelo_curso: '',
      nivel_educativo_id: '',
      aula_curso: '',
      turno_curso: '',
      descripcion: '',
    }
    vistaPreviaCurso.value = null
  } catch (error) {
    if (error.response?.status === 422) {
      errores.value = error.response.data.errors
    } else {
      mensaje.value = 'Ocurrió un error al crear el curso.'
    }
  }
}

const cancelar = () => {
  form.value = {
    grado_curso: '',
    paralelo_curso: '',
    nivel_educativo_id: '',
    aula_curso: '',
    turno_curso: '',
    descripcion: '',
  }
  vistaPreviaCurso.value = null
  errores.value = {}
  mensaje.value = null
  cursoCreado.value = null
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Registrar nuevo curso</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Formulario -->
        <div class="bg-white p-6 rounded-2xl shadow">
          <form @submit.prevent="enviarFormulario">
            <div v-if="mensaje" class="mb-4 text-green-600 font-medium">{{ mensaje }}</div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <select
                  v-model="form.grado_curso"
                  class="w-full border px-4 py-2 rounded text-gray-700"
                >
                  <option disabled value="">Seleccione grado *</option>
                  <option v-for="g in grados" :key="g" :value="g">{{ g }}</option>
                </select>
                <span v-if="errores.grado_curso" class="text-red-600 text-sm">
                  {{ errores.grado_curso[0] }}
                </span>
              </div>

              <div>
                <select
                  v-model="form.paralelo_curso"
                  class="w-full border px-4 py-2 rounded text-gray-700"
                >
                  <option disabled value="">Seleccione paralelo *</option>
                  <option v-for="p in paralelos" :key="p" :value="p">{{ p }}</option>
                </select>
                <span v-if="errores.paralelo_curso" class="text-red-600 text-sm">
                  {{ errores.paralelo_curso[0] }}
                </span>
              </div>

              <div>
                <select
                  v-model="form.nivel_educativo_id"
                  class="w-full border px-4 py-2 rounded text-gray-700"
                >
                  <option disabled value="">Seleccione nivel *</option>
                  <option v-for="n in niveles" :key="n.id" :value="n.id">{{ n.nombre }}</option>
                </select>
                <span v-if="errores.nivel_educativo_id" class="text-red-600 text-sm">
                  {{ errores.nivel_educativo_id[0] }}
                </span>
              </div>

              <div>
                <input
                  v-model="form.aula_curso"
                  type="text"
                  placeholder="Aula *"
                  class="w-full border px-4 py-2 rounded"
                />
                <span v-if="errores.aula_curso" class="text-red-600 text-sm">
                  {{ errores.aula_curso[0] }}
                </span>
              </div>

              <div>
                <select
                  v-model="form.turno_curso"
                  class="w-full border px-4 py-2 rounded text-gray-700"
                >
                  <option disabled value="">Seleccione turno</option>
                  <option v-for="t in turnos" :key="t" :value="t">{{ t }}</option>
                </select>
              </div>

              <div>
                <input
                  v-model="form.descripcion"
                  type="text"
                  placeholder="Descripción"
                  class="w-full border px-4 py-2 rounded"
                />
              </div>
            </div>

            <div class="flex gap-2 pt-6">
              <BaseButton type="submit" label="Registrar" color="success" />
              <BaseButton type="button" label="Cancelar" color="default" @click="cancelar" />
            </div>
          </form>
        </div>

        <!-- Card derecho: listado de cursos -->
        <div class="bg-white p-6 rounded-2xl shadow">
          <h2 class="text-lg font-bold mb-4">Cursos registrados</h2>

          <div v-if="vistaPreviaCurso" class="mb-4 p-3 border rounded bg-blue-50 text-blue-700">
            Curso en preparación: <strong>{{ vistaPreviaCurso }}</strong>
          </div>

          <div v-if="cursoDuplicado" class="mb-4 p-3 border rounded bg-red-100 text-red-700">
            Ya existe un curso con este grado, paralelo y nivel.
          </div>

          <div v-if="cursosSimilares.length" class="mb-4">
            <p class="text-sm text-gray-700 font-medium mb-2">Cursos similares:</p>
            <ul class="space-y-2">
              <li
                v-for="curso in cursosSimilares"
                :key="curso.id_curso"
                class="border px-4 py-2 rounded bg-yellow-50 text-yellow-800"
              >
                {{ curso.grado_curso }} {{ curso.paralelo_curso }} -
                {{ curso.nivel_educativo.nombre }}
              </li>
            </ul>
          </div>

          <hr class="my-4" />

          <div class="max-h-[300px] overflow-y-auto pr-2">
            <ul v-if="cursosRecientes.length" class="space-y-2">
              <li
                v-for="curso in cursosRecientes"
                :key="curso.id_curso"
                class="border px-4 py-2 rounded hover:bg-gray-50"
              >
                <p class="font-semibold">{{ curso.grado_curso }} {{ curso.paralelo_curso }}</p>
                <p class="text-sm text-gray-600">{{ curso.nivel_educativo?.nombre }}</p>
              </li>
            </ul>
            <p v-else class="text-gray-500">No hay cursos registrados aún.</p>
          </div>
        </div>
      </div>

      <!-- Card debajo: curso creado -->
      <div v-if="cursoCreado" class="mt-6 bg-white p-6 rounded-2xl shadow">
        <h2 class="text-lg font-bold mb-2 text-green-700">Curso creado recientemente</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <p><strong>Grado:</strong> {{ cursoCreado.grado_curso }}</p>
          <p><strong>Paralelo:</strong> {{ cursoCreado.paralelo_curso }}</p>
          <p><strong>Nivel:</strong> {{ cursoCreado.nivel_educativo?.nombre }}</p>
          <p><strong>Aula:</strong> {{ cursoCreado.aula_curso }}</p>
          <p><strong>Turno:</strong> {{ cursoCreado.turno_curso || '-' }}</p>
          <p><strong>Descripción:</strong> {{ cursoCreado.descripcion || '-' }}</p>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
