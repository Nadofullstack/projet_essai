import { computed } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'

/**
 * Composable de debug pour vérifier l'état de l'utilisateur
 * Utilisation: const { userInfo, testToken } = useUserDebug()
 */
export function useUserDebug() {
  const dashboardStore = useDashboardStore()

  // Informations de debug
  const userInfo = computed(() => ({
    currentUser: dashboardStore.currentUser,
    hasToken: !!localStorage.getItem('auth_token'),
    token: localStorage.getItem('auth_token') ? localStorage.getItem('auth_token').substring(0, 20) + '...' : 'none',
    isUserSet: !!(dashboardStore.currentUser?.id),
    userName: dashboardStore.currentUser?.name || 'N/A',
    userEmail: dashboardStore.currentUser?.email || 'N/A'
  }))

  const logUserState = () => {
    console.group('📋 État de l\'utilisateur')
    console.log('Current User:', dashboardStore.currentUser)
    console.log('Has Token:', !!localStorage.getItem('auth_token'))
    console.log('User Name:', dashboardStore.currentUser?.name)
    console.log('User Email:', dashboardStore.currentUser?.email)
    console.groupEnd()
  }

  const testFetchUser = async () => {
    console.group('🧪 Test fetchCurrentUser')
    const result = await dashboardStore.fetchCurrentUser()
    console.log('Résultat:', result)
    console.log('User après fetch:', dashboardStore.currentUser)
    console.groupEnd()
    return result
  }

  return {
    userInfo,
    logUserState,
    testFetchUser
  }
}
