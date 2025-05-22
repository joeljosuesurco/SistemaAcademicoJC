import { mdiAccount, mdiEmail, mdiLogout } from '@mdi/js'

export default [
  {
    isCurrentUser: true,
    menu: [
      {
        icon: mdiAccount,
        label: 'Mi cuenta',
        to: '/mi-perfil',
      },
      {
        icon: mdiEmail,
        label: 'Comunicados',
        to: '/comunicado-reciente',
      },
      {
        isDivider: true,
      },
      {
        icon: mdiLogout,
        label: 'Salir',
        isLogout: true,
      },
    ],
  },
]
