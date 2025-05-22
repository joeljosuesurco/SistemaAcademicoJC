<script setup>
import NavBarItem from '@/components/NavBarItem.vue'
import { useMainStore } from '@/stores/main'

const mainStore = useMainStore()

defineProps({
  menu: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['menu-click'])

const menuClick = async (event, item) => {
  // Si el usuario hace clic en "Comunicados", actualizamos y marcamos como vistos
  if (item.label === 'Comunicados') {
    await mainStore.cargarYMarcarNotificacionesRecientes()
  }

  emit('menu-click', event, item)
}
</script>

<template>
  <NavBarItem v-for="(item, index) in menu" :key="index" :item="item" @menu-click="menuClick" />
</template>
