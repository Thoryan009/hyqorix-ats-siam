export const DEFAULT_PRIMARY_COLOR = '#10b981'
export const PRIMARY_COLOR_STORAGE_KEY = 'ats-primary-color'

export function isValidHexColor(value) {
  return typeof value === 'string' && /^#([A-Fa-f0-9]{6})$/.test(value)
}

function hexToRgb(hex) {
  const normalized = hex.replace('#', '')

  return {
    r: parseInt(normalized.slice(0, 2), 16),
    g: parseInt(normalized.slice(2, 4), 16),
    b: parseInt(normalized.slice(4, 6), 16),
  }
}

function rgbToHex({ r, g, b }) {
  const toHex = (n) => Math.round(Math.min(255, Math.max(0, n))).toString(16).padStart(2, '0')
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}

function mixWith(hex, target, amount) {
  const from = hexToRgb(hex)
  const to = hexToRgb(target)

  return rgbToHex({
    r: from.r + (to.r - from.r) * amount,
    g: from.g + (to.g - from.g) * amount,
    b: from.b + (to.b - from.b) * amount,
  })
}

export function buildPrimaryPalette(hex) {
  const color = isValidHexColor(hex) ? hex : DEFAULT_PRIMARY_COLOR

  return {
    primary: color,
    light: mixWith(color, '#ffffff', 0.88),
    gradient: mixWith(color, '#ffffff', 0.78),
    ring: mixWith(color, '#ffffff', 0.45),
    hover: mixWith(color, '#000000', 0.18),
    dark: mixWith(color, '#000000', 0.45),
  }
}

export function getStoredPrimaryColor() {
  try {
    return localStorage.getItem(PRIMARY_COLOR_STORAGE_KEY)
  } catch {
    return null
  }
}

export function applyPrimaryTheme(hex, { persist = true } = {}) {
  const palette = buildPrimaryPalette(hex)
  const root = document.documentElement

  root.style.setProperty('--color-primary', palette.primary)
  root.style.setProperty('--color-primary-light', palette.light)
  root.style.setProperty('--color-primary-gradient', palette.gradient)
  root.style.setProperty('--color-primary-ring', palette.ring)
  root.style.setProperty('--color-primary-hover', palette.hover)
  root.style.setProperty('--color-primary-dark', palette.dark)
  root.style.setProperty('--primary', palette.primary)

  if (persist && isValidHexColor(hex)) {
    try {
      localStorage.setItem(PRIMARY_COLOR_STORAGE_KEY, palette.primary)
    } catch {
      // ignore storage failures
    }
  }

  return palette.primary
}
