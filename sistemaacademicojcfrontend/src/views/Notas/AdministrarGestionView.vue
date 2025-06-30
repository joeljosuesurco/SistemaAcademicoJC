<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'

const nuevaGestion = ref('')
const password = ref('')
const feedback = ref(null)
const loading = ref(false)

const activarNuevaGestion = async () => {
  if (!nuevaGestion.value || !password.value) {
    feedback.value = 'Completa todos los campos.'
    return
  }
  loading.value = true
  feedback.value = null
  try {
    const res = await api.post('/gestiones/administrar/cambiar', {
      nueva_gestion: nuevaGestion.value,
      password: password.value,
    })
    feedback.value = 'Nueva gestión creada correctamente.'
    nuevaGestion.value = ''
    password.value = ''
  } catch (err) {
    if (err.response?.status === 403) {
      feedback.value = 'Contraseña incorrecta.'
    } else {
      feedback.value = 'Ocurrió un error al cambiar la gestión.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Administrar Gestión Académica</h1>

      <div class="bg-white rounded-xl shadow p-6 max-w-xl">
        <p class="mb-4 text-gray-700">
          Esta acción cerrará la gestión actual y activará una nueva. Se requiere contraseña de
          administrador.
        </p>

        <div class="mb-4">
          <label class="block font-medium mb-1">Nueva gestión (ej. 2026)</label>
          <input
            type="number"
            v-model="nuevaGestion"
            placeholder="Año de la nueva gestión"
            class="w-full p-3 border rounded"
          />
        </div>

        <div class="mb-4">
          <label class="block font-medium mb-1">Contraseña de administrador</label>
          <input
            type="password"
            v-model="password"
            placeholder="********"
            class="w-full p-3 border rounded"
          />
        </div>

        <BaseButton
          :label="loading ? 'Procesando...' : 'Crear nueva gestión'"
          :disabled="loading"
          color="blue"
          @click="activarNuevaGestion"
        />

        <div v-if="feedback" class="mt-4 text-sm text-red-600">{{ feedback }}</div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
