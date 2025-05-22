<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'

const estudiantes = ref([])
const loading = ref(true)
const error = ref(null)
const search = ref('')
const estudianteSeleccionado = ref(null)
const mostrarDetalle = ref(false)
const detalleRef = ref(null)
const gestionActual = ref('')

const currentPage = ref(1)
const pageSize = 10

watch(search, () => {
  mostrarDetalle.value = false
  estudianteSeleccionado.value = null
})

onMounted(async () => {
  try {
    const res = await api.get('/info/estudiantes')
    const data = res.data.estudiantes || res.data // compatible con ambos formatos

    estudiantes.value = data
      .map((est) => {
        const personaRol = est.persona_rol || {}
        const persona = personaRol.persona || {}
        const documento = persona.documento || {}
        //const cursoRel = est.cursos[0] || {}
        const cursoRel = est.cursos.find((c) => c.estado === 'inscrito') || {}
        const curso = cursoRel.curso || {}
        const gestion = cursoRel.gestion || {}

        if (gestion.estado_gestion === 'activa') {
          gestionActual.value = gestion.nombre_gestion
        }

        const cursoNombre = curso.id_curso
          ? `${curso.grado_curso} ${curso.paralelo_curso} - ${curso.nivel_educativo?.codigo || '—'}`
          : 'Sin curso'

        return {
          ...est,
          nombre_completo:
            `${persona.apellidos_pat} ${persona.apellidos_mat} ${persona.nombres_persona}`.trim(),
          apellidos_pat: persona.apellidos_pat || '',
          apellidos_mat: persona.apellidos_mat || '',
          nombres: persona.nombres_persona || '',
          fecha_nacimiento: persona.fecha_nacimiento || '',
          curso: cursoNombre,
          estado: cursoRel.estado || 'no_inscrito',
          persona,
          padres: est.padres || [],
          carnet_identidad: documento.carnet_identidad || null,
          certificado_nacimiento: documento.certificado_nacimiento || null,
          libreta_anterior: est.libreta_anterior || null,
          rude: est.rude || null,
        }
      })
      .sort((a, b) => {
        const apPat = a.apellidos_pat.toLowerCase()
        const bpPat = b.apellidos_pat.toLowerCase()
        if (apPat !== bpPat) return apPat.localeCompare(bpPat)

        const apMat = a.apellidos_mat.toLowerCase()
        const bpMat = b.apellidos_mat.toLowerCase()
        if (apMat !== bpMat) return apMat.localeCompare(bpMat)

        return a.nombres.toLowerCase().localeCompare(b.nombres.toLowerCase())
      })
  } catch (err) {
    error.value = 'No se pudo cargar la lista de estudiantes'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const estudiantesFiltrados = computed(() => {
  const filtrados = search.value
    ? estudiantes.value.filter(
        (est) =>
          est.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
          est.curso.toLowerCase().includes(search.value.toLowerCase()),
      )
    : estudiantes.value

  const start = (currentPage.value - 1) * pageSize
  return filtrados.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  const total = search.value
    ? estudiantes.value.filter(
        (est) =>
          est.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
          est.curso.toLowerCase().includes(search.value.toLowerCase()),
      ).length
    : estudiantes.value.length
  return Math.ceil(total / pageSize)
})

function verEstudiante(estudiante) {
  console.log('Foto estudiante:', estudiante.persona.fotografia_persona)
  estudianteSeleccionado.value = { ...estudiante }
  mostrarDetalle.value = true

  nextTick(() => {
    detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

function calcularEdad(fechaNacimiento) {
  if (!fechaNacimiento) return '—'

  const hoy = new Date()
  const nacimiento = new Date(fechaNacimiento)
  let edad = hoy.getFullYear() - nacimiento.getFullYear()
  const m = hoy.getMonth() - nacimiento.getMonth()

  if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
    edad--
  }

  return `${edad} años`
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-xl font-bold mb-4">Listado de Estudiantes</h2>

      <div class="mb-4 flex flex-col md:flex-row md:items-center md:gap-4">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nombre o curso..."
          class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
        />
        <div
          v-if="gestionActual"
          class="bg-blue-100 text-blue-800 px-4 py-2 rounded shadow text-sm mt-2 md:mt-0"
        >
          Gestión actual: <strong>{{ gestionActual }}</strong>
        </div>
      </div>

      <div v-if="loading" class="text-gray-500">Cargando estudiantes...</div>
      <div v-else-if="error" class="text-red-600">{{ error }}</div>

      <table v-else class="w-full text-left bg-white rounded shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">Apellido Paterno</th>
            <th class="p-2">Apellido Materno</th>
            <th class="p-2">Nombres</th>
            <th class="p-2">Curso</th>
            <th class="p-2">Estado</th>
            <th class="p-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="est in estudiantesFiltrados" :key="est.id_estudiante">
            <td class="p-2">{{ est.apellidos_pat }}</td>
            <td class="p-2">{{ est.apellidos_mat }}</td>
            <td class="p-2">{{ est.nombres }}</td>
            <td class="p-2">{{ est.curso }}</td>
            <td class="p-2 capitalize">{{ est.estado }}</td>
            <td class="p-2 space-x-2">
              <BaseButton color="info" label="Ver" small @click="verEstudiante(est)" />
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 flex justify-center items-center gap-2" v-if="totalPages > 1">
        <BaseButton label="« Anterior" :disabled="currentPage === 1" @click="currentPage--" small />
        <span>Página {{ currentPage }} de {{ totalPages }}</span>
        <BaseButton
          label="Siguiente »"
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          small
        />
      </div>

      <div v-if="mostrarDetalle" ref="detalleRef" class="mt-6 grid md:grid-cols-2 gap-6 text-base">
        <div class="border border-blue-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-xl font-semibold mb-4 text-blue-800">Detalles del Estudiante</h3>
          <div class="flex gap-6 items-start">
            <img
              v-if="estudianteSeleccionado.persona.fotografia_persona"
              :src="`/storage/fotos/${estudianteSeleccionado.persona.fotografia_persona}`"
              alt="Foto del estudiante"
              class="w-32 h-32 object-cover rounded-full border shadow"
            />
            <div class="space-y-1">
              <p><strong>RUDE:</strong> {{ estudianteSeleccionado.rude }}</p>
              <p><strong>Apellido Paterno:</strong> {{ estudianteSeleccionado.apellidos_pat }}</p>
              <p><strong>Apellido Materno:</strong> {{ estudianteSeleccionado.apellidos_mat }}</p>
              <p><strong>Nombres:</strong> {{ estudianteSeleccionado.nombres }}</p>
              <p>
                <strong>Fecha de Nacimiento:</strong> {{ estudianteSeleccionado.fecha_nacimiento }}
              </p>
              <p>
                <strong>Edad:</strong> {{ calcularEdad(estudianteSeleccionado.fecha_nacimiento) }}
              </p>
              <p><strong>Sexo:</strong> {{ estudianteSeleccionado.persona.sexo_persona }}</p>
              <p>
                <strong>Nacionalidad:</strong>
                {{ estudianteSeleccionado.persona.nacionalidad_persona }}
              </p>
              <p><strong>Estado:</strong> {{ estudianteSeleccionado.estado }}</p>
              <p><strong>Curso:</strong> {{ estudianteSeleccionado.curso }}</p>

              <p><strong>Celular:</strong> {{ estudianteSeleccionado.persona.celular_persona }}</p>
              <p>
                <strong>Dirección:</strong> {{ estudianteSeleccionado.persona.direccion_persona }}
              </p>
            </div>
          </div>
        </div>

        <div class="border border-green-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-xl font-semibold mb-4 text-green-800">Documentos del Estudiante</h3>
          <ul class="list-disc list-inside text-sm space-y-2">
            <li>
              Carnet de Identidad:
              <span class="text-gray-800">{{
                estudianteSeleccionado.carnet_identidad || '---'
              }}</span>
              <span
                class="ml-2"
                :class="estudianteSeleccionado.carnet_identidad ? 'text-green-600' : 'text-red-600'"
              >
                {{ estudianteSeleccionado.carnet_identidad ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
            <li>
              Certificado de Nacimiento:
              <span class="text-gray-800">{{
                estudianteSeleccionado.certificado_nacimiento || '---'
              }}</span>
              <span
                class="ml-2"
                :class="
                  estudianteSeleccionado.certificado_nacimiento ? 'text-green-600' : 'text-red-600'
                "
              >
                {{
                  estudianteSeleccionado.certificado_nacimiento ? '✓ Presentado' : '✗ No presentado'
                }}
              </span>
            </li>
            <li>
              Libreta Anterior:
              <span class="text-gray-800">{{
                estudianteSeleccionado.libreta_anterior || '---'
              }}</span>
              <span
                class="ml-2"
                :class="estudianteSeleccionado.libreta_anterior ? 'text-green-600' : 'text-red-600'"
              >
                {{ estudianteSeleccionado.libreta_anterior ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
          </ul>
          <div class="mt-6">
            <h4 class="font-semibold text-gray-700 mb-1">Padres de Familia:</h4>
            <ul class="list-disc list-inside text-sm">
              <li v-for="padre in estudianteSeleccionado.padres" :key="padre.padres_id_padre">
                {{ padre.padre.persona_rol.persona.apellidos_pat }}
                {{ padre.padre.persona_rol.persona.apellidos_mat }}
                {{ padre.padre.persona_rol.persona.nombres_persona }}
                ({{ padre.padre.profesion_padre }})
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
