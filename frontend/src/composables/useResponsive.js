import { ref, onMounted, onUnmounted } from 'vue'

export function useResponsive() {
  const windowWidth = ref(window.innerWidth)
  const windowHeight = ref(window.innerHeight)
  
  // Breakpoints
  const isMobile = ref(false)
  const isTablet = ref(false)
  const isDesktop = ref(false)
  const isLarge = ref(false)
  
  // Device detection
  const isTouchDevice = ref(false)
  const isMobileDevice = ref(false)
  
  // Orientation
  const orientation = ref('portrait')
  
  const updateWindowSize = () => {
    windowWidth.value = window.innerWidth
    windowHeight.value = window.innerHeight
    
    // Update breakpoints
    isMobile.value = windowWidth.value < 768
    isTablet.value = windowWidth.value >= 768 && windowWidth.value < 1024
    isDesktop.value = windowWidth.value >= 1024 && windowWidth.value < 1280
    isLarge.value = windowWidth.value >= 1280
    
    // Update orientation
    orientation.value = windowWidth.value > windowHeight.value ? 'landscape' : 'portrait'
  }
  
  const updateDeviceType = () => {
    // Detect touch device
    isTouchDevice.value = 'ontouchstart' in window || navigator.maxTouchPoints > 0
    
    // Detect mobile device (more comprehensive)
    const userAgent = navigator.userAgent.toLowerCase()
    const mobileKeywords = [
      'android', 'iphone', 'ipad', 'ipod', 'blackberry', 
      'windows phone', 'mobile', 'palm', 'webos'
    ]
    
    isMobileDevice.value = mobileKeywords.some(keyword => userAgent.includes(keyword))
  }
  
  const handleResize = () => {
    updateWindowSize()
  }
  
  const handleOrientationChange = () => {
    updateWindowSize()
  }
  
  onMounted(() => {
    updateWindowSize()
    updateDeviceType()
    
    // Add event listeners
    window.addEventListener('resize', handleResize)
    window.addEventListener('orientationchange', handleOrientationChange)
  })
  
  onUnmounted(() => {
    // Remove event listeners
    window.removeEventListener('resize', handleResize)
    window.removeEventListener('orientationchange', handleOrientationChange)
  })
  
  // Utility functions
  const getResponsiveValue = (mobile, tablet, desktop, large) => {
    if (isLarge.value) return large
    if (isDesktop.value) return desktop
    if (isTablet.value) return tablet
    return mobile
  }
  
  const getResponsiveClass = (classes) => {
    const classArray = Array.isArray(classes) ? classes : [classes]
    
    return classArray
      .map(cls => {
        if (typeof cls === 'string') {
          return cls
        }
        
        return getResponsiveValue(
          cls.mobile || '',
          cls.tablet || '',
          cls.desktop || '',
          cls.large || ''
        )
      })
      .filter(Boolean)
      .join(' ')
  }
  
  const getBreakpoint = () => {
    if (isMobile.value) return 'mobile'
    if (isTablet.value) return 'tablet'
    if (isDesktop.value) return 'desktop'
    if (isLarge.value) return 'large'
    return 'unknown'
  }
  
  return {
    // Reactive state
    windowWidth,
    windowHeight,
    isMobile,
    isTablet,
    isDesktop,
    isLarge,
    isTouchDevice,
    isMobileDevice,
    orientation,
    
    // Utility functions
    getResponsiveValue,
    getResponsiveClass,
    getBreakpoint
  }
}
