<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'

const profesores = ref([])
const search = ref('')
const loading = ref(true)
const error = ref(null)
const profesorSeleccionado = ref(null)
const mostrarDetalle = ref(false)
const detalleRef = ref(null)
const materiasAsignadas = ref([])
const cargandoMaterias = ref(false)
const vista = ref(null) // 'detalles' | 'materias'

const currentPage = ref(1)
const pageSize = 10

watch(search, () => {
  mostrarDetalle.value = false
  profesorSeleccionado.value = null
})

onMounted(async () => {
  try {
    const res = await api.get('/info/profesores')
    profesores.value = res.data.data
      .map((prof) => {
        const persona = prof.persona_rol?.persona || {}
        const documento = persona.documento || {}
        return {
          ...prof,
          apellidos_pat: persona.apellidos_pat || '',
          apellidos_mat: persona.apellidos_mat || '',
          nombres: persona.nombres_persona || '',
          fecha_nacimiento: persona.fecha_nacimiento || '',
          celular: persona.celular_persona || '',
          direccion: persona.direccion_persona || '',
          nacionalidad: persona.nacionalidad_persona || '',
          sexo: persona.sexo_persona || '',
          fotografia: persona.fotografia_persona || '',
          ci: documento.carnet_identidad || '',
          certificado_nacimiento: documento.certificado_nacimiento || '',
        }
      })
      .sort((a, b) => a.apellidos_pat.localeCompare(b.apellidos_pat))
  } catch (err) {
    error.value = 'No se pudo cargar la lista de profesores'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const profesoresFiltrados = computed(() => {
  const filtro = search.value.toLowerCase()
  const lista = profesores.value.filter(
    (p) =>
      `${p.apellidos_pat} ${p.apellidos_mat} ${p.nombres}`.toLowerCase().includes(filtro) ||
      p.especialidad_profesor.toLowerCase().includes(filtro),
  )
  const start = (currentPage.value - 1) * pageSize
  return lista.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  const total = profesores.value.length
  return Math.ceil(
    (search.value
      ? profesores.value.filter(
          (p) =>
            `${p.apellidos_pat} ${p.apellidos_mat} ${p.nombres}`
              .toLowerCase()
              .includes(search.value.toLowerCase()) ||
            p.especialidad_profesor.toLowerCase().includes(search.value.toLowerCase()),
        ).length
      : total) / pageSize,
  )
})

function verProfesor(profesor) {
  profesorSeleccionado.value = { ...profesor }
  mostrarDetalle.value = true
  materiasAsignadas.value = []
  vista.value = 'detalles'
  nextTick(() => detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }))
}

async function verMaterias(idProfesor) {
  cargandoMaterias.value = true
  mostrarDetalle.value = true
  vista.value = 'materias'
  try {
    const res = await api.get(`/info/profesores/${idProfesor}`)
    const prof = res.data.data
    profesorSeleccionado.value = {
      ...prof,
      apellidos_pat: prof.persona_rol?.persona?.apellidos_pat || '',
      apellidos_mat: prof.persona_rol?.persona?.apellidos_mat || '',
      nombres: prof.persona_rol?.persona?.nombres_persona || '',
      fecha_nacimiento: prof.persona_rol?.persona?.fecha_nacimiento || '',
      celular: prof.persona_rol?.persona?.celular_persona || '',
      direccion: prof.persona_rol?.persona?.direccion_persona || '',
      nacionalidad: prof.persona_rol?.persona?.nacionalidad_persona || '',
      sexo: prof.persona_rol?.persona?.sexo_persona || '',
      fotografia: prof.persona_rol?.persona?.fotografia_persona || '',
      ci: prof.persona_rol?.persona?.documento?.carnet_identidad || '',
      certificado_nacimiento: prof.persona_rol?.persona?.documento?.certificado_nacimiento || '',
    }
    materiasAsignadas.value = prof.curso_profesor_materia_gestion || []
  } catch (err) {
    console.error('Error al cargar materias del profesor', err)
    materiasAsignadas.value = []
  } finally {
    cargandoMaterias.value = false
    nextTick(() => detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }))
  }
}

function calcularEdad(fechaNacimiento) {
  if (!fechaNacimiento) return '—'
  const hoy = new Date()
  const nacimiento = new Date(fechaNacimiento)
  let edad = hoy.getFullYear() - nacimiento.getFullYear()
  const m = hoy.getMonth() - nacimiento.getMonth()
  if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) edad--
  return `${edad} años`
}
</script>

<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Listado de Profesores</h2>

    <div class="mb-4 flex flex-col md:flex-row md:items-center md:gap-4">
      <input
        v-model="search"
        type="text"
        placeholder="Buscar por nombre o especialidad..."
        class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
      />
    </div>

    <div v-if="loading" class="text-gray-500">Cargando profesores...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>

    <table v-else class="w-full text-left bg-white rounded shadow">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2">Apellido Paterno</th>
          <th class="p-2">Apellido Materno</th>
          <th class="p-2">Nombres</th>
          <th class="p-2">Especialidad</th>
          <th class="p-2">Estado</th>
          <th class="p-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in profesoresFiltrados" :key="p.id_profesor">
          <td class="p-2">{{ p.apellidos_pat }}</td>
          <td class="p-2">{{ p.apellidos_mat }}</td>
          <td class="p-2">{{ p.nombres }}</td>
          <td class="p-2">{{ p.especialidad_profesor }}</td>
          <td class="p-2 capitalize">{{ p.estado_profesor }}</td>
          <td class="p-2 space-x-2">
            <BaseButton color="info" label="Ver" small @click="verProfesor(p)" />
            <BaseButton
              color="success"
              label="Ver Materias"
              small
              @click="verMaterias(p.id_profesor)"
            />
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

    <div v-if="mostrarDetalle && profesorSeleccionado" ref="detalleRef" class="mt-6">
      <!-- DETALLES -->
      <div v-if="vista === 'detalles'" class="grid md:grid-cols-2 gap-6 text-base">
        <div class="border border-blue-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-xl font-semibold mb-4 text-blue-800">Detalles del Profesor</h3>
          <div class="flex gap-6 items-start">
            <img
              v-if="profesorSeleccionado.fotografia"
              :src="`/storage/fotos/${profesorSeleccionado.fotografia}`"
              alt="Foto del profesor"
              class="w-32 h-32 rounded-full object-cover border shadow"
            />

            <div class="space-y-1">
              <p><strong>Apellido Paterno:</strong> {{ profesorSeleccionado.apellidos_pat }}</p>
              <p><strong>Apellido Materno:</strong> {{ profesorSeleccionado.apellidos_mat }}</p>
              <p><strong>Nombres:</strong> {{ profesorSeleccionado.nombres }}</p>
              <p>
                <strong>Fecha de Nacimiento:</strong> {{ profesorSeleccionado.fecha_nacimiento }}
              </p>
              <p>
                <strong>Edad:</strong> {{ calcularEdad(profesorSeleccionado.fecha_nacimiento) }}
              </p>
              <p><strong>Sexo:</strong> {{ profesorSeleccionado.sexo }}</p>
              <p><strong>Estado:</strong> {{ profesorSeleccionado.estado_profesor }}</p>
              <p><strong>Especialidad:</strong> {{ profesorSeleccionado.especialidad_profesor }}</p>
              <p><strong>Celular:</strong> {{ profesorSeleccionado.celular }}</p>
              <p><strong>Dirección:</strong> {{ profesorSeleccionado.direccion }}</p>
              <p><strong>Nacionalidad:</strong> {{ profesorSeleccionado.nacionalidad }}</p>
            </div>
          </div>
        </div>
        <!-- DOCUMENTOS -->
        <div class="border border-green-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-xl font-semibold mb-4 text-green-800">Documentos del Profesor</h3>
          <ul class="list-disc list-inside text-sm space-y-2">
            <li>
              Carnet de Identidad:
              <span class="text-gray-800">{{ profesorSeleccionado.ci || '---' }}</span>
              <span
                class="ml-2"
                :class="profesorSeleccionado.ci ? 'text-green-600' : 'text-red-600'"
              >
                {{ profesorSeleccionado.ci ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
            <li>
              Certificado de Nacimiento:
              <span class="text-gray-800">{{
                profesorSeleccionado.certificado_nacimiento || '---'
              }}</span>
              <span
                class="ml-2"
                :class="
                  profesorSeleccionado.certificado_nacimiento ? 'text-green-600' : 'text-red-600'
                "
              >
                {{
                  profesorSeleccionado.certificado_nacimiento ? '✓ Presentado' : '✗ No presentado'
                }}
              </span>
            </li>
            <li>
              Título de Provisión Nacional:
              <span class="text-gray-800">{{
                profesorSeleccionado.titulo_provision_nacional || '---'
              }}</span>
            </li>
            <li>
              RDA:
              <span class="text-gray-800">{{ profesorSeleccionado.rda || '---' }}</span>
            </li>
            <li>
              CAS:
              <span class="text-gray-800">{{ profesorSeleccionado.cas || '---' }}</span>
            </li>
          </ul>
        </div>
      </div>
      <!-- MATERIAS -->
      <div
        v-else-if="vista === 'materias'"
        class="border border-purple-300 p-6 rounded shadow-xl bg-white md:col-span-2"
      >
        <h3 class="text-xl font-semibold mb-4 text-purple-800">Materias que Dicta</h3>
        <div v-if="cargandoMaterias" class="text-gray-600">Cargando materias...</div>
        <div v-else-if="materiasAsignadas.length === 0" class="text-gray-500">
          No tiene materias asignadas.
        </div>
        <table v-else class="w-full text-sm border">
          <thead class="bg-purple-100 text-left">
            <tr>
              <th class="p-2">Materia</th>
              <th class="p-2">Sigla</th>
              <th class="p-2">Nivel Educativo</th>
              <th class="p-2">Curso</th>
              <th class="p-2">Gestión</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="asig in materiasAsignadas" :key="asig.id">
              <td class="p-2">{{ asig.materia?.campo_materia || '—' }}</td>
              <td class="p-2">{{ asig.materia?.sigla_materia || '—' }}</td>
              <td class="p-2">{{ asig.materia?.nivel_educativo?.nombre || '—' }}</td>
              <td class="p-2">
                {{ asig.curso?.grado_curso || '' }}
                {{ asig.curso?.paralelo_curso || '' }} -
                {{ asig.curso?.nivel_educativo?.nombre || '' }}
              </td>
              <td class="p-2">{{ asig.gestion?.nombre_gestion || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
