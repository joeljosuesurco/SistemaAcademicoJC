<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-4">Mis Hijos</h2>

      <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <input
          type="text"
          v-model="search"
          placeholder="Buscar por nombre o curso..."
          class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
        />
      </div>

      <div v-if="loading" class="text-gray-500">Cargando hijos...</div>
      <div v-else-if="error" class="text-red-600">{{ error }}</div>

      <table v-else class="w-full text-left bg-white rounded shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">Nombre completo</th>
            <th class="p-2">Curso</th>
            <th class="p-2">Nivel</th>
            <th class="p-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="hijo in hijosFiltrados" :key="hijo.id_estudiante">
            <td class="p-2">{{ hijo.nombre_completo }}</td>
            <td class="p-2">{{ hijo.curso?.grado_curso }} {{ hijo.curso?.paralelo_curso }}</td>
            <td class="p-2">{{ hijo.curso?.nivel }}</td>
            <td class="p-2 space-x-2">
              <BaseButton color="info" label="Ver" @click="verDetalle(hijo)" />
              <BaseButton color="primary" label="Ver Horario" @click="verHorario(hijo)" />
            </td>
          </tr>
        </tbody>
      </table>

      <div
        v-if="mostrarDetalle && estudianteSeleccionado"
        class="mt-8 grid md:grid-cols-2 gap-6 text-base"
      >
        <div class="border border-blue-300 p-6 rounded shadow-xl bg-white">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-blue-800">Detalles del Estudiante</h3>
            <BaseButton label="Cerrar" color="warning" @click="cerrarDetalle" />
          </div>
          <div class="flex gap-6 items-start">
            <img
              v-if="estudianteSeleccionado.foto"
              :src="`/storage/fotos/${estudianteSeleccionado.foto}`"
              alt="Foto del estudiante"
              class="w-32 h-32 rounded-full object-cover border shadow"
            />
            <div class="space-y-1">
              <p><strong>RUDE:</strong> {{ estudianteSeleccionado.rude || '—' }}</p>
              <p><strong>Apellido Paterno:</strong> {{ estudianteSeleccionado.apellidos_pat }}</p>
              <p><strong>Apellido Materno:</strong> {{ estudianteSeleccionado.apellidos_mat }}</p>
              <p><strong>Nombres:</strong> {{ estudianteSeleccionado.nombres }}</p>
              <p>
                <strong>Fecha de Nacimiento:</strong> {{ estudianteSeleccionado.fecha_nacimiento }}
              </p>
              <p>
                <strong>Edad:</strong> {{ calcularEdad(estudianteSeleccionado.fecha_nacimiento) }}
              </p>
              <p><strong>Sexo:</strong> {{ estudianteSeleccionado.sexo }}</p>
              <p><strong>Nacionalidad:</strong> {{ estudianteSeleccionado.nacionalidad }}</p>
              <p><strong>Curso:</strong> {{ cursoTexto }}</p>
              <p><strong>Celular:</strong> {{ estudianteSeleccionado.celular }}</p>
              <p><strong>Dirección:</strong> {{ estudianteSeleccionado.direccion }}</p>
            </div>
          </div>
        </div>

        <div class="border border-green-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-xl font-semibold mb-4 text-green-800">Documentos del Estudiante</h3>
          <ul class="list-disc list-inside text-sm space-y-2">
            <li>
              Carnet de Identidad:
              <span class="text-gray-800">{{ estudianteSeleccionado.ci || '---' }}</span>
              <span
                class="ml-2"
                :class="estudianteSeleccionado.ci ? 'text-green-600' : 'text-red-600'"
              >
                {{ estudianteSeleccionado.ci ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
            <li>
              Certificado de Nacimiento:
              <span class="text-gray-800">{{ estudianteSeleccionado.cert_nac || '---' }}</span>
              <span
                class="ml-2"
                :class="estudianteSeleccionado.cert_nac ? 'text-green-600' : 'text-red-600'"
              >
                {{ estudianteSeleccionado.cert_nac ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
            <li>
              Libreta Anterior:
              <span class="text-gray-800">{{ estudianteSeleccionado.libreta || '---' }}</span>
              <span
                class="ml-2"
                :class="estudianteSeleccionado.libreta ? 'text-green-600' : 'text-red-600'"
              >
                {{ estudianteSeleccionado.libreta ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Horario del estudiante -->
      <div v-if="mostrarHorario && horario.length" class="mt-10">
        <div class="bg-white border rounded shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Horario de {{ hijoSeleccionado.nombre_completo }}</h3>
            <BaseButton label="Cerrar" color="warning" @click="cerrarHorario" />
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded">
              <thead class="bg-blue-100">
                <tr>
                  <th class="px-4 py-2 border">Hora</th>
                  <th v-for="dia in diasOrdenados" :key="dia" class="px-4 py-2 border">
                    {{ dia }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(bloque, i) in bloquesHorario" :key="i" class="hover:bg-blue-50">
                  <td class="px-4 py-2 border bg-blue-50">
                    {{ bloque.hora_inicio }} - {{ bloque.hora_fin }}
                  </td>
                  <td v-for="dia in diasOrdenados" :key="dia" class="px-4 py-2 border text-center">
                    <span
                      v-if="bloque.dia === dia && bloque.materia"
                      class="inline-block bg-blue-200 text-blue-900 px-2 py-1 rounded text-xs leading-tight"
                    >
                      <strong>{{ bloque.materia.sigla_materia }}</strong
                      ><br />
                      {{ bloque.materia.area_materia }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const hijos = ref([])
const horario = ref([])
const hijoSeleccionado = ref(null)
const mostrarHorario = ref(false)
const mostrarDetalle = ref(false)
const estudianteSeleccionado = ref(null)
const search = ref('')
const loading = ref(true)
const error = ref(null)

const diasOrdenados = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']

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
  if (!search.value) return hijos.value
  return hijos.value.filter(
    (h) =>
      h.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) ||
      `${h.curso?.grado_curso} ${h.curso?.paralelo_curso}`
        .toLowerCase()
        .includes(search.value.toLowerCase()),
  )
})

const verHorario = async (hijo) => {
  mostrarDetalle.value = false
  hijoSeleccionado.value = hijo
  mostrarHorario.value = false
  horario.value = []
  try {
    const res = await api.get(`/padre-auth/horario/${hijo.id_estudiante}`)
    horario.value = res.data.data || []
    mostrarHorario.value = true
  } catch (err) {
    console.error('Error al cargar horario:', err)
  }
}

const cerrarHorario = () => {
  mostrarHorario.value = false
  hijoSeleccionado.value = null
}

const verDetalle = (hijo) => {
  mostrarHorario.value = false
  mostrarDetalle.value = true
  estudianteSeleccionado.value = { ...hijo }
}

const cerrarDetalle = () => {
  mostrarDetalle.value = false
  estudianteSeleccionado.value = null
}

const calcularEdad = (fechaNacimiento) => {
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

const cursoTexto = computed(() => {
  const c = estudianteSeleccionado.value?.curso
  if (!c) return '—'
  return `${c.grado_curso} ${c.paralelo_curso} - ${c.nivel}`
})

const bloquesHorario = computed(() => {
  if (!horario.value.length) return []
  return horario.value.map((h) => ({
    dia: h.dia,
    hora_inicio: h.hora_inicio,
    hora_fin: h.hora_fin,
    materia: h.materia,
  }))
})
</script>
