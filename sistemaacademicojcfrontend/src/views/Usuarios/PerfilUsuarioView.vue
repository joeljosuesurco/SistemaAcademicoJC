<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'

const perfil = ref(null)
const mensaje = ref('')
const modalMensaje = ref(false)
const loading = ref(true)

const form = ref({
  name_user: '',
  password: '',
})

onMounted(async () => {
  try {
    const res = await api.get('/perfil-detallado')
    perfil.value = res.data.data
    form.value = {
      name_user: perfil.value.name_user,
      password: '**************',
    }
  } catch (err) {
    console.error('Error cargando perfil:', err)
  } finally {
    loading.value = false
  }
})

async function guardarCambios() {
  try {
    const payload = {
      name_user: form.value.name_user,
    }
    if (form.value.password !== '**************') {
      payload.password = form.value.password
    }

    const res = await api.put('/perfil', payload)
    perfil.value.name_user = res.data.data.name_user
    mensaje.value = 'Usuario actualizado correctamente.'
  } catch (err) {
    if (err.response?.status === 422) {
      const errores = err.response.data.errors
      mensaje.value = Object.values(errores).flat().join(', ')
    } else {
      mensaje.value = 'Error al actualizar el usuario.'
    }
    console.error(err)
  } finally {
    modalMensaje.value = true
  }
}

function cancelarEdicion() {
  if (!perfil.value) return
  form.value = {
    name_user: perfil.value.name_user,
    password: '**************',
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-xl font-bold mb-4">Mi Perfil</h2>

      <div v-if="loading" class="text-gray-500">Cargando datos...</div>

      <div v-else class="grid md:grid-cols-2 gap-6">
        <div class="border border-blue-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-lg font-semibold text-blue-800 mb-4">Detalles del Usuario</h3>
          <p><strong>Nombre de Usuario:</strong> {{ perfil.name_user }}</p>
          <p><strong>Estado:</strong> {{ perfil.state_user }}</p>
          <p><strong>Rol:</strong> {{ perfil.persona_rol?.rol?.nombre }}</p>
          <p class="mt-4 text-blue-700 font-semibold">Datos personales:</p>
          <ul class="list-disc ml-6 mt-2 text-gray-800">
            <li>
              <strong>Nombre completo:</strong> {{ perfil.persona_rol?.persona?.apellidos_pat }}
              {{ perfil.persona_rol?.persona?.apellidos_mat }}
              {{ perfil.persona_rol?.persona?.nombres_persona }}
            </li>
          </ul>
        </div>

        <div class="border border-yellow-300 p-6 rounded shadow-xl bg-white">
          <h3 class="text-lg font-semibold text-yellow-800 mb-4">Editar Usuario</h3>
          <form class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Nombre de Usuario</label>
              <input v-model="form.name_user" class="w-full px-4 py-2 border rounded" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Contraseña</label>
              <input
                type="password"
                v-model="form.password"
                class="w-full px-4 py-2 border rounded"
              />
            </div>

            <div class="flex gap-2">
              <BaseButton label="Guardar cambios" color="success" @click.prevent="guardarCambios" />
              <BaseButton label="Cancelar" color="secondary" @click="cancelarEdicion" />
            </div>
          </form>
        </div>
      </div>

      <CardBoxModal v-model="modalMensaje" title="Información">
        <p>{{ mensaje }}</p>
      </CardBoxModal>
    </div>
  </LayoutAuthenticated>
</template>
