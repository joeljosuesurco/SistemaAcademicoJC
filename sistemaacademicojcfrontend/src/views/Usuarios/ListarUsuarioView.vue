<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'

const usuarios = ref([])
const rolesDisponibles = ref([])
const loading = ref(true)
const error = ref(null)
const search = ref('')
const usuarioSeleccionado = ref(null)
const mostrandoEdicion = ref(false)
const detalleRef = ref(null)
const modalPassword = ref(false)
const modalMensaje = ref(false)
const mensajeModal = ref('')

const currentPage = ref(1)
const pageSize = 10

const nuevaPassword = ref('')
let copiaPasswordOriginal = ''

watch(search, () => {
  usuarioSeleccionado.value = null
  mostrandoEdicion.value = false
})

async function fetchUsuarios() {
  try {
    const res = await api.get('/users')
    usuarios.value = res.data.data.sort((a, b) => a.name_user.localeCompare(b.name_user))
  } catch (err) {
    error.value = 'No se pudo cargar la lista de usuarios'
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchUsuarios()
  try {
    const res = await api.get('/roles')
    rolesDisponibles.value = res.data.data
  } catch (e) {
    console.error('Error cargando roles:', e)
  }
})

const usuariosFiltrados = computed(() => {
  const filtrados = search.value
    ? usuarios.value.filter((u) => {
        const p = u.persona_rol?.persona || {}
        const nombres = p.nombres_persona?.toLowerCase() || ''
        const apPat = p.apellidos_pat?.toLowerCase() || ''
        const apMat = p.apellidos_mat?.toLowerCase() || ''
        return (
          nombres.includes(search.value.toLowerCase()) ||
          apPat.includes(search.value.toLowerCase()) ||
          apMat.includes(search.value.toLowerCase())
        )
      })
    : usuarios.value

  const start = (currentPage.value - 1) * pageSize
  return filtrados.slice(start, start + pageSize)
})

const totalPages = computed(() => {
  const total = search.value
    ? usuarios.value.filter((u) => {
        const p = u.persona_rol?.persona || {}
        const nombres = p.nombres_persona?.toLowerCase() || ''
        const apPat = p.apellidos_pat?.toLowerCase() || ''
        const apMat = p.apellidos_mat?.toLowerCase() || ''
        return (
          nombres.includes(search.value.toLowerCase()) ||
          apPat.includes(search.value.toLowerCase()) ||
          apMat.includes(search.value.toLowerCase())
        )
      }).length
    : usuarios.value.length
  return Math.ceil(total / pageSize)
})

function verUsuario(usuario) {
  usuarioSeleccionado.value = { ...usuario }
  mostrandoEdicion.value = false
  nextTick(() => detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }))
}

function editarUsuario(usuario) {
  usuarioSeleccionado.value = { ...usuario }
  copiaPasswordOriginal = usuario.password
  mostrandoEdicion.value = true
  nextTick(() => detalleRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }))
}

function intentarGuardar() {
  if (usuarioSeleccionado.value.password !== copiaPasswordOriginal) {
    nuevaPassword.value = usuarioSeleccionado.value.password
    modalPassword.value = true
  } else {
    guardarCambios()
  }
}

async function guardarCambios() {
  try {
    const userPayload = {
      name_user: usuarioSeleccionado.value.name_user,
      password: usuarioSeleccionado.value.password,
      state_user: usuarioSeleccionado.value.state_user,
      persona_rol_id_persona_rol: usuarioSeleccionado.value.persona_rol_id_persona_rol,
    }

    await api.put(`/users/${usuarioSeleccionado.value.id_user}`, userPayload)

    await api.put(`/persona-roles/${usuarioSeleccionado.value.persona_rol.id_persona_rol}`, {
      roles_id_rol: usuarioSeleccionado.value.persona_rol.rol.id_rol,
      personas_id_persona: usuarioSeleccionado.value.persona_rol.persona.id_persona,
    })

    await fetchUsuarios()
    mostrandoEdicion.value = false
    modalMensaje.value = true
    mensajeModal.value = 'Cambios guardados correctamente'
    modalPassword.value = false
  } catch (err) {
    console.error('Error al actualizar usuario:', err)
    if (err.response && err.response.status === 422) {
      const errores = err.response.data.errors
      if (errores?.name_user?.some((msg) => msg.includes('has already been taken'))) {
        mensajeModal.value = 'El nombre de usuario ya está en uso. Por favor, elige otro.'
      } else {
        mensajeModal.value = Object.values(errores).flat().join(', ')
      }
    } else {
      mensajeModal.value = 'Error al actualizar usuario.'
    }
    modalMensaje.value = true
    modalPassword.value = false
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-xl font-bold mb-4">Listado de Usuarios</h2>

      <div class="mb-4 flex flex-col md:flex-row md:items-center md:gap-4">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nombre o apellidos..."
          class="w-full md:w-1/3 px-4 py-2 border rounded shadow-sm"
        />
      </div>

      <div v-if="loading" class="text-gray-500">Cargando usuarios...</div>
      <div v-else-if="error" class="text-red-600">{{ error }}</div>

      <table v-else class="w-full text-left bg-white rounded shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">Nombre Completo</th>
            <th class="p-2">Rol</th>
            <th class="p-2">Estado</th>
            <th class="p-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="usuario in usuariosFiltrados" :key="usuario.id_user">
            <td class="p-2">
              {{ usuario.persona_rol?.persona?.apellidos_pat || '' }}
              {{ usuario.persona_rol?.persona?.apellidos_mat || '' }}
              {{ usuario.persona_rol?.persona?.nombres_persona || '' }}
            </td>
            <td class="p-2">{{ usuario.persona_rol?.rol?.nombre || '—' }}</td>
            <td class="p-2 capitalize">{{ usuario.state_user }}</td>
            <td class="p-2 space-x-2">
              <BaseButton color="info" label="Ver" small @click="verUsuario(usuario)" />
              <BaseButton color="warning" label="Editar" small @click="editarUsuario(usuario)" />
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

      <div
        v-if="usuarioSeleccionado"
        ref="detalleRef"
        class="mt-6 grid md:grid-cols-2 gap-6 text-base"
      >
        <div class="border border-blue-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-xl font-semibold mb-4 text-blue-800">Detalles del Usuario</h3>
          <p><strong>Nombre de Usuario:</strong> {{ usuarioSeleccionado.name_user }}</p>
          <p><strong>Estado:</strong> {{ usuarioSeleccionado.state_user }}</p>
          <p><strong>Rol:</strong> {{ usuarioSeleccionado.persona_rol?.rol?.nombre || '—' }}</p>

          <h4 class="mt-4 font-semibold text-blue-700">Datos personales:</h4>
          <p>
            {{ usuarioSeleccionado.persona_rol?.persona?.apellidos_pat }}
            {{ usuarioSeleccionado.persona_rol?.persona?.apellidos_mat }}
            {{ usuarioSeleccionado.persona_rol?.persona?.nombres_persona }}
          </p>
        </div>

        <div
          v-if="mostrandoEdicion"
          class="border border-yellow-300 p-6 rounded shadow-xl bg-white"
        >
          <h3 class="text-xl font-semibold mb-4 text-yellow-800">Editar Usuario</h3>
          <form class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Nombre de Usuario</label>
              <input
                v-model="usuarioSeleccionado.name_user"
                class="px-4 py-2 border rounded w-full"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Estado</label>
              <select
                v-model="usuarioSeleccionado.state_user"
                class="px-4 py-2 border rounded w-full"
              >
                <option value="activo">activo</option>
                <option value="inactivo">inactivo</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Contraseña</label>
              <input
                type="password"
                v-model="usuarioSeleccionado.password"
                class="px-4 py-2 border rounded w-full"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Rol</label>
              <select
                v-model="usuarioSeleccionado.persona_rol.rol.id_rol"
                class="px-4 py-2 border rounded w-full"
              >
                <option v-for="rol in rolesDisponibles" :key="rol.id_rol" :value="rol.id_rol">
                  {{ rol.nombre }}
                </option>
              </select>
            </div>

            <div class="flex gap-2">
              <BaseButton
                label="Guardar cambios"
                color="success"
                @click.prevent="intentarGuardar"
              />
              <BaseButton label="Cancelar" color="secondary" @click="usuarioSeleccionado = null" />
            </div>
          </form>
        </div>
      </div>

      <CardBoxModal v-model="modalPassword" title="Nueva Contraseña" :hide-footer="true">
        <p class="mb-4">
          Esta será la nueva contraseña:
          <span class="font-bold text-black">{{ nuevaPassword }}</span>
        </p>
        <div class="flex justify-end gap-2">
          <BaseButton color="secondary" label="Volver" @click="modalPassword = false" />
          <BaseButton color="success" label="Confirmar y guardar" @click="guardarCambios" />
        </div>
      </CardBoxModal>

      <CardBoxModal v-model="modalMensaje" title="Confirmación">
        <p>{{ mensajeModal }}</p>
      </CardBoxModal>
    </div>
  </LayoutAuthenticated>
</template>
