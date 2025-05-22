<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Administrar Cursos</h1>

      <div v-if="!cursoSeleccionado">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-xl font-semibold mb-4">Listado de cursos</h2>
            <input
              v-model="busqueda"
              type="text"
              placeholder="Buscar por grado, nivel o paralelo"
              class="w-full mb-4 px-4 py-2 border rounded shadow-sm"
            />
            <div class="overflow-auto max-h-[500px] border rounded">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-100 sticky top-0">
                  <tr>
                    <th class="px-4 py-2 text-left">Grado</th>
                    <th class="px-4 py-2 text-left">Paralelo</th>
                    <th class="px-4 py-2 text-left">Nivel</th>
                    <th class="px-4 py-2 text-left">Aula</th>
                    <th class="px-4 py-2 text-left">Turno</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="curso in cursosFiltrados"
                    :key="curso.id_curso"
                    @click="seleccionarCurso(curso)"
                    :class="[
                      'cursor-pointer',
                      cursoSeleccionado?.id_curso === curso.id_curso
                        ? 'bg-blue-100'
                        : 'hover:bg-gray-50',
                    ]"
                  >
                    <td class="px-4 py-2">{{ curso.grado_curso }}</td>
                    <td class="px-4 py-2">{{ curso.paralelo_curso }}</td>
                    <td class="px-4 py-2">{{ curso.nivel_educativo?.nombre }}</td>
                    <td class="px-4 py-2">{{ curso.aula_curso }}</td>
                    <td class="px-4 py-2">{{ curso.turno_curso }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div v-else>
        <div v-if="!mostrarHorario" class="bg-white p-6 rounded shadow-md">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">
              Editar curso: {{ cursoSeleccionado.grado_curso }}
              {{ cursoSeleccionado.paralelo_curso }}
            </h2>
            <BaseButton label="Cancelar" color="secondary" @click="cancelar" />
          </div>

          <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div>
              <label class="block text-sm font-medium mb-1">Grado</label>
              <select v-model="form.grado_curso" class="w-full border px-4 py-2 rounded">
                <option disabled value="">Seleccione grado</option>
                <option v-for="g in grados" :key="g" :value="g">{{ g }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Paralelo</label>
              <select v-model="form.paralelo_curso" class="w-full border px-4 py-2 rounded">
                <option disabled value="">Seleccione paralelo</option>
                <option v-for="p in paralelos" :key="p" :value="p">{{ p }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Nivel educativo</label>
              <select v-model="form.nivel_educativo_id" class="w-full border px-4 py-2 rounded">
                <option disabled value="">Seleccione nivel</option>
                <option v-for="n in niveles" :key="n.id" :value="n.id">{{ n.nombre }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Aula</label>
              <input
                v-model="form.aula_curso"
                class="w-full border px-4 py-2 rounded"
                placeholder="Aula"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Turno</label>
              <select v-model="form.turno_curso" class="w-full border px-4 py-2 rounded">
                <option disabled value="">Seleccione turno</option>
                <option v-for="t in turnos" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Descripción</label>
              <input
                v-model="form.descripcion"
                class="w-full border px-4 py-2 rounded"
                placeholder="Descripción"
              />
            </div>
          </div>

          <div class="flex gap-4">
            <BaseButton label="Guardar cambios" color="success" @click="actualizarCurso" />
            <BaseButton label="Crear Horario" color="primary" @click="activarHorario" />
          </div>
          <div v-if="mensaje" class="text-green-600 mt-2">{{ mensaje }}</div>
          <div v-if="error" class="text-red-600 mt-2">{{ error }}</div>
        </div>

        <div v-else class="bg-white p-6 rounded shadow-md">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">
              Horario de {{ cursoSeleccionado.grado_curso }}
              {{ cursoSeleccionado.paralelo_curso }} – Nivel
              {{ cursoSeleccionado.nivel_educativo?.nombre }}
            </h2>
            <BaseButton label="Volver" color="secondary" @click="cancelar" />
          </div>

          <!-- Tabla tradicional editable -->
          <div class="overflow-auto mb-6">
            <table class="min-w-full text-sm border">
              <thead class="bg-gray-100">
                <tr>
                  <th class="border px-3 py-2">Hora</th>
                  <th v-for="dia in dias" :key="dia" class="border px-3 py-2">{{ dia }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="hora in horasOrdenadas" :key="hora">
                  <td class="border px-2 py-1 font-medium">{{ hora }}</td>
                  <td
                    v-for="dia in dias"
                    :key="dia + hora"
                    class="border px-2 py-1 text-center cursor-pointer hover:bg-blue-50"
                    @click="seleccionarBloqueGuardado(dia, hora)"
                  >
                    <pre class="whitespace-pre-wrap text-sm">{{
                      obtenerMateriaPorHorario(dia, hora) || '-'
                    }}</pre>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Formulario para agregar o editar bloque -->
          <div class="grid md:grid-cols-3 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium mb-1">Día</label>
              <select v-model="nuevoBloque.dia" class="w-full border px-3 py-2 rounded">
                <option disabled value="">Seleccione día</option>
                <option v-for="dia in dias" :key="dia" :value="dia">{{ dia }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Hora Inicio</label>
              <input
                type="time"
                step="60"
                v-model.lazy="nuevoBloque.hora_inicio"
                class="w-full border px-3 py-2 rounded"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Hora Fin</label>
              <input
                type="time"
                step="60"
                v-model.lazy="nuevoBloque.hora_fin"
                class="w-full border px-3 py-2 rounded"
              />
            </div>

            <div class="md:col-span-3">
              <label class="block text-sm font-medium mb-1">Materia</label>
              <select
                v-model="nuevoBloque.materias_id_materia"
                class="w-full border px-3 py-2 rounded"
              >
                <option disabled value="">Seleccione materia</option>
                <option v-for="m in materiasDisponibles" :key="m.id_materia" :value="m.id_materia">
                  {{ m.area_materia }} ({{ m.sigla_materia }})
                </option>
              </select>
            </div>
          </div>

          <!-- Botones dinámicos -->
          <div class="flex gap-4 mb-4">
            <BaseButton
              v-if="modoEditar"
              label="Actualizar bloque"
              color="info"
              @click="actualizarBloque"
            />
            <BaseButton
              v-if="modoEditar"
              label="Eliminar bloque"
              color="danger"
              @click="eliminarBloque"
            />
            <BaseButton
              v-if="!modoEditar"
              label="Agregar bloque"
              color="primary"
              @click="agregarBloque"
            />
          </div>

          <!-- Tabla temporal de nuevos bloques -->
          <div v-if="bloquesTemporales.length" class="overflow-auto border rounded mb-4">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2 text-left">Día</th>
                  <th class="px-4 py-2 text-left">Inicio</th>
                  <th class="px-4 py-2 text-left">Fin</th>
                  <th class="px-4 py-2 text-left">Materia</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(b, i) in bloquesTemporales" :key="i">
                  <td class="px-4 py-2">{{ b.dia }}</td>
                  <td class="px-4 py-2">{{ b.hora_inicio }}</td>
                  <td class="px-4 py-2">{{ b.hora_fin }}</td>
                  <td class="px-4 py-2">
                    {{
                      materiasDisponibles.find((m) => m.id_materia === b.materias_id_materia)
                        ?.area_materia
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex gap-4">
            <BaseButton label="Guardar Horario" color="success" @click="guardarHorario" />
            <BaseButton
              label="Limpiar"
              color="warning"
              @click="bloquesTemporales = []"
              v-if="bloquesTemporales.length"
            />
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'

const cursos = ref([])
const cursoSeleccionado = ref(null)
const mostrarHorario = ref(false)
const mensaje = ref('')
const error = ref('')
const busqueda = ref('')

const form = reactive({
  grado_curso: '',
  paralelo_curso: '',
  nivel_educativo_id: '',
  aula_curso: '',
  turno_curso: '',
  descripcion: '',
})

const grados = ['PRIMERO', 'SEGUNDO', 'TERCERO', 'CUARTO', 'QUINTO', 'SEXTO']
const paralelos = ['A', 'B', 'C', 'D']
const turnos = ['MAÑANA', 'TARDE', 'NOCHE']
const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']

const nuevoBloque = reactive({
  dia: '',
  hora_inicio: '',
  hora_fin: '',
  materias_id_materia: '',
})

const bloquesTemporales = ref([])
const bloquesGuardados = ref([])
const materiasDisponibles = ref([])
const gestionActiva = ref(null)

const horarioTabla = reactive({})
const horasOrdenadas = ref([])

const modoEditar = ref(false)
const idBloqueEditando = ref(null)

const cargarCursos = async () => {
  try {
    const res = await api.get('/cursos')
    cursos.value = res.data.data.map((curso) => ({
      ...curso,
      grado_curso: curso.grado_curso.toUpperCase(),
      paralelo_curso: curso.paralelo_curso.toUpperCase(),
      turno_curso: curso.turno_curso?.toUpperCase() || '',
    }))
  } catch {
    error.value = 'Error al cargar cursos'
  }
}

const cursosFiltrados = computed(() => {
  if (!busqueda.value) return cursos.value
  const q = busqueda.value.toUpperCase()
  return cursos.value.filter(
    (curso) =>
      curso.grado_curso.includes(q) ||
      curso.paralelo_curso.includes(q) ||
      curso.nivel_educativo?.nombre?.toUpperCase().includes(q),
  )
})

const seleccionarCurso = (curso) => {
  cursoSeleccionado.value = curso
  Object.assign(form, {
    grado_curso: curso.grado_curso,
    paralelo_curso: curso.paralelo_curso,
    nivel_educativo_id: curso.nivel_educativo_id,
    aula_curso: curso.aula_curso,
    turno_curso: curso.turno_curso,
    descripcion: curso.descripcion,
  })
  mensaje.value = ''
  error.value = ''
}

const actualizarCurso = async () => {
  if (!cursoSeleccionado.value) return
  try {
    const res = await api.put(`/cursos/${cursoSeleccionado.value.id_curso}`, form)
    mensaje.value = res.data.message
    error.value = ''
    await cargarCursos()
  } catch {
    error.value = 'Error al actualizar el curso'
    mensaje.value = ''
  }
}

const activarHorario = async () => {
  mostrarHorario.value = true
  await cargarGestionActiva()
  await cargarMateriasPorNivel(cursoSeleccionado.value.nivel_educativo_id)
  await cargarBloquesGuardados()
  construirHorario()
}

const cargarGestionActiva = async () => {
  try {
    const res = await api.get('/gestiones')
    gestionActiva.value = res.data.data.find((g) => g.estado_gestion === 'activa')
  } catch (err) {
    console.error('Error al obtener gestión activa:', err)
  }
}

const cargarMateriasPorNivel = async (nivel_id) => {
  try {
    const res = await api.get('/materias')
    materiasDisponibles.value = res.data.data.filter((m) => m.nivel_educativo_id === nivel_id)
  } catch (err) {
    console.error('Error al cargar materias:', err)
  }
}

const cargarBloquesGuardados = async () => {
  try {
    const res = await api.get(`/cursos/${cursoSeleccionado.value.id_curso}/horario`)

    // ✅ Mapeamos cada bloque para incluir 'id' desde 'id_horario'
    bloquesGuardados.value = res.data.data.map((b) => ({
      ...b,
      id: b.id_horario,
    }))

    console.log('Primer bloque completo:', bloquesGuardados.value[0])
    console.log(
      'IDs:',
      bloquesGuardados.value.map((b) => b.id),
    )
  } catch (err) {
    console.error('Error al cargar horario guardado:', err)
  }
}

const seleccionarBloqueGuardado = (dia, hora) => {
  const bloque = bloquesGuardados.value.find(
    (b) => b.dia === dia && `${b.hora_inicio} - ${b.hora_fin}` === hora,
  )
  if (bloque) {
    Object.assign(nuevoBloque, {
      dia: bloque.dia,
      hora_inicio: bloque.hora_inicio?.slice(0, 5), // ← elimina los segundos
      hora_fin: bloque.hora_fin?.slice(0, 5), // ← elimina los segundos
      materias_id_materia: bloque.materias_id_materia,
    })
    idBloqueEditando.value = bloque.id
    modoEditar.value = true
  }
}

const actualizarBloque = async () => {
  if (!idBloqueEditando.value) return
  try {
    const payload = {
      dia: nuevoBloque.dia,
      hora_inicio: nuevoBloque.hora_inicio,
      hora_fin: nuevoBloque.hora_fin,
      materias_id_materia: nuevoBloque.materias_id_materia,
      cursos_id_curso: cursoSeleccionado.value.id_curso,
      gestiones_id_gestion: gestionActiva.value.id_gestion,
    }
    console.log('🟢 Payload enviado al PUT:', payload)
    await api.put(`/horarios/${idBloqueEditando.value}`, payload)
    mensaje.value = 'Bloque actualizado correctamente.'
    modoEditar.value = false
    idBloqueEditando.value = null
    await cargarBloquesGuardados()
    construirHorario()
  } catch (err) {
    console.error('Error al actualizar bloque:', err)
    error.value = 'No se pudo actualizar el bloque.'
  }
}

const eliminarBloque = async () => {
  if (!idBloqueEditando.value) return
  try {
    await api.delete(`/horarios/${idBloqueEditando.value}`)
    mensaje.value = 'Bloque eliminado correctamente.'
    modoEditar.value = false
    idBloqueEditando.value = null
    Object.assign(nuevoBloque, { dia: '', hora_inicio: '', hora_fin: '', materias_id_materia: '' })
    await cargarBloquesGuardados()
    construirHorario()
  } catch (err) {
    console.error('Error al eliminar bloque:', err)
    error.value = 'No se pudo eliminar el bloque.'
  }
}

const agregarBloque = () => {
  if (
    !nuevoBloque.dia ||
    !nuevoBloque.hora_inicio ||
    !nuevoBloque.hora_fin ||
    !nuevoBloque.materias_id_materia
  ) {
    alert('Completa todos los campos antes de agregar.')
    return
  }
  bloquesTemporales.value.push({ ...nuevoBloque })
  Object.assign(nuevoBloque, { dia: '', hora_inicio: '', hora_fin: '', materias_id_materia: '' })
  construirHorario()
}

const construirHorario = () => {
  const mapa = {}
  const horasSet = new Set()
  const todosLosBloques = [...bloquesGuardados.value, ...bloquesTemporales.value]
  todosLosBloques.forEach((b) => {
    const hora = `${b.hora_inicio} - ${b.hora_fin}`
    horasSet.add(hora)
    if (!mapa[b.dia]) mapa[b.dia] = {}
    const sigla =
      b.materia?.sigla_materia ||
      materiasDisponibles.value.find((m) => m.id_materia === b.materias_id_materia)
        ?.sigla_materia ||
      'MAT'
    const nombre =
      b.materia?.area_materia ||
      materiasDisponibles.value.find((m) => m.id_materia === b.materias_id_materia)?.area_materia ||
      ''
    mapa[b.dia][hora] = `${sigla}\n${nombre}`
  })
  horarioTabla.value = mapa
  horasOrdenadas.value = Array.from(horasSet).sort()
}

const obtenerMateriaPorHorario = (dia, hora) => {
  return horarioTabla.value?.[dia]?.[hora] || ''
}

const guardarHorario = async () => {
  if (!cursoSeleccionado.value || !gestionActiva.value) return
  try {
    const payload = bloquesTemporales.value.map((b) => ({
      cursos_id_curso: cursoSeleccionado.value.id_curso,
      gestiones_id_gestion: gestionActiva.value.id_gestion,
      materias_id_materia: b.materias_id_materia,
      dia: b.dia,
      hora_inicio: b.hora_inicio,
      hora_fin: b.hora_fin,
    }))
    for (const bloque of payload) {
      await api.post('/horarios', bloque)
    }
    mensaje.value = 'Horario guardado exitosamente.'
    bloquesTemporales.value = []
    await cargarBloquesGuardados()
    construirHorario()
  } catch (err) {
    console.error('Error al guardar horario:', err)
    error.value = 'No se pudo guardar el horario.'
  }
}

const cancelar = () => {
  cursoSeleccionado.value = null
  mostrarHorario.value = false
  mensaje.value = ''
  error.value = ''
  bloquesTemporales.value = []
  bloquesGuardados.value = []
  horarioTabla.value = {}
  modoEditar.value = false
  idBloqueEditando.value = null
}

const niveles = computed(() => {
  const mapa = new Map()
  cursos.value.forEach((curso) => {
    const nivel = curso.nivel_educativo
    if (nivel && !mapa.has(nivel.id)) {
      mapa.set(nivel.id, nivel)
    }
  })
  return Array.from(mapa.values())
})

onMounted(() => {
  cargarCursos()
})
</script>
