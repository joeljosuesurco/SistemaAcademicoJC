<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-6">Mi Información</h2>

      <BaseButton
        v-if="!mostrarHorario"
        label="Ver Horario"
        color="info"
        outline
        class="mb-6"
        @click="cargarHorario"
      />

      <div v-if="loading" class="text-gray-500">Cargando datos del estudiante...</div>
      <div v-else-if="error" class="text-red-600">{{ error }}</div>

      <div v-else class="grid md:grid-cols-2 gap-6 text-base">
        <!-- Información del estudiante -->
        <div class="border border-blue-300 p-6 rounded shadow-xl bg-white">
          <div class="flex gap-6 items-start">
            <img
              v-if="estudiante.foto"
              :src="`/storage/fotos/${estudiante.foto}`"
              alt="Foto del estudiante"
              class="w-32 h-32 rounded-full object-cover border shadow"
            />
            <div class="space-y-1">
              <p><strong>RUDE:</strong> {{ estudiante.rude || '—' }}</p>
              <p><strong>Apellido Paterno:</strong> {{ estudiante.apellidos_pat }}</p>
              <p><strong>Apellido Materno:</strong> {{ estudiante.apellidos_mat }}</p>
              <p><strong>Nombres:</strong> {{ estudiante.nombres }}</p>
              <p><strong>Fecha de Nacimiento:</strong> {{ estudiante.fecha_nacimiento }}</p>
              <p><strong>Edad:</strong> {{ calcularEdad(estudiante.fecha_nacimiento) }}</p>
              <p><strong>Sexo:</strong> {{ estudiante.sexo }}</p>
              <p><strong>Nacionalidad:</strong> {{ estudiante.nacionalidad }}</p>
              <p><strong>Curso:</strong> {{ cursoTexto }}</p>
              <p><strong>Celular:</strong> {{ estudiante.celular }}</p>
              <p><strong>Dirección:</strong> {{ estudiante.direccion }}</p>
            </div>
          </div>
        </div>

        <!-- Documentos -->
        <div class="border border-green-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-xl font-semibold mb-4 text-green-800">Documentos</h3>
          <ul class="list-disc list-inside text-sm space-y-2">
            <li>
              Carnet de Identidad:
              <span class="text-gray-800">{{ estudiante.ci || '---' }}</span>
              <span class="ml-2" :class="estudiante.ci ? 'text-green-600' : 'text-red-600'">
                {{ estudiante.ci ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
            <li>
              Certificado de Nacimiento:
              <span class="text-gray-800">{{ estudiante.cert_nac || '---' }}</span>
              <span class="ml-2" :class="estudiante.cert_nac ? 'text-green-600' : 'text-red-600'">
                {{ estudiante.cert_nac ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
            <li>
              Libreta Anterior:
              <span class="text-gray-800">{{ estudiante.libreta || '---' }}</span>
              <span class="ml-2" :class="estudiante.libreta ? 'text-green-600' : 'text-red-600'">
                {{ estudiante.libreta ? '✓ Presentado' : '✗ No presentado' }}
              </span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Horario del estudiante -->
      <div v-if="mostrarHorario && horario.length" class="mt-10">
        <div class="bg-white border rounded shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Mi Horario</h3>
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

const estudiante = ref({})
const loading = ref(true)
const error = ref(null)

const mostrarHorario = ref(false)
const horario = ref([])
const diasOrdenados = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']

onMounted(async () => {
  try {
    const res = await api.get('/estudiante-auth/datos')
    estudiante.value = res.data.data || {}
  } catch (err) {
    error.value = 'No se pudo cargar la información del estudiante.'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const cargarHorario = async () => {
  mostrarHorario.value = false
  horario.value = []
  try {
    const res = await api.get('/estudiante-auth/horario')
    horario.value = res.data.data || []
    mostrarHorario.value = true
  } catch (err) {
    console.error('Error al cargar horario:', err)
  }
}

const cerrarHorario = () => {
  mostrarHorario.value = false
  horario.value = []
}

const bloquesHorario = computed(() => {
  if (!horario.value.length) return []
  return horario.value.map((h) => ({
    dia: h.dia,
    hora_inicio: h.hora_inicio,
    hora_fin: h.hora_fin,
    materia: h.materia,
  }))
})

const cursoTexto = computed(() => {
  const c = estudiante.value?.curso
  if (!c) return '—'
  return `${c.grado_curso} ${c.paralelo_curso} - ${c.nivel}`
})

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
</script>
