<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'

const estudiantes = ref([])
const cursos = ref([])
const gestionActiva = ref(null)
const estudianteSeleccionado = ref(null)
const mensaje = ref('')
const error = ref('')
const busqueda = ref('')
const mostrarFormularioNuevo = ref(false)

// 1. Estado del modal de confirmación
const mostrarModalInscribir = ref(false)

// 2. Función que confirma e invoca la inscripción
const confirmarInscribir = () => {
  mostrarModalInscribir.value = false
  inscribir()
}

// 1. Estado que indica si toca fallo (true) o éxito (false)
const falloInscripcion = ref(false)

// 2. Método que decide aleatoriamente y abre el modal
const abrirModalInscribir = () => {
  // 50% de probabilidad
  falloInscripcion.value = Math.random() < 0.5
  mostrarModalInscribir.value = true
}

const modalConfirmacion = ref(false)
const datosUsuarioGenerado = ref({ usuario: '', password: 'admin123' })

const form = reactive({
  estudiante_id: '',
  curso_id: '',
})

const nuevoEstudiante = ref({
  persona: {
    nombres_persona: '',
    apellidos_pat: '',
    apellidos_mat: '',
    sexo_persona: '',
    fecha_nacimiento: '',
    direccion_persona: '',
    nacionalidad_persona: '',
    celular_persona: '',
    fotografia_persona: null, // ✅ archivo
  },
  documento: {
    carnet_identidad: '',
    certificado_nacimiento: '',
  },
  estudiante: {
    rude: '',
    libreta_anterior: '',
    obsev_estudiante: '',
  },
  inscripcion: {
    cursos_id_curso: '',
    gestiones_id_gestion: '',
  },
})

const mensajeNuevo = ref('')
const errorNuevo = ref('')

const cargarDatos = async () => {
  try {
    const resEstudiantes = await api.get('/info/estudiantes')
    const resCursos = await api.get('/cursos')
    const resGestiones = await api.get('/gestiones')

    gestionActiva.value = resGestiones.data.data.find((g) => g.estado_gestion === 'activa')

    estudiantes.value = resEstudiantes.data.estudiantes.filter((est) => {
      return !est.cursos.some(
        (c) =>
          c.gestiones_id_gestion === gestionActiva.value?.id_gestion && c.estado === 'inscrito',
      )
    })

    cursos.value = resCursos.data.data
  } catch (err) {
    console.error(err)
    error.value = 'Error al cargar datos'
  }
}

onMounted(cargarDatos)

const seleccionarEstudiante = (est) => {
  estudianteSeleccionado.value = est
  form.estudiante_id = est.id_estudiante
  form.curso_id = ''
  mensaje.value = ''
  error.value = ''
}

const inscribir = async () => {
  if (!form.estudiante_id || !form.curso_id || !gestionActiva.value) {
    error.value = 'Completa todos los campos antes de inscribir.'
    return
  }

  try {
    const payload = {
      estudiantes_id_estudiante: form.estudiante_id,
      cursos_id_curso: form.curso_id,
      gestiones_id_gestion: gestionActiva.value.id_gestion,
    }

    const res = await api.post('/inscribir-curso', payload)
    mensaje.value = res.data.message
    error.value = ''
    estudianteSeleccionado.value = null
    cargarDatos()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Error al inscribir estudiante'
    mensaje.value = ''
  }
}

const estudiantesFiltrados = computed(() => {
  if (!busqueda.value) return estudiantes.value

  const query = busqueda.value.toLowerCase()
  return estudiantes.value.filter((est) => {
    const nombres = est.persona_rol.persona.nombres_persona.toLowerCase()
    const apPat = est.persona_rol.persona.apellidos_pat.toLowerCase()
    const apMat = est.persona_rol.persona.apellidos_mat.toLowerCase()
    return nombres.includes(query) || apPat.includes(query) || apMat.includes(query)
  })
})

const registrarNuevoEstudiante = async () => {
  if (!gestionActiva.value) {
    errorNuevo.value = 'No hay gestión activa definida.'
    return
  }

  const estudianteParaEnviar = nuevoEstudiante.value // ✅ Mantener la referencia original

  // Asignar gestión si se elige curso
  if (estudianteParaEnviar.inscripcion.cursos_id_curso) {
    estudianteParaEnviar.inscripcion.gestiones_id_gestion = gestionActiva.value.id_gestion
  } else {
    estudianteParaEnviar.inscripcion = {}
  }

  try {
    const formData = new FormData()

    // 🧪 Verificamos el tipo de fotografía
    console.log('Tipo de fotografia_persona:', estudianteParaEnviar.persona.fotografia_persona)

    // Añadir campos de persona
    for (const [key, value] of Object.entries(estudianteParaEnviar.persona)) {
      // Solo agregamos si tiene valor (evita errores con "undefined")
      if (value !== undefined && value !== null) {
        formData.append(`persona[${key}]`, value)
      }
    }

    // Documento
    for (const [key, value] of Object.entries(estudianteParaEnviar.documento)) {
      formData.append(`documento[${key}]`, value)
    }

    // Estudiante
    for (const [key, value] of Object.entries(estudianteParaEnviar.estudiante)) {
      formData.append(`estudiante[${key}]`, value)
    }

    // Inscripción (solo si se llenó)
    for (const [key, value] of Object.entries(estudianteParaEnviar.inscripcion)) {
      if (value) {
        formData.append(`inscripcion[${key}]`, value)
      }
    }

    // 📤 Enviar a backend
    const res = await api.post('/inscribir-estudiante', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    mensajeNuevo.value = res.data.message
    errorNuevo.value = ''
    mostrarFormularioNuevo.value = false
    cargarDatos()

    // Mostrar modal con usuario generado
    const nombres = nuevoEstudiante.value.persona.nombres_persona.trim().split(' ')[0] || ''
    const apellido = nuevoEstudiante.value.persona.apellidos_pat.trim() || ''
    datosUsuarioGenerado.value.usuario = `${nombres}${apellido}`.toLowerCase()
    modalConfirmacion.value = true
    resetNuevoEstudiante()
  } catch (err) {
    console.error(err)
    console.log(err.response?.data)
    errorNuevo.value = err.response?.data?.message || 'Error al registrar estudiante'
    mensajeNuevo.value = ''
  }
}

const resetNuevoEstudiante = () => {
  nuevoEstudiante.value = {
    persona: {
      nombres_persona: '',
      apellidos_pat: '',
      apellidos_mat: '',
      sexo_persona: '',
      fecha_nacimiento: '',
      direccion_persona: '',
      nacionalidad_persona: '',
      celular_persona: '',
      fotografia_persona: null, // ✅ reiniciar correctamente
    },
    documento: {
      carnet_identidad: '',
      certificado_nacimiento: '',
    },
    estudiante: {
      rude: '',
      libreta_anterior: '',
      obsev_estudiante: '',
    },
    inscripcion: {
      cursos_id_curso: '',
      gestiones_id_gestion: '',
    },
  }
}

const onFotoChange = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    nuevoEstudiante.value.persona.fotografia_persona = file
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Inscripción de Estudiantes</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1 -->
        <div class="bg-white p-6 rounded shadow-md">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">
              {{ mostrarFormularioNuevo ? 'Registrar nuevo estudiante' : 'Estudiantes sin curso' }}
            </h2>
            <BaseButton
              :label="mostrarFormularioNuevo ? 'Ver lista' : 'Registrar nuevo estudiante'"
              color="info"
              size="sm"
              @click="
                () => {
                  mostrarFormularioNuevo = !mostrarFormularioNuevo
                  estudianteSeleccionado = null
                  form.estudiante_id = ''
                  form.curso_id = ''
                  //mensaje.value = ''
                  //error.value = ''
                }
              "
            />
          </div>

          <template v-if="!mostrarFormularioNuevo">
            <input
              type="text"
              v-model="busqueda"
              placeholder="Buscar por nombre o apellido"
              class="w-full mb-4 px-4 py-2 border rounded shadow-sm"
            />

            <div v-if="estudiantesFiltrados.length === 0" class="text-gray-500">
              No hay estudiantes encontrados
            </div>

            <div class="overflow-auto max-h-[500px] border rounded">
              <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-100 sticky top-0">
                  <tr>
                    <th class="px-4 py-2 font-medium text-gray-600">Nombre completo</th>
                    <th class="px-4 py-2 font-medium text-gray-600">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="est in estudiantesFiltrados"
                    :key="est.id_estudiante"
                    @click="seleccionarEstudiante(est)"
                    :class="[
                      'cursor-pointer border-b',
                      estudianteSeleccionado?.id_estudiante === est.id_estudiante
                        ? 'bg-green-100 font-semibold'
                        : 'hover:bg-gray-100',
                    ]"
                  >
                    <td class="px-4 py-2">
                      {{ est.persona_rol.persona.nombres_persona }}
                      {{ est.persona_rol.persona.apellidos_pat }}
                      {{ est.persona_rol.persona.apellidos_mat }}
                    </td>
                    <td class="px-4 py-2 capitalize">
                      {{ est.cursos[0]?.estado || 'No inscrito' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>

          <template v-else>
            <form @submit.prevent="registrarNuevoEstudiante" class="space-y-4">
              <!-- Persona -->
              <div class="grid md:grid-cols-2 gap-4">
                <input
                  v-model="nuevoEstudiante.persona.nombres_persona"
                  type="text"
                  placeholder="Nombres"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <input
                  v-model="nuevoEstudiante.persona.apellidos_pat"
                  type="text"
                  placeholder="Apellido paterno"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <input
                  v-model="nuevoEstudiante.persona.apellidos_mat"
                  type="text"
                  placeholder="Apellido materno"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <select
                  v-model="nuevoEstudiante.persona.sexo_persona"
                  class="w-full px-4 py-2 border rounded"
                  required
                >
                  <option value="">Seleccionar sexo</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                </select>

                <input
                  v-model="nuevoEstudiante.persona.fecha_nacimiento"
                  type="date"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <input
                  v-model="nuevoEstudiante.persona.direccion_persona"
                  type="text"
                  placeholder="Dirección"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <input
                  v-model="nuevoEstudiante.persona.nacionalidad_persona"
                  type="text"
                  placeholder="Nacionalidad"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <input
                  v-model="nuevoEstudiante.persona.celular_persona"
                  type="text"
                  placeholder="Celular"
                  class="w-full px-4 py-2 border rounded"
                />
                <input
                  type="file"
                  accept="image/*"
                  @change="onFotoChange"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>

              <!-- Documento -->
              <div class="grid md:grid-cols-2 gap-4">
                <input
                  v-model="nuevoEstudiante.documento.carnet_identidad"
                  type="text"
                  placeholder="Carnet de Identidad"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <input
                  v-model="nuevoEstudiante.documento.certificado_nacimiento"
                  type="text"
                  placeholder="Certificado de Nacimiento"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
              </div>

              <!-- Estudiante -->
              <div class="grid md:grid-cols-2 gap-4">
                <input
                  v-model="nuevoEstudiante.estudiante.rude"
                  type="text"
                  placeholder="RUDE"
                  class="w-full px-4 py-2 border rounded"
                  required
                />
                <input
                  v-model="nuevoEstudiante.estudiante.libreta_anterior"
                  type="text"
                  placeholder="Libreta anterior"
                  class="w-full px-4 py-2 border rounded"
                />
                <input
                  v-model="nuevoEstudiante.estudiante.obsev_estudiante"
                  type="text"
                  placeholder="Observaciones"
                  class="w-full px-4 py-2 border rounded"
                />
              </div>

              <!-- Curso y gestión -->
              <div class="grid md:grid-cols-2 gap-4">
                <select
                  v-model="nuevoEstudiante.inscripcion.cursos_id_curso"
                  class="w-full px-4 py-2 border rounded"
                >
                  <option value="">Selecciona curso (opcional)</option>
                  <option v-for="curso in cursos" :key="curso.id_curso" :value="curso.id_curso">
                    {{ curso.grado_curso }} {{ curso.paralelo_curso }} -
                    {{ curso.nivel_educativo.codigo }}
                  </option>
                </select>
                <div class="w-full px-4 py-2 border rounded bg-gray-100 text-sm">
                  <span>Gestión:</span>
                  <strong>{{ gestionActiva?.nombre_gestion || 'No disponible' }}</strong>
                </div>
              </div>

              <div class="flex gap-4">
                <BaseButton type="submit" label="Registrar" color="success" />
                <BaseButton
                  type="button"
                  label="Cancelar"
                  color="secondary"
                  @click="mostrarFormularioNuevo = false"
                />
              </div>

              <div v-if="mensajeNuevo" class="text-green-600">{{ mensajeNuevo }}</div>
              <div v-if="errorNuevo" class="text-red-600">{{ errorNuevo }}</div>
            </form>
          </template>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Asignar curso</h2>

          <div v-if="!estudianteSeleccionado" class="text-gray-500">
            Selecciona un estudiante para continuar.
          </div>

          <div v-else>
            <div class="mb-4">
              <p>
                <strong>Nombre:</strong>
                {{ estudianteSeleccionado.persona_rol.persona.nombres_persona }}
                {{ estudianteSeleccionado.persona_rol.persona.apellidos_pat }}
                {{ estudianteSeleccionado.persona_rol.persona.apellidos_mat }}
              </p>
              <p>
                <strong>CI:</strong>
                {{
                  estudianteSeleccionado.persona_rol.persona.documento?.carnet_identidad || '---'
                }}
              </p>
              <p>
                <strong>Estado:</strong>
                {{ estudianteSeleccionado.cursos[0]?.estado || 'no_inscrito' }}
              </p>
            </div>

            <div class="mb-4">
              <label class="block font-medium mb-1">Selecciona curso</label>
              <select v-model="form.curso_id" class="w-full border px-4 py-2 rounded shadow-sm">
                <option value="">Seleccionar</option>
                <option v-for="curso in cursos" :key="curso.id_curso" :value="curso.id_curso">
                  {{ curso.grado_curso }} {{ curso.paralelo_curso }} -
                  {{ curso.nivel_educativo.codigo }}
                </option>
              </select>
            </div>

            <div v-if="gestionActiva" class="mb-4 text-sm text-gray-600">
              Gestión activa: <strong>{{ gestionActiva.nombre_gestion }}</strong>
            </div>

            <div class="flex gap-4">
              <BaseButton color="success" label="Inscribir" @click="abrirModalInscribir" />

              <BaseButton
                color="info"
                outline
                label="Cancelar"
                @click="estudianteSeleccionado = null"
              />
            </div>

            <div v-if="mensaje" class="mt-4 text-green-600">{{ mensaje }}</div>
            <div v-if="error" class="mt-4 text-red-600">{{ error }}</div>
          </div>
        </div>
      </div>
    </div>
    <!-- MODAL CONFIRMACIÓN -->
    <CardBoxModal
      v-model="modalConfirmacion"
      title="ALUMNO CREADO EXITOSAMENTE"
      :hide-footer="true"
    >
      <p class="mb-2">
        Usuario generado: <strong>{{ datosUsuarioGenerado.usuario }}</strong>
      </p>
      <p>
        Contraseña predeterminada: <strong>{{ datosUsuarioGenerado.password }}</strong>
      </p>
      <div class="mt-4 flex justify-end">
        <BaseButton color="primary" label="Aceptar" @click="modalConfirmacion = false" />
      </div>
    </CardBoxModal>
    <!-- Modal: Confirmar inscripción -->
    <div
      v-if="mostrarModalInscribir"
      class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.3)] backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full space-y-4">
        <!-- Variante Éxito -->
        <template v-if="!falloInscripcion">
          <h2 class="text-lg font-bold text-blue-700">
            Este alumno aprobó la gestión anterior, ¿desea inscribirlo?
          </h2>
          <div class="flex justify-end gap-2 mt-4">
            <button
              @click="mostrarModalInscribir = false"
              class="px-4 py-2 text-sm text-gray-700 border rounded hover:bg-gray-100"
            >
              Cancelar
            </button>
            <button
              @click="confirmarInscribir"
              class="px-4 py-2 text-sm text-white bg-blue-600 rounded"
            >
              Aceptar
            </button>
          </div>
        </template>

        <!-- Variante Fallo -->
        <template v-else>
          <h2 class="text-lg font-bold text-red-600">
            Este estudiante NO aprobó la gestión anterior, no puede inscribirse al curso actual.
          </h2>
          <div class="flex justify-end mt-4">
            <button
              @click="mostrarModalInscribir = false"
              class="px-4 py-2 text-sm text-white bg-red-600 rounded"
            >
              Cerrar
            </button>
          </div>
        </template>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
