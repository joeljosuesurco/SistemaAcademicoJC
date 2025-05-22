<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseButton from '@/components/BaseButton.vue'
import FormField from '@/components/FormField.vue'
import FormControl from '@/components/FormControl.vue'
import api from '@/services/api'
import { useMainStore } from '@/stores/main'

const router = useRouter()
const mainStore = useMainStore()

const form = ref({
  titulo_notificacion: '',
  mensaje_notificacion: '',
  fecha_notificacion: '',
  estado_notificacion: 'activo',
  tipo_notificacion: 'publico',
  grupo_destino: 'todos', // nuevo campo
})

const mensaje = ref('')
const error = ref('')

const enviarNotificacion = async () => {
  try {
    const userId = mainStore.userId
    if (!userId) throw new Error('Usuario no identificado')

    // 1. Crear la notificación
    const res = await api.post('/notificaciones', {
      ...form.value,
      users_id_user: userId,
    })

    const notificacionId = res.data.data.id_notificacion

    // 2. Obtener los destinatarios según grupo
    const grupo = form.value.grupo_destino
    const resUsuarios = await api.get(`/usuarios-por-grupo/${grupo}`)
    const ids = resUsuarios.data.data.map((u) => u.id_user)

    // 3. Asignar la notificación
    await api.post('/asignar-notificacion', {
      notificaciones_id_notificacion: notificacionId,
      usuarios: ids,
    })

    // 4. Actualizar badge
    await mainStore.fetchComunicadosVisibles()

    mensaje.value = 'Notificación enviada correctamente.'
    error.value = ''
    router.push('/comunicados')
  } catch (err) {
    mensaje.value = ''
    error.value = err.response?.data?.message || err.message
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton title="Nuevo Comunicado" />

      <CardBox>
        <form @submit.prevent="enviarNotificacion" class="space-y-4">
          <FormField label="Título">
            <FormControl v-model="form.titulo_notificacion" type="text" required />
          </FormField>

          <FormField label="Mensaje">
            <FormControl v-model="form.mensaje_notificacion" type="textarea" required />
          </FormField>

          <FormField label="Fecha de Publicación">
            <FormControl v-model="form.fecha_notificacion" type="date" required />
          </FormField>

          <FormField label="Estado">
            <select
              v-model="form.estado_notificacion"
              class="form-select w-full p-2 rounded-md border"
            >
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </FormField>

          <FormField label="Tipo">
            <select
              v-model="form.tipo_notificacion"
              class="form-select w-full p-2 rounded-md border"
            >
              <option value="publico">Público</option>
              <option value="privado">Privado</option>
            </select>
          </FormField>

          <FormField label="Enviar a">
            <select
              v-model="form.grupo_destino"
              class="form-select w-full p-2 rounded-md border"
              required
            >
              <option value="todos">Todos</option>
              <option value="estudiantes">Estudiantes</option>
              <option value="profesores">Profesores</option>
            </select>
          </FormField>

          <BaseButton type="submit" color="info" label="Enviar Comunicado" />

          <p v-if="mensaje" class="text-green-600 mt-2">{{ mensaje }}</p>
          <p v-if="error" class="text-red-600 mt-2">{{ error }}</p>
        </form>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
